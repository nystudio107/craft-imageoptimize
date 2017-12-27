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
use nystudio107\imageoptimize\models\OptimizedImage;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\Image;
use craft\models\AssetTransform;

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
                if (empty($this->variants)) {
                    $variants = $settings->defaultVariants;
                }
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
    protected function addVariantImageToModel(Asset $asset, OptimizedImage $model, $transform, $variant, $aspectRatio): void
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
}
