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
use nystudio107\imageoptimize\helpers\Color as ColorHelper;
use nystudio107\imageoptimize\lib\Potracio;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\errors\ImageException;
use craft\helpers\Image;
use craft\helpers\StringHelper;
use craft\image\Raster;

use ColorThief\ColorThief;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class Placeholder extends Component
{
    // Constants
    // =========================================================================

    const PLACEHOLDER_WIDTH = 16;
    const PLACEHOLDER_QUALITY = 50;

    const TEMP_PLACEHOLDER_WIDTH = 300;
    const TEMP_PLACEHOLDER_QUALITY = 75;

    const MAX_SILHOUETTE_SIZE = 30 * 1024;

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Return an SVG box as a placeholder image
     *
     * @param             $width
     * @param             $height
     * @param string|null $color
     *
     * @return string
     */
    public function generatePlaceholderBox($width, $height, $color = null): string
    {
        $color = $color ?? '#CCC';
        $header = 'data:image/svg+xml,';
        $content = "<svg xmlns='http://www.w3.org/2000/svg' "
            ."width='$width' "
            ."height='$height' "
            ."style='background:$color' "
            ."/>";

        return $header.ImageOptimize::$plugin->optimizedImages->encodeOptimizedSVGDataUri($content);
    }

    /**
     * Generate a base64-encoded placeholder image
     *
     * @param string            $tempPath
     * @param float             $aspectRatio
     * @param mixed|string|null $position
     *
     * @return string
     */
    public function generatePlaceholderImage(string $tempPath, float $aspectRatio, $position): string
    {
        Craft::beginProfile('generatePlaceholderImage', __METHOD__);
        Craft::info(
            'Generating placeholder image for asset',
            __METHOD__
        );
        $result = '';
        $width = self::PLACEHOLDER_WIDTH;
        $height = (int)($width / $aspectRatio);
        $placeholderPath = $this->createImageFromPath($tempPath, $width, $height, self::PLACEHOLDER_QUALITY, $position);
        if (!empty($placeholderPath)) {
            $result = base64_encode(file_get_contents($placeholderPath));
            unlink($placeholderPath);
        }
        Craft::endProfile('generatePlaceholderImage', __METHOD__);

        return $result;
    }

    /**
     * Generate a color palette from the image
     *
     * @param string $tempPath
     *
     * @return array
     */
    public function generateColorPalette(string $tempPath): array
    {
        Craft::beginProfile('generateColorPalette', __METHOD__);
        Craft::info(
            'Generating color palette for: ' . $tempPath,
            __METHOD__
        );
        $colorPalette = [];
        if (!empty($tempPath)) {
            // Extract the color palette
            try {
                $palette = ColorThief::getPalette($tempPath, 5);
            } catch (\Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);

                return [];
            }
            // Convert RGB to hex color
            foreach ($palette as $colors) {
                $colorPalette[] = sprintf('#%02x%02x%02x', $colors[0], $colors[1], $colors[2]);
            }
        }
        Craft::endProfile('generateColorPalette', __METHOD__);

        return $colorPalette;
    }

    /**
     * @param array $colors
     *
     * @return float|int|null
     */
    public function calculateLightness(array $colors)
    {
        $lightness = null;
        if (!empty($colors)) {
            $lightness = 0;
            $colorWeight = count($colors);
            $colorCount = 0;
            foreach ($colors as $color) {
                $rgb = ColorHelper::HTMLToRGB($color);
                $hsl = ColorHelper::RGBToHSL($rgb);
                $lightness += $hsl['l'] * $colorWeight;
                $colorCount += $colorWeight;
                $colorWeight--;
            }

            $lightness /= $colorCount;
        }

        return $lightness === null ? $lightness : (int)$lightness;
    }
    /**
     * Generate an SVG image via Potrace
     *
     * @param string $tempPath
     *
     * @return string
     */
    public function generatePlaceholderSvg(string $tempPath): string
    {
        Craft::beginProfile('generatePlaceholderSvg', __METHOD__);
        $result = '';

        if (!empty($tempPath)) {
            // Potracio depends on `gd` being installed
            if (\function_exists('imagecreatefromjpeg')) {
                $pot = new Potracio();
                $pot->loadImageFromFile($tempPath);
                $pot->process();

                $result = $pot->getSVG(1);

                // Optimize the result if we got one
                if (!empty($result)) {
                    $result = ImageOptimize::$plugin->optimizedImages->encodeOptimizedSVGDataUri($result);
                }
            }
            /**
             * If Potracio failed or gd isn't installed, or this is larger
             * than MAX_SILHOUETTE_SIZE bytes, just return a box
             */
            if (empty($result) || (\strlen($result) > self::MAX_SILHOUETTE_SIZE)) {
                $size = getimagesize($tempPath);
                if ($size !== false) {
                    list($width, $height) = $size;
                    $result = $this->generatePlaceholderBox($width, $height);
                }
            }
        }
        Craft::endProfile('generatePlaceholderSvg', __METHOD__);

        return $result;
    }

    /**
     * Create a small placeholder image file that the various placerholder
     * generators can use
     *
     * @param Asset             $asset
     * @param float             $aspectRatio
     * @param mixed|string|null $position
     *
     * @return string
     */
    public function createTempPlaceholderImage(Asset $asset, float $aspectRatio, $position): string
    {
        Craft::beginProfile('createTempPlaceholderImage', __METHOD__);
        Craft::info(
            'Creating temporary placeholder image for asset',
            __METHOD__
        );
        $width = self::TEMP_PLACEHOLDER_WIDTH;
        $height = (int)($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::TEMP_PLACEHOLDER_QUALITY, $position);
        Craft::endProfile('createTempPlaceholderImage', __METHOD__);

        return $tempPath;
    }

    /**
     * @param Asset             $asset
     * @param int               $width
     * @param int               $height
     * @param int               $quality
     * @param mixed|string|null $position
     *
     * @return string
     */
    public function createImageFromAsset(Asset $asset, int $width, int $height, int $quality, $position): string
    {
        $tempPath = '';

        if ($asset !== null && Image::canManipulateAsImage($asset->getExtension())) {
            $imageSource = $asset->getTransformSource();
            // Scale and crop the placeholder image
            $tempPath = $this->createImageFromPath($imageSource, $width, $height, $quality, $position);
        }

        return $tempPath;
    }

    /**
     * @param string            $filePath
     * @param int               $width
     * @param int               $height
     * @param int               $quality
     * @param mixed|string|null $position
     *
     * @return string
     */
    public function createImageFromPath(
        string $filePath,
        int $width,
        int $height,
        int $quality,
        $position
    ): string {
        $images = Craft::$app->getImages();
        $pathParts = pathinfo($filePath);
        /** @var Image $image */
        if (StringHelper::toLowerCase($pathParts['extension']) === 'svg') {
            $image = $images->loadImage($filePath, true, $width);
        } else {
            $image = $images->loadImage($filePath);
        }

        if ($image instanceof Raster) {
            $image->setQuality($quality);
        }

        // No matter what the user settings are, we don't want any metadata/color palette cruft
        $config = Craft::$app->getConfig()->getGeneral();
        $oldOptimizeImageFilesize = $config->optimizeImageFilesize;
        $oldPreserveImageColorProfiles = $config->preserveImageColorProfiles;
        $oldPreserveExifData = $config->preserveExifData;
        $config->optimizeImageFilesize = true;
        $config->preserveImageColorProfiles = false;
        $config->preserveExifData = false;

        // Resize the image
        $image->scaleAndCrop($width, $height, true, $position);

        // Restore the old settings
        $config->optimizeImageFilesize = $oldOptimizeImageFilesize;
        $config->preserveImageColorProfiles = $oldPreserveImageColorProfiles;
        $config->preserveExifData = $oldPreserveExifData;

        // Save the image out to a temp file, then return its contents
        $tempFilename = uniqid(pathinfo($pathParts['filename'], PATHINFO_FILENAME), true).'.'.'jpg';
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;
        clearstatcache(true, $tempPath);
        try {
            $image->saveAs($tempPath);
        } catch (ImageException $e) {
            Craft::error(
                'Error saving temporary image: '.$e->getMessage(),
                __METHOD__
            );
        }

        return $tempPath;
    }
}
