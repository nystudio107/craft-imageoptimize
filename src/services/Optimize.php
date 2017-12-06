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
use nystudio107\imageoptimize\fields\OptimizedImages;

use Craft;
use craft\base\Component;
use craft\base\Image;
use craft\base\Volume;
use craft\elements\Asset;
use craft\errors\VolumeException;
use craft\errors\VolumeObjectExistsException;
use craft\events\GetAssetUrlEvent;
use craft\events\GenerateTransformEvent;
use craft\helpers\FileHelper;
use craft\models\AssetTransformIndex;
use craft\models\FieldLayout;
use craft\queue\jobs\ResaveElements;

use mikehaertl\shellcommand\Command as ShellCommand;

/** @noinspection MissingPropertyAnnotationsInspection */

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
     * Handle responding to EVENT_GET_ASSET_URL events
     *
     * @param GetAssetUrlEvent $event
     *
     * @return string
     */
    public function handleGetAssetUrlEvent(GetAssetUrlEvent $event)
    {
        $url = null;
        $settings = ImageOptimize::$plugin->getSettings();
        if ($settings->transformMethod != 'craft') {
            // Generate an image transform url
            $url = ImageOptimize::$transformClass::getTransformUrl(
                $event->asset,
                $event->transform,
                ImageOptimize::$transformParams
            );
        }

        return $url;
    }

    /**
     * Handle responding to EVENT_GENERATE_TRANSFORM events
     *
     * @param GenerateTransformEvent $event
     *
     * @return string
     */
    public function handleGenerateTransformEvent(GenerateTransformEvent $event): string
    {
        $tempPath = null;

        $settings = ImageOptimize::$plugin->getSettings();
        // Only do this for local Craft transforms
        if ($settings->transformMethod == 'craft') {
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
                .'.'
                .$index->detectedFormat
                .' -> '
                .Craft::t('image-optimize', 'Original')
                .': '
                .$this->humanFileSize($originalFileSize, 1)
                .', '
                .Craft::t('image-optimize', 'Optimized')
                .': '
                .$this->humanFileSize($optimizedFileSize, 1)
                .' -> '
                .Craft::t('image-optimize', 'Savings')
                .': '
                .number_format(abs(100 - (($optimizedFileSize * 100) / $originalFileSize)), 1)
                .'%',
                __METHOD__
            );
            // Create any image variants
            $this->createImageVariants(
                $event->transformIndex,
                $event->asset,
                $tempPath
            );
        }

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
        $tempFilename = uniqid(pathinfo($index->filename, PATHINFO_FILENAME), true).'.'.$index->detectedFormat;
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;
        $image->saveAs($tempPath);
        Craft::info('Transformed image saved to: '.$tempPath, __METHOD__);

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
        $activeImageProcessors = $settings->activeImageProcessors;
        $fileFormat = $index->detectedFormat;
        if (!empty($activeImageProcessors[$fileFormat])) {
            // Iterate through all of the processors for this format
            $imageProcessors = $settings->imageProcessors;
            if (!empty($activeImageProcessors[$fileFormat])) {
                foreach ($activeImageProcessors[$fileFormat] as $processor) {
                    if (!empty($processor) && !empty($imageProcessors[$processor])) {
                        $this->executeImageProcessor($imageProcessors[$processor], $tempPath);
                    }
                }
            }
        }
    }

    /**
     * @param string  $tempPath
     * @param         $thisProcessor
     */
    protected function executeImageProcessor($thisProcessor, string $tempPath)
    {
        // Make sure the command exists
        if (is_file($thisProcessor['commandPath'])) {
            // Set any options for the command
            $commandOptions = '';
            if (!empty($thisProcessor['commandOptions'])) {
                $commandOptions = ' '
                    .$thisProcessor['commandOptions']
                    .' ';
            }
            // Redirect the command output if necessary for this processor
            $outputFileFlag = '';
            if (!empty($thisProcessor['commandOutputFileFlag'])) {
                $outputFileFlag = ' '
                    .$thisProcessor['commandOutputFileFlag']
                    .' '
                    .escapeshellarg($tempPath)
                    .' ';
            }
            // Build the command to execute
            $cmd =
                $thisProcessor['commandPath']
                .$commandOptions
                .$outputFileFlag
                .escapeshellarg($tempPath);
            // Execute the command
            $shellOutput = $this->executeShellCommand($cmd);
            Craft::info($cmd."\n".$shellOutput, __METHOD__);
        } else {
            Craft::error(
                $thisProcessor['commandPath']
                .' '
                .Craft::t('image-optimize', 'does not exist'),
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
     * Translate bytes into something human-readable
     *
     * @param     $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function humanFileSize($bytes, $decimals = 1): string
    {
        $oldSize = Craft::$app->formatter->sizeFormatBase;
        Craft::$app->formatter->sizeFormatBase = 1000;
        $result = Craft::$app->formatter->asShortSize($bytes, $decimals);
        Craft::$app->formatter->sizeFormatBase = $oldSize;

        return $result;
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
        $activeImageVariantCreators = $settings->activeImageVariantCreators;
        $fileFormat = $index->detectedFormat;
        if (!empty($activeImageVariantCreators[$fileFormat])) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings->imageVariantCreators;
            if (!empty($activeImageVariantCreators[$fileFormat])) {
                foreach ($activeImageVariantCreators[$fileFormat] as $variantCreator) {
                    if (!empty($variantCreator) && !empty($imageVariantCreators[$variantCreator])) {
                        // Create the image variant in a temporary folder
                        $generalConfig = Craft::$app->getConfig()->getGeneral();
                        $quality = $index->transform->quality ?: $generalConfig->defaultImageQuality;
                        $outputPath = $this->executeVariantCreator(
                            $imageVariantCreators[$variantCreator],
                            $tempPath,
                            $quality
                        );

                        // Get info on the original and the created variant
                        $originalFileSize = filesize($tempPath);
                        $variantFileSize = filesize($outputPath);

                        Craft::info(
                            pathinfo($tempPath, PATHINFO_FILENAME)
                            .'.'
                            .pathinfo($tempPath, PATHINFO_EXTENSION)
                            .' -> '
                            .pathinfo($outputPath, PATHINFO_FILENAME)
                            .'.'
                            .pathinfo($outputPath, PATHINFO_EXTENSION)
                            .' -> '
                            .Craft::t('image-optimize', 'Original')
                            .': '
                            .$this->humanFileSize($originalFileSize, 1)
                            .', '
                            .Craft::t('image-optimize', 'Variant')
                            .': '
                            .$this->humanFileSize($variantFileSize, 1)
                            .' -> '
                            .Craft::t('image-optimize', 'Savings')
                            .': '
                            .number_format(abs(100 - (($variantFileSize * 100) / $originalFileSize)), 1)
                            .'%',
                            __METHOD__
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
        $outputPath = $tempPath;
        // Make sure the command exists
        if (is_file($variantCreatorCommand['commandPath'])) {
            // Get the output file for this image variant
            $outputPath .= '.'.$variantCreatorCommand['imageVariantExtension'];
            // Set any options for the command
            $commandOptions = '';
            if (!empty($variantCreatorCommand['commandOptions'])) {
                $commandOptions = ' '
                    .$variantCreatorCommand['commandOptions']
                    .' ';
            }
            // Redirect the command output if necessary for this variantCreator
            $outputFileFlag = '';
            if (!empty($variantCreatorCommand['commandOutputFileFlag'])) {
                $outputFileFlag = ' '
                    .$variantCreatorCommand['commandOutputFileFlag']
                    .' '
                    .escapeshellarg($outputPath)
                    .' ';
            }
            // Get the quality setting of this transform
            $commandQualityFlag = '';
            if (!empty($variantCreatorCommand['commandQualityFlag'])) {
                $commandQualityFlag = ' '
                    .$variantCreatorCommand['commandQualityFlag']
                    .' '
                    .$imageQuality
                    .' ';
            }
            // Build the command to execute
            $cmd =
                $variantCreatorCommand['commandPath']
                .$commandOptions
                .$commandQualityFlag
                .$outputFileFlag
                .escapeshellarg($tempPath);
            // Execute the command
            $shellOutput = $this->executeShellCommand($cmd);
            Craft::info($cmd."\n".$shellOutput, __METHOD__);
        } else {
            Craft::error(
                $variantCreatorCommand['commandPath']
                .' '
                .Craft::t('image-optimize', 'does not exist'),
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
        if (!empty($outputPath) && is_file($outputPath)) {
            // Figure out the resulting path for the image variant
            $volume = $asset->getVolume();
            $assetTransforms = Craft::$app->getAssetTransforms();
            $transformPath = $asset->getFolder()->path.$assetTransforms->getTransformSubpath($asset, $index);
            $variantPath = $transformPath.'.'.$variantCreatorCommand['imageVariantExtension'];

            // Delete the variant file in case it is stale
            try {
                $volume->deleteFile($variantPath);
            } catch (VolumeException $e) {
                // We're fine with that.
            }

            clearstatcache(true, $outputPath);
            $stream = @fopen($outputPath, 'rb');

            // Now create it
            try {
                $volume->createFileByStream($variantPath, $stream, []);
            } catch (VolumeObjectExistsException $e) {
                // We're fine with that.
            }

            FileHelper::removeFile($outputPath);
        } else {
            Craft::error(
                Craft::t('image-optimize', 'Failed to create image variant at: ')
                .$outputPath,
                __METHOD__
            );
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
        $activeImageProcessors = $settings->activeImageProcessors;
        foreach ($activeImageProcessors as $imageFormat => $imageProcessor) {
            // Iterate through all of the processors for this format
            $imageProcessors = $settings->imageProcessors;
            foreach ($activeImageProcessors[$imageFormat] as $processor) {
                if (!empty($imageProcessors[$processor])) {
                    $thisImageProcessor = $imageProcessors[$processor];
                    $result[] = [
                        'format'    => $imageFormat,
                        'creator'   => $processor,
                        'command'   => $thisImageProcessor['commandPath']
                            .' '
                            .$thisImageProcessor['commandOptions'],
                        'installed' => is_file($thisImageProcessor['commandPath']),
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
        $activeImageVariantCreators = $settings->activeImageVariantCreators;
        foreach ($activeImageVariantCreators as $imageFormat => $imageCreator) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings->imageVariantCreators;
            foreach ($activeImageVariantCreators[$imageFormat] as $variantCreator) {
                if (!empty($imageVariantCreators[$variantCreator])) {
                    $thisVariantCreator = $imageVariantCreators[$variantCreator];
                    $result[] = [
                        'format'    => $imageFormat,
                        'creator'   => $variantCreator,
                        'command'   => $thisVariantCreator['commandPath']
                            .' '
                            .$thisVariantCreator['commandOptions'],
                        'installed' => is_file($thisVariantCreator['commandPath']),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Re-save all of the assets in all of the volumes
     */
    public function resaveAllVolumesAssets()
    {
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        foreach ($volumes as $volume) {
            if (is_subclass_of($volume, Volume::class)) {
                /** @var Volume $volume */
                $this->resaveVolumeAssets($volume);
            }
        }
    }

    /**
     * Re-save all of the Asset elements in the Volume $volume that have an
     * OptimizedImages field in the FieldLayout
     *
     * @param Volume $volume
     *
     * @throws \craft\errors\SiteNotFoundException
     */
    public function resaveVolumeAssets(Volume $volume)
    {
        $needToReSave = false;
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $volume->getFieldLayout();
        // Loop through the fields in the layout to see if there is an OptimizedImages field
        if ($fieldLayout) {
            $fields = $fieldLayout->getFields();
            foreach ($fields as $field) {
                if ($field instanceof OptimizedImages) {
                    $needToReSave = true;
                }
            }
        }
        if ($needToReSave) {
            $siteId = Craft::$app->getSites()->getPrimarySite()->id;

            $queue = Craft::$app->getQueue();
            $jobId = $queue->push(new ResaveElements([
                'description' => Craft::t('image-optimize', 'Resaving Assets in {name}', ['name' => $volume->name]),
                'elementType' => Asset::class,
                'criteria'    => [
                    'siteId'         => $siteId,
                    'volumeId'       => $volume->id,
                    'status'         => null,
                    'enabledForSite' => false,
                ],
            ]));
            Craft::trace(
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
     */
    public function resaveAsset(int $id)
    {
        $queue = Craft::$app->getQueue();
        $jobId = $queue->push(new ResaveElements([
            'description' => Craft::t('image-optimize', 'Resaving new Asset id {id}', ['id' => $id]),
            'elementType' => Asset::class,
            'criteria'    => [
                'id'             => $id,
                'status'         => null,
                'enabledForSite' => false,
            ],
        ]));
        // @TODO: Run this queue immediately, so we don't have to wait for the next request
        //$queue->run();
        Craft::trace(
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

    // Protected Methods
    // =========================================================================

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
            // put spaces back in
            '/%20/' => ' ',
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

    /**
     * @param string $path
     * @param string $extension
     *
     * @return string
     */
    protected function swapPathExtension(string $path, string $extension): string
    {
        $pathParts = pathinfo($path);
        $newPath = $pathParts['filename'].'.'.$extension;
        if (!empty($pathParts['dirname']) && $pathParts['dirname'] !== '.') {
            $newPath = $pathParts['dirname'].DIRECTORY_SEPARATOR.$newPath;
            $newPath = preg_replace('#/+#', '/', $newPath);
        }

        return $newPath;
    }
}
