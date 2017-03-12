<?php
/**
 * ImageOptim plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptim\services;

use nystudio107\imageoptim\ImageOptim;

use Craft;
use craft\base\Component;
use craft\base\Image;
use craft\models\AssetTransformIndex;

use mikehaertl\shellcommand\Command as ShellCommand;

/**
 * @author    nystudio107
 * @package   ImageOptim
 * @since     1.0.0
 */
class Optimize extends Component
{
    // Public Methods
    // =========================================================================

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
        $tempFilename = uniqid(pathinfo($index->filename, PATHINFO_FILENAME), true) . "." . $index->detectedFormat;
        $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $tempFilename;
        $image->saveAs($tempPath);
        Craft::info("Transformed image saved to: " . $tempPath, __METHOD__);

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
        // Get the active processors for the transform format
        $activeProcessors = Craft::$app->config->get("activeProcessors", "imageoptim");
        $fileFormat = $index->detectedFormat;
        if (!empty($activeProcessors[$fileFormat])) {
            // Iterate through all of the processors for this format
            $imageProcessors = Craft::$app->config->get("imageProcessors", "imageoptim");
            foreach ($activeProcessors[$fileFormat] as $processor) {
                if (!empty($imageProcessors[$processor])) {
                    // Make sure the command exists
                    $thisProcessor = $imageProcessors[$processor];
                    if (file_exists($thisProcessor["commandPath"])) {
                        // Build the command to execute
                        $cmd =
                            $thisProcessor["commandPath"]
                            . " "
                            . $thisProcessor["commandOptions"]
                            . " "
                            . escapeshellarg($tempPath);
                        // Execute the command
                        $shellOutput = $this->_executeShellCommand($cmd);
                        Craft::info($cmd."\n" . $shellOutput, __METHOD__);
                    } else {
                        Craft::error(
                            $thisProcessor["commandPath"]
                            . " "
                            . Craft::t("imageoptim", "does not exist"),
                            __METHOD__
                        );
                    }
                }
            }
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Execute a shell command
     *
     * @param string $command
     *
     * @return string
     */
    private function _executeShellCommand(string $command): string
    {
        // Create the shell command
        $shellCommand = new ShellCommand();
        $shellCommand->setCommand($command);

        // If we don't have proc_open, maybe we've got exec
        if (!function_exists("proc_open") && function_exists("exec")) {
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
