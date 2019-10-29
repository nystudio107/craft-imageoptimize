<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace nystudio107\imageoptimize\helpers;

use Craft;
use craft\errors\ImageException;
use craft\helpers\FileHelper;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Imagick\Imagine as ImagickImagine;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.4
 */
class Image
{
    // Public Static Methods
    // =========================================================================

    /**
     * This is a hacked version of `Raster::loadImage()` because the $_isAnimatedGif
     * property is private and there is no `getIsAnimatedGif()` getter
     *
     * @param string $path
     *
     * @return bool
     *
     * @throws ImageException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getIsAnimatedGif(string $path): bool
    {
        $generalConfig = Craft::$app->getConfig()->getGeneral();

        $extension = strtolower($generalConfig->imageDriver);

        // If it's explicitly set, take their word for it.
        if ($extension === 'gd') {
            $instance = new GdImagine();
        } else {
            if ($extension === 'imagick') {
                $instance = new ImagickImagine();
            } else {
                // Let's try to auto-detect.
                if (Craft::$app->getImages()->getIsGd()) {
                    $instance = new GdImagine();
                } else {
                    $instance = new ImagickImagine();
                }
            }
        }

        $imageService = Craft::$app->getImages();
        if ($imageService->getIsGd()) {
            return false;
        }
        
        if (!is_file($path)) {
            Craft::error('Tried to load an image at '.$path.', but the file does not exist.', __METHOD__);
            throw new ImageException(Craft::t('app', 'No file exists at the given path.'));
        }

        if (!$imageService->checkMemoryForImage($path)) {
            throw new ImageException(Craft::t(
                'app',
                'Not enough memory available to perform this image operation.'
            ));
        }

        // Make sure the image says it's an image
        $mimeType = FileHelper::getMimeType($path, null, false);

        if ($mimeType !== null && strpos($mimeType, 'image/') !== 0 && strpos($mimeType, 'application/pdf') !== 0) {
            throw new ImageException(Craft::t(
                'app',
                'The file “{name}” does not appear to be an image.',
                ['name' => pathinfo($path, PATHINFO_BASENAME)]
            ));
        }

        try {
            $image = $instance->open($path);
        } catch (\Throwable $e) {
            throw new ImageException(Craft::t(
                'app',
                'The file “{path}” does not appear to be an image.',
                ['path' => $path]
            ), 0, $e);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension === 'gif' && $image->layers();
    }
}
