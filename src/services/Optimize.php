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
use craft\base\Volume;
use craft\elements\Asset;
use craft\errors\VolumeObjectExistsException;
use craft\events\GenerateTransformEvent;
use craft\helpers\FileHelper;
use craft\models\AssetTransformIndex;
use craft\queue\jobs\ResaveElements;

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
        clearstatcache(true, $tempPath);
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
            . number_format(abs((1 - ($originalFileSize / $optimizedFileSize)) * 100), 1)
            . '%',
            __METHOD__
        );
        // Create any image variants
        $this->createImageVariants(
            $event->transformIndex,
            $event->asset,
            $tempPath
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
                    $this->executeImageProcessor($imageProcessors[$processor], $tempPath);
                }
            }
        }
    }

    /**
     * Create any image variants for the image file
     *
     * @param AssetTransformIndex $index
     * @param Asset               $asset
     * @param string              $tempPath
     */
    public function createImageVariants(AssetTransformIndex $index, Asset $asset, string $tempPath)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active image variant creators
        $activeImageVariantCreators = $settings['activeImageVariantCreators'];
        $fileFormat = $index->detectedFormat;
        if (!empty($activeImageVariantCreators[$fileFormat])) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings['imageVariantCreators'];
            foreach ($activeImageVariantCreators[$fileFormat] as $variantCreator) {
                if (!empty($imageVariantCreators[$variantCreator])) {
                    // Create the image variant in a temporary folder
                    $quality = $index->transform->quality ?: Craft::$app->getConfig()->getGeneral()->defaultImageQuality;
                    $outputPath = $this->executeVariantCreator(
                        $imageVariantCreators[$variantCreator],
                        $tempPath,
                        $quality
                    );
                    // Copy the image variant into place
                    $this->copyImageVariantToVolume(
                        $imageVariantCreators[$variantCreator],
                        $asset,
                        $index,
                        $outputPath
                    );
                }
            }
        }
    }

    /**
     * Return an array of active image processors
     *
     * @return array
     */
    public function getActiveImageProcessors(): array
    {
        $result = [];
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active processors for the transform format
        $activeImageProcessors = $settings['activeImageProcessors'];
        foreach ($activeImageProcessors as $imageFormat => $imageProcessor) {
            // Iterate through all of the processors for this format
            $imageProcessors = $settings['imageProcessors'];
            foreach ($activeImageProcessors[$imageFormat] as $processor) {
                if (!empty($imageProcessors[$processor])) {
                    $thisImageProcessor = $imageProcessors[$processor];
                    $result[] = [
                        'format'  => $imageFormat,
                        'creator' => $processor,
                        'command' => $thisImageProcessor['commandPath']
                            . ' '
                            . $thisImageProcessor['commandOptions'],
                        'installed' => file_exists($thisImageProcessor['commandPath']),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Return an array of active image variant creators
     *
     * @return array
     */
    public function getActiveVariantCreators(): array
    {
        $result = [];
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active image variant creators
        $activeImageVariantCreators = $settings['activeImageVariantCreators'];
        foreach ($activeImageVariantCreators as $imageFormat => $imageCreator) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings['imageVariantCreators'];
            foreach ($activeImageVariantCreators[$imageFormat] as $variantCreator) {
                if (!empty($imageVariantCreators[$variantCreator])) {
                    $thisVariantCreator = $imageVariantCreators[$variantCreator];
                    $result[] = [
                        'format'  => $imageFormat,
                        'creator' => $variantCreator,
                        'command' => $thisVariantCreator['commandPath']
                            . ' '
                            . $thisVariantCreator['commandOptions'],
                        'installed' => file_exists($thisVariantCreator['commandPath']),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Resave all of the Asset elements in the Volume $volume
     *
     * @param Volume $volume
     */
    public function resaveVolumeAssets(Volume $volume)
    {
        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => Craft::t('image-optimize', 'Resaving Assets in {name}', ['name' => $volume->name]),
            'elementType' => Asset::class,
            'criteria'    => [
                'siteId'         => $siteId,
                'volumeId'       => $volume->id,
                'status'         => null,
                'enabledForSite' => false,
            ],
        ]));
    }

    /**
     * Translate bytes into something human-readable
     *
     * @param     $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function humanFileSize($bytes, $decimals = 2): string
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[intval($factor)];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param string  $tempPath
     * @param         $thisProcessor
     */
    protected function executeImageProcessor($thisProcessor, string $tempPath)
    {
        // Make sure the command exists
        if (file_exists($thisProcessor['commandPath'])) {
            // Set any options for the command
            $commandOptions = '';
            if (!empty($thisProcessor['commandOptions'])) {
                $commandOptions = ' '
                    . $thisProcessor['commandOptions']
                    . ' ';
            }
            // Redirect the command output if necessary for this processor
            $outputFileFlag = '';
            if (!empty($thisProcessor['commandOutputFileFlag'])) {
                $outputFileFlag = ' '
                    . $thisProcessor['commandOutputFileFlag']
                    . ' '
                    . escapeshellarg($tempPath)
                    . ' ';
            }
            // Build the command to execute
            $cmd =
                $thisProcessor['commandPath']
                . $commandOptions
                . $outputFileFlag
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

    /**
     * @param         $variantCreatorCommand
     * @param string  $tempPath
     * @param int     $imageQuality
     *
     * @return string the path to the created variant
     */
    protected function executeVariantCreator($variantCreatorCommand, string $tempPath, int $imageQuality): string
    {
        $outputPath = '';
        // Make sure the command exists
        if (file_exists($variantCreatorCommand['commandPath'])) {
            // Get the output file for this image variant
            $outputPath .= '.' . $variantCreatorCommand['imageVariantExtension'];
            // Set any options for the command
            $commandOptions = '';
            if (!empty($variantCreatorCommand['commandOptions'])) {
                $commandOptions = ' '
                    . $variantCreatorCommand['commandOptions']
                    . ' ';
            }
            // Redirect the command output if necessary for this variantCreator
            $outputFileFlag = '';
            if (!empty($variantCreatorCommand['commandOutputFileFlag'])) {
                $outputFileFlag = ' '
                    . $variantCreatorCommand['commandOutputFileFlag']
                    . ' '
                    . escapeshellarg($outputPath)
                    . ' ';
            }
            // Get the quality setting of this transform
            $commandQualityFlag = '';
            if (!empty($variantCreatorCommand['commandQualityFlag'])) {
                $commandQualityFlag = ' '
                    . $variantCreatorCommand['commandQualityFlag']
                    . ' '
                    . $imageQuality
                    . ' ';
            }
            // Build the command to execute
            $cmd =
                $variantCreatorCommand['commandPath']
                . $commandOptions
                . $commandQualityFlag
                . $outputFileFlag
                . escapeshellarg($tempPath);
            // Execute the command
            $shellOutput = $this->executeShellCommand($cmd);
            Craft::info($cmd . "\n" . $shellOutput, __METHOD__);
        } else {
            Craft::error(
                $variantCreatorCommand['commandPath']
                . ' '
                . Craft::t('image-optimize', 'does not exist'),
                __METHOD__
            );
        }

        return $outputPath;
    }

    /**
     * @param                     $variantCreatorCommand
     * @param Asset               $asset
     * @param AssetTransformIndex $index
     * @param                     $outputPath
     */
    protected function copyImageVariantToVolume(
        $variantCreatorCommand,
        Asset $asset,
        AssetTransformIndex $index,
        $outputPath
    ) {
        // If the image variant creation succeeded, copy it into place
        if (!empty($outputPath) && file_exists($outputPath)) {
            // Figure out the resulting path for the image variant
            $volume = $asset->getVolume();
            $assetTransforms = Craft::$app->getAssetTransforms();
            $transformPath = $asset->getFolder()->path . $assetTransforms->getTransformSubpath($asset, $index);
            $transformPath .= '.' . $variantCreatorCommand['imageVariantExtension'];

            // No need to create the file if it already exists
            if ($volume->fileExists($transformPath)) {
                return;
            }
            clearstatcache(true, $outputPath);

            $stream = fopen($outputPath, 'rb');

            try {
                $volume->createFileByStream($transformPath, $stream, []);
            } catch (VolumeObjectExistsException $e) {
                // We're fine with that.
            }

            FileHelper::removeFile($outputPath);
        } else {
            Craft::error(
                Craft::t('image-optimize', 'Failed to create image variant at: ')
                . $outputPath,
                __METHOD__
            );
        }
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

    /**
     * @param string $path
     * @param string $extension
     *
     * @return string
     */
    protected function swapPathExtension(string $path, string $extension): string
    {
        $pathParts = pathinfo($path);
        $newPath = $pathParts['filename'] . '.' . $extension;
        if (!empty($pathParts['dirname']) && $pathParts['dirname'] !== '.') {
            $newPath = $pathParts['dirname'] . DIRECTORY_SEPARATOR . $newPath;
            $newPath = preg_replace('#/+#', '/', $newPath);
        }

        return $newPath;
    }
}
