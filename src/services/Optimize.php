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

use Craft;
use craft\base\Component;
use craft\base\Image;
use craft\models\AssetTransformIndex;
use craft\events\GenerateTransformEvent;

use mikehaertl\shellcommand\Command as ShellCommand;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class Optimize extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Handle responding to EVENT_GENERATE_TRANSFORM events
     *
     * @param GenerateTransformEvent $event
     *
     * @return string
     */
    public function handleGenerateTransformEvent(GenerateTransformEvent $event): string
    {
        // Save the transformed image to a temp file
        $tempPath = $this->saveTransformToTempFile(
            $event->transformIndex,
            $event->image
        );
        $originalFileSize = filesize($tempPath);
        // Optimize the image
        $this->optimizeImage(
            $event->transformIndex,
            $tempPath
        );
        // Log the results of the image optimization
        $optimizedFileSize = filesize($tempPath);
        $index = $event->transformIndex;
        Craft::info(
            pathinfo($index->filename, PATHINFO_FILENAME)
            . '.'
            . $index->detectedFormat
            . ' -> '
            . Craft::t('image-optimize', 'Original')
            . ': '
            . $this->humanFileSize($originalFileSize, 1)
            . ', '
            . Craft::t('image-optimize', 'Optimized')
            . ': '
            . $this->humanFileSize($optimizedFileSize, 1)
            . ' -> '
            . Craft::t('image-optimize', 'Savings')
            . ': '
            . number_format(abs((1 - ($originalFileSize / $optimizedFileSize )) * 100), 1)
            . '%',
            __METHOD__
        );

        return $tempPath;
    }

    /**
     * Save out the image to a temp file
     *
     * @param AssetTransformIndex $index
     * @param Image               $image
     *
     * @return string
     */
    public function saveTransformToTempFile(AssetTransformIndex $index, Image $image): string
    {
        $tempFilename = uniqid(pathinfo($index->filename, PATHINFO_FILENAME), true) . '.' . $index->detectedFormat;
        $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $tempFilename;
        $image->saveAs($tempPath);
        Craft::info('Transformed image saved to: ' . $tempPath, __METHOD__);

        return $tempPath;
    }

    /**
     * Run any image post-processing/optimization on the image file
     *
     * @param AssetTransformIndex $index
     * @param string              $tempPath
     */
    public function optimizeImage(AssetTransformIndex $index, string $tempPath)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active processors for the transform format
        $activeImageProcessors = $settings['activeImageProcessors'];
        $fileFormat = $index->detectedFormat;
        if (!empty($activeImageProcessors[$fileFormat])) {
            // Iterate through all of the processors for this format
            $imageProcessors = $settings['imageProcessors'];
            foreach ($activeImageProcessors[$fileFormat] as $processor) {
                if (!empty($imageProcessors[$processor])) {
                    // Make sure the command exists
                    $thisProcessor = $imageProcessors[$processor];
                    if (file_exists($thisProcessor['commandPath'])) {
                        // Build the command to execute
                        $cmd =
                            $thisProcessor['commandPath']
                            . ' '
                            . $thisProcessor['commandOptions']
                            . ' '
                            . escapeshellarg($tempPath);
                        // Execute the command
                        $shellOutput = $this->executeShellCommand($cmd);
                        Craft::info($cmd . "\n" . $shellOutput, __METHOD__);
                    } else {
                        Craft::error(
                            $thisProcessor['commandPath']
                            . ' '
                            . Craft::t('image-optimize', 'does not exist'),
                            __METHOD__
                        );
                    }
                }
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Translate bytes into something human-readable
     *
     * @param     $bytes
     * @param int $decimals
     *
     * @return string
     */
    protected function humanFileSize($bytes, $decimals = 2): string
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[intval($factor)];
    }

    /**
     * Execute a shell command
     *
     * @param string $command
     *
     * @return string
     */
    protected function executeShellCommand(string $command): string
    {
        // Create the shell command
        $shellCommand = new ShellCommand();
        $shellCommand->setCommand($command);

        // If we don't have proc_open, maybe we've got exec
        if (!function_exists('proc_open') && function_exists('exec')) {
            $shellCommand->useExec = true;
        }

        // Return the result of the command's output or error
        if ($shellCommand->execute()) {
            $result = $shellCommand->getOutput();
        } else {
            $result = $shellCommand->getError();
        }

        return $result;
    }
}
