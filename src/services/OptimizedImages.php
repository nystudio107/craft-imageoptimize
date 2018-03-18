<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\services;

use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\fields\OptimizedImages as OptimizedImagesField;
use nystudio107\imageoptimize\models\OptimizedImage;
use nystudio107\imageoptimize\jobs\ResaveOptimizedImages;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\Volume;
use craft\elements\Asset;
use craft\errors\SiteNotFoundException;
use craft\helpers\Image;
use craft\helpers\Json;
use craft\models\AssetTransform;
use craft\models\FieldLayout;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.4.0
 */
class OptimizedImages extends Component
{
    // Constants
    // =========================================================================

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @param Asset $asset
     * @param array $variants
     *
     * @return OptimizedImage|null
     */
    public function createOptimizedImages(Asset $asset, $variants = [])
    {
        if (empty($variants)) {
            $settings = ImageOptimize::$plugin->getSettings();
            if ($settings) {
                $variants = $settings->defaultVariants;
            }
        }

        $model = new OptimizedImage();
        $this->populateOptimizedImageModel($asset, $variants, $model);

        return $model;
    }

    /**
     * @param Asset          $asset
     * @param array          $variants
     * @param OptimizedImage $model
     */
    public function populateOptimizedImageModel(Asset $asset, $variants, OptimizedImage $model)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Empty our the optimized image URLs
        $model->optimizedImageUrls = [];
        $model->optimizedWebPImageUrls = [];
        $model->variantSourceWidths = [];
        $model->placeholderWidth = 0;
        $model->placeholderHeight = 0;

        foreach ($variants as $variant) {
            $retinaSizes = ['1'];
            if (!empty($variant['retinaSizes'])) {
                $retinaSizes = $variant['retinaSizes'];
            }
            foreach ($retinaSizes as $retinaSize) {
                $finalFormat = $variant['format'] == null ? $asset->getExtension() : $variant['format'];
                // Only try the transform if it's possible
                if (Image::canManipulateAsImage($finalFormat)
                    && Image::canManipulateAsImage($asset->getExtension())
                    && $asset->height > 0) {
                    // Create the transform based on the variant
                    list($transform, $aspectRatio) = $this->getTransformFromVariant($asset, $variant, $retinaSize);
                    // Only create the image variant if it is not upscaled, or they are okay with it being up-scaled
                    if (($asset->width >= $transform->width && $asset->height >= $transform->height)
                        || $settings->allowUpScaledImageVariants
                    ) {
                        $this->addVariantImageToModel($asset, $model, $transform, $variant, $aspectRatio);
                    }
                } else {
                    Craft::error(
                        'Could not create transform for: '.$asset->title
                        ." - Final format: ".$finalFormat
                        ." - Element extension: ".$asset->getExtension()
                        ." - canManipulateAsImage: ".Image::canManipulateAsImage($asset->getExtension()),
                        __METHOD__
                    );
                }
            }
        }

        // If no image variants were created, populate it with the image itself
        if (empty($model->optimizedImageUrls)) {
            $finalFormat = $asset->getExtension();
            if (Image::canManipulateAsImage($finalFormat)
                && Image::canManipulateAsImage($finalFormat)
                && $asset->height > 0) {
                $variant =         [
                    'width'          => $asset->width,
                    'useAspectRatio' => false,
                    'aspectRatioX'   => $asset->width,
                    'aspectRatioY'   => $asset->height,
                    'retinaSizes'    => ['1'],
                    'quality'        => 0,
                    'format'         => $finalFormat,
                ];
                list($transform, $aspectRatio) = $this->getTransformFromVariant($asset, $variant, 1);
                $this->addVariantImageToModel($asset, $model, $transform, $variant, $aspectRatio);
            } else {
                Craft::error(
                    'Could not create transform for: '.$asset->title
                    ." - Final format: ".$finalFormat
                    ." - Element extension: ".$asset->getExtension()
                    ." - canManipulateAsImage: ".Image::canManipulateAsImage($asset->getExtension()),
                    __METHOD__
                );
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param Asset          $element
     * @param OptimizedImage $model
     * @param                $aspectRatio
     */
    protected function generatePlaceholders(Asset $element, OptimizedImage $model, $aspectRatio)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        if ($settings->generatePlacholders && ImageOptimize::$generatePlacholders) {
            $placeholder = ImageOptimize::$plugin->placeholder;
            if ($element->focalPoint) {
                $position = $element->getFocalPoint();
            } else {
                $position = 'center-center';
            }
            $tempPath = $placeholder->createTempPlaceholderImage($element, $aspectRatio, $position);
            if (!empty($tempPath)) {
                // Generate our placeholder image
                $model->placeholder = $placeholder->generatePlaceholderImage($tempPath, $aspectRatio, $position);
                // Generate the color palette for the image
                if ($settings->createColorPalette) {
                    $model->colorPalette = $placeholder->generateColorPalette($tempPath);
                }
                // Generate the Potrace SVG
                if ($settings->createPlaceholderSilhouettes) {
                    $model->placeholderSvg = $placeholder->generatePlaceholderSvg($tempPath);
                }
                // Get rid of our placeholder image
                @unlink($tempPath);
            }
        }
    }

    /**
     * @param Asset $asset
     * @param       $variant
     * @param       $retinaSize
     *
     * @return array
     */
    protected function getTransformFromVariant(Asset $asset, $variant, $retinaSize): array
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $transform = new AssetTransform();
        $transform->format = $variant['format'];
        $useAspectRatio = isset($variant['useAspectRatio']) ? $variant['useAspectRatio'] : true;
        if ($useAspectRatio) {
            $aspectRatio = $variant['aspectRatioX'] / $variant['aspectRatioY'];
        } else {
            $aspectRatio = $asset->width / $asset->height;
        }
        $width = $variant['width'] * $retinaSize;
        $transform->width = $width;
        $transform->height = intval($width / $aspectRatio);
        // Image quality
        $quality = $variant['quality'];
        if ($settings->lowerQualityRetinaImageVariants && $retinaSize != '1') {
            $quality = intval($quality * (1 / intval($retinaSize)));
        }
        $transform->quality = $quality;
        // Interlaced (progressive JPEGs or interlaced PNGs)
        if (property_exists($transform, 'interlace')) {
            $transform->interlace = 'line';
        }

        return [$transform, $aspectRatio];
    }

    /**
     * @param Asset          $asset
     * @param OptimizedImage $model
     * @param                $transform
     * @param                $variant
     * @param                $aspectRatio
     */
    protected function addVariantImageToModel(Asset $asset, OptimizedImage $model, $transform, $variant, $aspectRatio)
    {
        // Generate an image transform url
        $url = ImageOptimize::$transformClass::getTransformUrl(
            $asset,
            $transform,
            ImageOptimize::$transformParams
        );
        Craft::info(
            'URL created: '.print_r($url, true),
            __METHOD__
        );
        // Update the model
        if (!empty($url)) {
            $model->variantSourceWidths[] = $variant['width'];
            // Store & prefetch image at the image URL
            ImageOptimize::$transformClass::prefetchRemoteFile($url);
            $model->optimizedImageUrls[$transform->width] = $url;
            // Store & prefetch image at the webp URL
            $webPUrl = ImageOptimize::$transformClass::getWebPUrl($url);
            ImageOptimize::$transformClass::prefetchRemoteFile($webPUrl);
            $model->optimizedWebPImageUrls[$transform->width] = $webPUrl;
            $model->focalPoint = $asset->focalPoint;
            $model->originalImageWidth = $asset->width;
            $model->originalImageHeight = $asset->height;
            // Make our placeholder image once, from the first variant
            if (!$model->placeholderWidth) {
                $model->placeholderWidth = $transform->width;
                $model->placeholderHeight = $transform->height;
                $this->generatePlaceholders($asset, $model, $aspectRatio);
            }
            Craft::info(
                'Created transforms for variant: '.print_r($variant, true),
                __METHOD__
            );
        }
    }

    /**
     * @param Field            $field
     * @param ElementInterface $asset
     *
     * @throws \yii\db\Exception
     */
    public function updateOptimizedImageFieldData(Field $field, ElementInterface $asset)
    {
        /** @var Asset $asset */
        if ($asset instanceof Asset && $field instanceof OptimizedImagesField) {
            // Create a new OptimizedImage model and populate it
            $model = new OptimizedImage();
            if (!empty($asset)) {
                $this->populateOptimizedImageModel(
                    $asset,
                    $field->variants,
                    $model
                );
            }
            // Save our field data directly into the content table
            if (!empty($field->handle)) {
                $asset->setFieldValue($field->handle, $field->serializeValue($model));
                $table = $asset->getContentTable();
                $column = $asset->getFieldColumnPrefix().$field->handle;
                $data = Json::encode($field->serializeValue($asset->getFieldValue($field->handle), $asset));
                Craft::$app->db->createCommand()
                    ->update($table, [
                        $column => $data,
                    ], [
                        'id' => $asset->contentId,
                    ], [], false)
                    ->execute();
            }
        }
    }


    /**
     * Re-save all of the assets in all of the volumes
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function resaveAllVolumesAssets()
    {
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        foreach ($volumes as $volume) {
            if (is_subclass_of($volume, Volume::class)) {
                /** @var Volume $volume */
                $this->resaveVolumeAssets($volume);
            }
        }
    }

    /**
     * Re-save all of the Asset elements in the Volume $volume that have an
     * OptimizedImages field in the FieldLayout
     *
     * @param Volume $volume
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function resaveVolumeAssets(Volume $volume)
    {
        $needToReSave = false;
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $volume->getFieldLayout();
        // Loop through the fields in the layout to see if there is an OptimizedImages field
        if ($fieldLayout) {
            $fields = $fieldLayout->getFields();
            foreach ($fields as $field) {
                if ($field instanceof OptimizedImagesField) {
                    $needToReSave = true;
                }
            }
        }
        if ($needToReSave) {
            try {
                $siteId = Craft::$app->getSites()->getPrimarySite()->id;
            } catch (SiteNotFoundException $e) {
                $siteId = 0;
                Craft::error(
                    'Failed to get primary site: '.$e->getMessage(),
                    __METHOD__
                );
            }

            $queue = Craft::$app->getQueue();
            $jobId = $queue->push(new ResaveOptimizedImages([
                'description' => Craft::t('image-optimize', 'Optimizing images in {name}', ['name' => $volume->name]),
                'criteria'    => [
                    'siteId'         => $siteId,
                    'volumeId'       => $volume->id,
                    'status'         => null,
                    'enabledForSite' => false,
                ],
            ]));
            Craft::debug(
                Craft::t(
                    'image-optimize',
                    'Started resaveVolumeAssets queue job id: {jobId}',
                    [
                        'jobId' => $jobId,
                    ]
                ),
                __METHOD__
            );
        }
    }

    /**
     * Re-save an individual asset
     *
     * @param int $id
     */
    public function resaveAsset(int $id)
    {
        $queue = Craft::$app->getQueue();
        $jobId = $queue->push(new ResaveOptimizedImages([
            'description' => Craft::t('image-optimize', 'Optimizing image id {id}', ['id' => $id]),
            'criteria'    => [
                'id'             => $id,
                'status'         => null,
                'enabledForSite' => false,
            ],
        ]));
        Craft::debug(
            Craft::t(
                'image-optimize',
                'Started resaveAsset queue job id: {jobId} Element id: {elementId}',
                [
                    'elementId' => $id,
                    'jobId'     => $jobId,
                ]
            ),
            __METHOD__
        );
    }

    /**
     * Create an optimized SVG data uri
     * See: https://codepen.io/tigt/post/optimizing-svgs-in-data-uris
     *
     * @param string $uri
     *
     * @return string
     */
    public function encodeOptimizedSVGDataUri(string $uri): string
    {
        // First, uri encode everything
        $uri = rawurlencode($uri);
        $replacements = [
            // remove newlines
            '/%0A/' => '',
            // put spaces back in
            '/%20/' => ' ',
            // put equals signs back in
            '/%3D/' => '=',
            // put colons back in
            '/%3A/' => ':',
            // put slashes back in
            '/%2F/' => '/',
            // replace quotes with apostrophes (may break certain SVGs)
            '/%22/' => "'",
        ];
        foreach ($replacements as $pattern => $replacement) {
            $uri = preg_replace($pattern, $replacement, $uri);
        }

        return $uri;
    }
}
