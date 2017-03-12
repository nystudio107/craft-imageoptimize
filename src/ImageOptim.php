<?php
/**
 * ImageOptim plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptim;


use Craft;
use craft\base\Plugin;
use craft\services\AssetTransforms;
use craft\events\GenerateTransformEvent;

use yii\base\Event;

/**
 * @author    nystudio107
 * @package   ImageOptim
 * @since     1.0.0
 */
class ImageOptim extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var static
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Listen for image transform events
        Event::on(
            AssetTransforms::className(),
            AssetTransforms::EVENT_GENERATE_TRANSFORM,
            function (GenerateTransformEvent $event) {
                // Save the transformed image to a temp file
                $tempPath = ImageOptim::$plugin->optimize->saveTransformToTempFile(
                    $event->transformIndex,
                    $event->image
                );
                $originalFileSize = filesize($tempPath);
                // Optimize the image
                ImageOptim::$plugin->optimize->optimizeImage(
                    $event->transformIndex,
                    $tempPath
                );
                // Log the results of the image optimization
                $optimizedFileSize = filesize($tempPath);
                $index = $event->transformIndex;
                Craft::info(
                    pathinfo($index->filename, PATHINFO_FILENAME)
                    . "."
                    . $index->detectedFormat
                    . " -> "
                    . Craft::t("imageoptim", "Original")
                    . ": "
                    . $this->humanFileSize($originalFileSize, 1)
                    . ", "
                    . Craft::t("imageoptim", "Optimized")
                    . ": "
                    . $this->humanFileSize($optimizedFileSize, 1)
                    . " -- "
                    . Craft::t("imageoptim", "Savings")
                    . ": "
                    . number_format(abs((1 - ($originalFileSize / $optimizedFileSize )) * 100), 1)
                    . "%",
                    __METHOD__
                );
                // Return the path to the optimized image to _createTransformForAsset()
                $event->tempPath = $tempPath;
            }
        );

        Craft::info("ImageOptim " . Craft::t("imageoptim", "plugin loaded"), __METHOD__);
    }

    // Protected Methods
    // =========================================================================

    protected function humanFileSize($bytes, $decimals = 2): string
    {
        $sz = "BKMGTP";
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[intval($factor)];
    }
}
