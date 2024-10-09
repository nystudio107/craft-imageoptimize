<?php
/**
 * ImageOptimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\Volume;
use craft\console\Application as ConsoleApplication;
use craft\elements\Asset;
use craft\errors\ImageException;
use craft\errors\SiteNotFoundException;
use craft\helpers\ElementHelper;
use craft\helpers\Image;
use craft\helpers\Json;
use craft\models\AssetTransform;
use craft\models\FieldLayout;
use nystudio107\imageoptimize\fields\OptimizedImages as OptimizedImagesField;
use nystudio107\imageoptimize\helpers\Image as ImageHelper;
use nystudio107\imageoptimize\ImageOptimize;
use nystudio107\imageoptimize\jobs\ResaveOptimizedImages;
use nystudio107\imageoptimize\models\OptimizedImage;
use nystudio107\imageoptimize\models\Settings;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use function in_array;

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
    public function createOptimizedImages(Asset $asset, array $variants = [])
    {
        Craft::beginProfile('createOptimizedImages', __METHOD__);
        if (empty($variants)) {
            /** @var ?Settings $settings */
            $settings = ImageOptimize::$plugin->getSettings();
            if ($settings) {
                $variants = $settings->defaultVariants;
            }
        }

        $model = new OptimizedImage();
        $this->populateOptimizedImageModel($asset, $variants, $model);
        Craft::endProfile('createOptimizedImages', __METHOD__);

        return $model;
    }

    /**
     * @param Asset $asset
     * @param array $variants
     * @param OptimizedImage $model
     * @param boolean $force
     */
    public function populateOptimizedImageModel(Asset $asset, $variants, OptimizedImage $model, $force = false)
    {
        Craft::beginProfile('populateOptimizedImageModel', __METHOD__);
        /** @var Settings $settings */
        $settings = ImageOptimize::$plugin->getSettings();
        // Empty our the optimized image URLs
        $model->optimizedImageUrls = [];
        $model->optimizedWebPImageUrls = [];
        $model->variantSourceWidths = [];
        $model->placeholderWidth = 0;
        $model->placeholderHeight = 0;
        $model->stickyErrors = [];

        foreach ($variants as $variant) {
            $retinaSizes = ['1'];
            if (!empty($variant['retinaSizes'])) {
                $retinaSizes = $variant['retinaSizes'];
            }
            foreach ($retinaSizes as $retinaSize) {
                $finalFormat = empty($variant['format']) ? $asset->getExtension() : $variant['format'];
                // Only try the transform if it's possible
                if (Image::canManipulateAsImage($finalFormat)
                    && Image::canManipulateAsImage($asset->getExtension())
                    && (int)$asset->height > 0) {
                    // Create the transform based on the variant
                    /** @var AssetTransform $transform */
                    list($transform, $aspectRatio) = $this->getTransformFromVariant($asset, $variant, $retinaSize);
                    // If they want to $force it, set `fileExists` = 0 in the transform index, then delete the transformed image
                    if ($force) {
                        $transforms = Craft::$app->getAssetTransforms();
                        try {
                            $index = $transforms->getTransformIndex($asset, $transform);
                            $index->fileExists = false;
                            $transforms->storeTransformIndexData($index);
                            $volume = $asset->getVolume();
                            $transformPath = $asset->folderPath . $transforms->getTransformSubpath($asset, $index);
                            try {
                                $volume->deleteFile($transformPath);
                            } catch (Throwable $exception) {
                            }
                        } catch (Throwable $e) {
                            $msg = 'Failed to update transform: ' . $e->getMessage();
                            Craft::error($msg, __METHOD__);
                            if (Craft::$app instanceof ConsoleApplication) {
                                echo $msg . PHP_EOL;
                            }
                            // Add the error message to the stickyErrors for the model
                            $model->stickyErrors[] = $msg;
                        }
                    }
                    // Only create the image variant if it is not upscaled, or they are okay with it being up-scaled
                    if (($asset->width >= $transform->width && $asset->height >= $transform->height)
                        || $settings->allowUpScaledImageVariants
                    ) {
                        $this->addVariantImageToModel($asset, $model, $transform, $variant, $aspectRatio);
                    }
                } else {
                    $canManipulate = Image::canManipulateAsImage($asset->getExtension());
                    $msg = 'Could not create transform for: ' . $asset->title
                        . ' - Final format: ' . $finalFormat
                        . ' - Element extension: ' . $asset->getExtension()
                        . ' - canManipulateAsImage: ' . $canManipulate;
                    Craft::error(
                        $msg,
                        __METHOD__
                    );
                    if (Craft::$app instanceof ConsoleApplication) {
                        echo $msg . PHP_EOL;
                    }
                    if ($canManipulate) {
                        // Add the error message to the stickyErrors for the model
                        $model->stickyErrors[] = $msg;
                    }
                }
            }
        }

        // If no image variants were created, populate it with the image itself
        if (empty($model->optimizedImageUrls)) {
            $finalFormat = $asset->getExtension();
            if (Image::canManipulateAsImage($finalFormat)
                && (int)$asset->height > 0) {
                $variant = [
                    'width' => $asset->width,
                    'useAspectRatio' => false,
                    'aspectRatioX' => $asset->width,
                    'aspectRatioY' => $asset->height,
                    'retinaSizes' => ['1'],
                    'quality' => 0,
                ];
                list($transform, $aspectRatio) = $this->getTransformFromVariant($asset, $variant, 1);
                $this->addVariantImageToModel($asset, $model, $transform, $variant, $aspectRatio);
            } else {
                $canManipulate = Image::canManipulateAsImage($asset->getExtension());
                $msg = 'Could not create transform for: ' . $asset->title
                    . ' - Final format: ' . $finalFormat
                    . ' - Element extension: ' . $asset->getExtension()
                    . ' - canManipulateAsImage: ' . $canManipulate;
                Craft::error(
                    $msg,
                    __METHOD__
                );
                if ($canManipulate) {
                    // Add the error message to the stickyErrors for the model
                    $model->stickyErrors[] = $msg;
                }
            }
        }
        Craft::endProfile('populateOptimizedImageModel', __METHOD__);
    }

    /**
     * Should variants be created for the given OptimizedImages field and the Asset?
     *
     * @param $field
     * @param $asset
     * @return bool
     */
    public function shouldCreateVariants($field, $asset): bool
    {
        $createVariants = true;
        Craft::info(print_r($field->fieldVolumeSettings, true), __METHOD__);
        // See if we're ignoring files in this dir
        if (!empty($field->fieldVolumeSettings)) {
            foreach ($field->fieldVolumeSettings as $volumeHandle => $subfolders) {
                if ($asset->getVolume()->handle === $volumeHandle) {
                    if (is_string($subfolders) && $subfolders === '*') {
                        $createVariants = true;
                        Craft::info("Matched '*' wildcard ", __METHOD__);
                    } else {
                        $createVariants = false;
                        if (is_array($subfolders)) {
                            foreach ($subfolders as $subfolder) {
                                $folder = $asset->getFolder();
                                while ($folder !== null && !$createVariants) {
                                    if ($folder->uid === $subfolder || $folder->name === $subfolder) {
                                        Craft::info('Matched subfolder uid: ' . print_r($subfolder, true), __METHOD__);
                                        $createVariants = true;
                                    } else {
                                        $folder = $folder->getParent();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // See if we should ignore this type of file
        $sourceType = $asset->getMimeType();
        if (!empty($field->ignoreFilesOfType) && $sourceType !== null) {
            $ignoreTypes = array_values($field->ignoreFilesOfType);
            // If `image/svg` is being ignored, add `image/svg+xml` to the mime types to ignore as well
            if (in_array('image/svg', $ignoreTypes, false)) {
                $ignoreTypes[] = 'image/svg+xml';
            }
            if (in_array($sourceType, $ignoreTypes, false)) {
                $createVariants = false;
            }
        }
        return $createVariants;
    }

    /**
     * @param Field $field
     * @param ElementInterface $asset
     * @param boolean $force
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function updateOptimizedImageFieldData(Field $field, ElementInterface $asset, $force = false)
    {
        /** @var Asset $asset */
        if ($asset instanceof Asset && $field instanceof OptimizedImagesField) {
            $createVariants = $this->shouldCreateVariants($field, $asset);
            // Create a new OptimizedImage model and populate it
            $model = new OptimizedImage();
            // Empty our the optimized image URLs
            $model->optimizedImageUrls = [];
            $model->optimizedWebPImageUrls = [];
            $model->variantSourceWidths = [];
            $model->placeholderWidth = 0;
            $model->placeholderHeight = 0;
            if ($asset !== null && $createVariants) {
                $this->populateOptimizedImageModel(
                    $asset,
                    $field->variants,
                    $model,
                    $force
                );
            }
            // Save our field data directly into the content table
            if ($field->handle !== null) {
                $asset->setFieldValue($field->handle, $field->serializeValue($model));
                $table = $asset->getContentTable();
                $column = $asset->getFieldColumnPrefix() . $field->handle;
                // Special-case for Craft 3.7 or later, with the addition of a suffix to the Field content column name
                // ref: https://github.com/craftcms/cms/issues/6922
                if (ImageOptimize::$craft37) {
                    $column = ElementHelper::fieldColumnFromField($field);
                }
                $data = Json::encode($field->serializeValue($asset->getFieldValue($field->handle), $asset));
                Craft::$app->db->createCommand()
                    ->update($table, [
                        $column => $data,
                    ], [
                        'elementId' => $asset->getId(),
                    ], [], false)
                    ->execute();
            }
        }
    }

    /**
     * Re-save all of the assets in all of the volumes
     *
     * @param int|null $fieldId only for this specific id
     * @param bool $force Should image variants be forced to be recreated?
     *
     * @throws InvalidConfigException
     */
    public function resaveAllVolumesAssets($fieldId = null, $force = false)
    {
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        foreach ($volumes as $volume) {
            if (is_subclass_of($volume, Volume::class)) {
                /** @var Volume $volume */
                $this->resaveVolumeAssets($volume, $fieldId, $force);
            }
        }
    }

    /**
     * Re-save all of the Asset elements in the Volume $volume that have an
     * OptimizedImages field in the FieldLayout
     *
     * @param Volume $volume for this volume
     * @param int|null $fieldId only for this specific id
     * @param bool $force Should image variants be forced to be recreated?
     *
     * @throws InvalidConfigException
     */
    public function resaveVolumeAssets(Volume $volume, $fieldId = null, $force = false)
    {
        $needToReSave = false;
        /** @var ?FieldLayout $fieldLayout */
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
                    'Failed to get primary site: ' . $e->getMessage(),
                    __METHOD__
                );
            }

            $queue = Craft::$app->getQueue();
            $jobId = $queue->push(new ResaveOptimizedImages([
                'description' => Craft::t('image-optimize', 'Optimizing images in {name}', ['name' => $volume->name]),
                'criteria' => [
                    'siteId' => $siteId,
                    'volumeId' => $volume->id,
                    'status' => null,
                    'enabledForSite' => false,
                ],
                'fieldId' => $fieldId,
                'force' => $force,
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
     * @param bool $force Should image variants be forced to be recreated?
     */
    public function resaveAsset(int $id, $force = false)
    {
        $queue = Craft::$app->getQueue();
        $jobId = $queue->push(new ResaveOptimizedImages([
            'description' => Craft::t('image-optimize', 'Optimizing image id {id}', ['id' => $id]),
            'criteria' => [
                'id' => $id,
                'status' => null,
                'enabledForSite' => false,
            ],
            'force' => $force,
        ]));
        Craft::debug(
            Craft::t(
                'image-optimize',
                'Started resaveAsset queue job id: {jobId} Element id: {elementId}',
                [
                    'elementId' => $id,
                    'jobId' => $jobId,
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

    // Protected Methods
    // =========================================================================

    /**
     * @param Asset $element
     * @param OptimizedImage $model
     * @param                $aspectRatio
     */
    protected function generatePlaceholders(Asset $element, OptimizedImage $model, $aspectRatio)
    {
        Craft::beginProfile('generatePlaceholders', __METHOD__);
        Craft::info(
            'generatePlaceholders for: ' . print_r($model, true),
            __METHOD__
        );
        /** @var Settings $settings */
        $settings = ImageOptimize::$plugin->getSettings();
        if ($settings->generatePlaceholders && ImageOptimize::$generatePlaceholders) {
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
                    $model->lightness = $placeholder->calculateLightness($model->colorPalette);
                }
                // Generate the Potrace SVG
                if ($settings->createPlaceholderSilhouettes) {
                    $model->placeholderSvg = $placeholder->generatePlaceholderSvg($tempPath);
                }
                // Get rid of our placeholder image
                @unlink($tempPath);
            }
        }
        Craft::endProfile('generatePlaceholders', __METHOD__);
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
        /** @var Settings $settings */
        $settings = ImageOptimize::$plugin->getSettings();
        $transform = new AssetTransform();
        $transform->format = $variant['format'] ?? null;
        // Handle animate .gif images by never changing the format
        $images = Craft::$app->getImages();
        if ($asset->extension === 'gif' && !$images->getIsGd()) {
            $imageSource = $asset->getTransformSource();
            try {
                if (ImageHelper::getIsAnimatedGif($imageSource)) {
                    $transform->format = null;
                }
            } catch (ImageException $e) {
                Craft::error($e->getMessage(), __METHOD__);
            } catch (InvalidConfigException $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }
        $useAspectRatio = $variant['useAspectRatio'] ?? false;
        if ($useAspectRatio) {
            $aspectRatio = (float)$variant['aspectRatioX'] / (float)$variant['aspectRatioY'];
        } else {
            $aspectRatio = (float)$asset->width / (float)$asset->height;
        }
        $width = (int)$variant['width'] * (int)$retinaSize;
        $transform->width = $width;
        $transform->height = (int)($width / $aspectRatio);
        // Image quality
        $quality = (int)($variant['quality'] ?? null);
        if (empty($quality)) {
            $quality = null;
        }
        if ($quality !== null && $settings->lowerQualityRetinaImageVariants && $retinaSize != '1') {
            $quality = (int)($quality * (1.5 / (int)$retinaSize));
        }
        $transform->quality = $quality;
        // Interlaced (progressive JPEGs or interlaced PNGs)
        if (property_exists($transform, 'interlace')) {
            $transform->interlace = 'line';
        }

        return [$transform, $aspectRatio];
    }

    /**
     * @param Asset $asset
     * @param OptimizedImage $model
     * @param                $transform
     * @param                $variant
     * @param                $aspectRatio
     */
    protected function addVariantImageToModel(Asset $asset, OptimizedImage $model, $transform, $variant, $aspectRatio)
    {
        Craft::beginProfile('addVariantImageToModel', __METHOD__);
        // Generate an image transform url
        $url = ImageOptimize::$plugin->transformMethod->getTransformUrl(
            $asset,
            $transform
        );
        Craft::info(
            'URL created: ' . print_r($url, true),
            __METHOD__
        );
        // Update the model
        if (!empty($url)) {
            $model->variantSourceWidths[] = $variant['width'];
            $model->variantHeights[$transform->width] = $asset->getHeight($transform);
            // Store & prefetch image at the image URL
            //ImageOptimize::$plugin->transformMethod->prefetchRemoteFile($url);
            $model->optimizedImageUrls[$transform->width] = $url;
            // Store & prefetch image at the webp URL
            $webPUrl = ImageOptimize::$plugin->transformMethod->getWebPUrl(
                $url,
                $asset,
                $transform
            );
            // If the original image is an SVG, don't add a variant for it
            $path = parse_url($url, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension !== 'svg') {
                $model->optimizedWebPImageUrls[$transform->width] = $webPUrl;
            }
            //ImageOptimize::$plugin->transformMethod->prefetchRemoteFile($webPUrl);
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
                'Created transforms for variant: ' . print_r($variant, true),
                __METHOD__
            );
        }
        Craft::endProfile('addVariantImageToModel', __METHOD__);
    }
}
