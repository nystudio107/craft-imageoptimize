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

    const TEMP_PLACEHOLDER_WIDTH = 300;
    const TEMP_PLACEHOLDER_QUALITY = 75;

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

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
        $result = '';
        $width = self::PLACEHOLDER_WIDTH;
        $height = intval($width / $aspectRatio);
        $placeholderPath = $this->createImageFromPath($tempPath, $width, $height, self::PLACEHOLDER_QUALITY, $position);
        if (!empty($placeholderPath)) {
            $result = base64_encode(file_get_contents($placeholderPath));
            unlink($placeholderPath);
        }

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
        $colorPalette = [];
        if (!empty($tempPath)) {
            // Extract the color palette
            $palette = ColorThief::getPalette($tempPath, 5);
            // Convert RGB to hex color
            foreach ($palette as $colors) {
                $colorPalette[] = sprintf("#%02x%02x%02x", $colors[0], $colors[1], $colors[2]);
            }
        }

        return $colorPalette;
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
        $result = '';

        if (!empty($tempPath)) {
            $pot = new Potracio();
            $pot->loadImageFromFile($tempPath);
            $pot->process();

            $result = $pot->getSVG(1);
        }

        return ImageOptimize::$plugin->optimize->encodeOptimizedSVGDataUri($result);
    }

    /**
     * Create a small placeholder image file that the various placerholder generators can use
     *
     * @param Asset             $asset
     * @param float             $aspectRatio
     * @param mixed|string|null $position
     *
     * @return string
     */
    public function createTempPlaceholderImage(Asset $asset, float $aspectRatio, $position): string
    {
        $width = self::TEMP_PLACEHOLDER_WIDTH;
        $height = intval($width / $aspectRatio);
        $tempPath = $this->createImageFromAsset($asset, $width, $height, self::TEMP_PLACEHOLDER_QUALITY, $position);

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
    public function createImageFromAsset(Asset $asset, int $width, int $height, int $quality, $position)
    {
        $tempPath = '';

        if (!empty($asset) && Image::canManipulateAsImage($asset->getExtension())) {
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

        $image->scaleAndCrop($width, $height, true, $position);

        // Save the image out to a temp file, then return its contents
        $tempFilename = uniqid(pathinfo($pathParts['filename'], PATHINFO_FILENAME), true).'.'.'jpg';
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;
        clearstatcache(true, $tempPath);
        $image->saveAs($tempPath);

        return $tempPath;
    }
}
