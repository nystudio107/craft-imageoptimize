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
use nystudio107\imageoptimize\lib\Potracio;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
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

    const SVG_PLACEHOLDER_WIDTH = 300;
    const SVG_PLACEHOLDER_QUALITY = 75;

    const COLOR_PALETTE_WIDTH = 200;
    const COLOR_PALETTE_QUALITY = 75;

    // Public Methods
    // =========================================================================

    /**
     * Generate a base64-encoded placeholder image
     *
     * @param Asset $asset
     * @param float $aspectRatio
     *
     * @return string
     */
    public function generatePlaceholderImage(Asset $asset, float $aspectRatio): string
    {
        $result = '';
        $width = self::PLACEHOLDER_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::PLACEHOLDER_QUALITY);
        if (!empty($tempPath)) {
            $result = base64_encode(file_get_contents($tempPath));
            unlink($tempPath);
        }

        return $result;
    }

    /**
     * Generate a color palette from the image
     *
     * @param Asset $asset
     * @param float $aspectRatio
     *
     * @return array
     */
    public function generateColorPalette(Asset $asset, float $aspectRatio): array
    {
        $colorPalette = [];
        $width = self::COLOR_PALETTE_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::COLOR_PALETTE_QUALITY);
        if (!empty($tempPath)) {
            // Extract the color palette
            $palette = ColorThief::getPalette($tempPath, 5);
            // Convert RGB to hex color
            foreach ($palette as $colors) {
                $colorPalette[] = sprintf("#%02x%02x%02x", $colors[0], $colors[1], $colors[2]);
            }
            unlink($tempPath);
        }

        return $colorPalette;
    }

    /**
     * Generate an SVG image via Potrace
     *
     * @param Asset $asset
     * @param float $aspectRatio
     *
     * @return string
     */
    public function generatePlaceholderSvg(Asset $asset, float $aspectRatio): string
    {
        $result = '';
        $width = self::SVG_PLACEHOLDER_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::SVG_PLACEHOLDER_QUALITY);
        if (!empty($tempPath)) {
            $pot = new Potracio();
            $pot->loadImageFromFile($tempPath);
            $pot->process();

            $result = $pot->getSVG(1);
            unlink($tempPath);
        }

        return ImageOptimize::$plugin->optimize->encodeOptimizedSVGDataUri($result);
    }

    /**
     * @param Asset $asset
     * @param int   $width
     * @param int   $height
     * @param int   $quality
     *
     * @return string
     */
    public function createImageFromAsset(Asset $asset, int $width, int $height, int $quality)
    {
        $tempPath = '';
        if (!empty($asset) && Image::canManipulateAsImage($asset->getExtension())) {
            $images = Craft::$app->getImages();
            $imageSource = $asset->getTransformSource();
            /** @var Image $image */
            if (StringHelper::toLowerCase($asset->getExtension()) === 'svg') {
                $image = $images->loadImage($imageSource, true, $width);
            } else {
                $image = $images->loadImage($imageSource);
            }

            if ($image instanceof Raster) {
                $image->setQuality($quality);
            }

            // Scale and crop the placeholder image
            if ($asset->focalPoint) {
                $position = $asset->getFocalPoint();
            } else {
                $position = 'center-center';
            }
            $image->scaleAndCrop($width, $height, true, $position);

            // Save the image out to a temp file, then return its contents
            $tempFilename = uniqid(pathinfo($asset->filename, PATHINFO_FILENAME), true) . '.' . 'jpg';
            $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $tempFilename;
            clearstatcache(true, $tempPath);
            $image->saveAs($tempPath);
        }

        return $tempPath;
    }
}
