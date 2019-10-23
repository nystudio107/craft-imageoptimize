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
use nystudio107\imageoptimize\imagetransforms\CraftImageTransform;
use nystudio107\imageoptimize\imagetransforms\ImageTransform;
use nystudio107\imageoptimize\imagetransforms\ImageTransformInterface;
use nystudio107\imageoptimizeimgix\imagetransforms\ImgixImageTransform;
use nystudio107\imageoptimizethumbor\imagetransforms\ThumborImageTransform;
use nystudio107\imageoptimizesharp\imagetransforms\SharpImageTransform;

use Craft;
use craft\base\Component;
use craft\base\Image;
use craft\elements\Asset;
use craft\errors\ImageException;
use craft\errors\VolumeException;
use craft\events\AssetTransformImageEvent;
use craft\events\GetAssetThumbUrlEvent;
use craft\events\GetAssetUrlEvent;
use craft\events\GenerateTransformEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\FileHelper;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\Image as ImageHelper;
use craft\image\Raster;
use craft\models\AssetTransform;
use craft\models\AssetTransformIndex;

use mikehaertl\shellcommand\Command as ShellCommand;

use yii\base\InvalidConfigException;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class Optimize extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentTypesEvent The event that is triggered when registering
     *        Image Transform types
     *
     * Image Transform types must implement [[ImageTransformInterface]]. [[ImageTransform]]
     * provides a base implementation.
     *
     * ```php
     * use nystudio107\imageoptimize\services\Optimize;
     * use craft\events\RegisterComponentTypesEvent;
     * use yii\base\Event;
     *
     * Event::on(Optimize::class,
     *     Optimize::EVENT_REGISTER_IMAGE_TRANSFORM_TYPES,
     *     function(RegisterComponentTypesEvent $event) {
     *         $event->types[] = MyImageTransform::class;
     *     }
     * );
     * ```
     */
    const EVENT_REGISTER_IMAGE_TRANSFORM_TYPES = 'registerImageTransformTypes';

    const DEFAULT_IMAGE_TRANSFORM_TYPES = [
        CraftImageTransform::class,
        ImgixImageTransform::class,
        SharpImageTransform::class,
        ThumborImageTransform::class,
    ];

    // Public Methods
    // =========================================================================

    /**
     * Returns all available field type classes.
     *
     * @return string[] The available field type classes
     */
    public function getAllImageTransformTypes(): array
    {
        $imageTransformTypes = array_unique(array_merge(
            ImageOptimize::$plugin->getSettings()->defaultImageTransformTypes ?? [],
            self::DEFAULT_IMAGE_TRANSFORM_TYPES
        ), SORT_REGULAR);

        $event = new RegisterComponentTypesEvent([
            'types' => $imageTransformTypes
        ]);
        $this->trigger(self::EVENT_REGISTER_IMAGE_TRANSFORM_TYPES, $event);

        return $event->types;
    }

    /**
     * Creates an Image Transform with a given config.
     *
     * @param mixed $config The Image Transform’s class name, or its config,
     *                      with a `type` value and optionally a `settings` value
     *
     * @return null|ImageTransformInterface The Image Transform
     */
    public function createImageTransformType($config): ImageTransformInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var ImageTransform $imageTransform */
            $imageTransform = ComponentHelper::createComponent($config, ImageTransformInterface::class);
        } catch (\Throwable $e) {
            $imageTransform = null;
            Craft::error($e->getMessage(), __METHOD__);
        }

        return $imageTransform;
    }

    /**
     * Handle responding to EVENT_GET_ASSET_URL events
     *
     * @param GetAssetUrlEvent $event
     *
     * @return null|string
     * @throws InvalidConfigException
     */
    public function handleGetAssetUrlEvent(GetAssetUrlEvent $event)
    {
        Craft::beginProfile('handleGetAssetUrlEvent', __METHOD__);
        $url = null;
        if (!ImageOptimize::$plugin->transformMethod instanceof CraftImageTransform) {
            $asset = $event->asset;
            $transform = $event->transform;
            // If there's no transform requested, and we can't manipulate the image anyway, just return the URL
            if ($transform === null
                || !ImageHelper::canManipulateAsImage(pathinfo($asset->filename, PATHINFO_EXTENSION))) {
                $volume = $asset->getVolume();

                return AssetsHelper::generateUrl($volume, $asset);
            }
            // If we're passed in null, make a dummy AssetTransform model
            if (empty($transform)) {
                $transform = new AssetTransform([
                    'height'    => $asset->height,
                    'width'     => $asset->width,
                    'interlace' => 'line',
                ]);
            }
            // If we're passed an array, make an AssetTransform model out of it
            if (\is_array($transform)) {
                $transform = new AssetTransform($transform);
            }
            // If we're passing in a string, look up the asset transform in the db
            if (\is_string($transform)) {
                $assetTransforms = Craft::$app->getAssetTransforms();
                $transform = $assetTransforms->getTransformByHandle($transform);
            }
            // Generate an image transform url
            $url = ImageOptimize::$plugin->transformMethod->getTransformUrl(
                $asset,
                $transform
            );
        }
        Craft::endProfile('handleGetAssetUrlEvent', __METHOD__);

        return $url;
    }

    /**
     * Handle responding to EVENT_GET_ASSET_THUMB_URL events
     *
     * @param GetAssetThumbUrlEvent $event
     *
     * @return null|string
     */
    public function handleGetAssetThumbUrlEvent(GetAssetThumbUrlEvent $event)
    {
        Craft::beginProfile('handleGetAssetThumbUrlEvent', __METHOD__);
        $url = $event->url;
        if (!ImageOptimize::$plugin->transformMethod instanceof CraftImageTransform) {
            $asset = $event->asset;
            if (ImageHelper::canManipulateAsImage($asset->getExtension())) {
                $transform = new AssetTransform([
                    'height' => $event->height,
                    'width' => $event->width,
                    'interlace' => 'line',
                ]);
                /** @var ImageTransform $transformMethod */
                $transformMethod = ImageOptimize::$plugin->transformMethod;
                // Generate an image transform url
                if ($transformMethod->hasProperty('generateTransformsBeforePageLoad')) {
                    $transformMethod->generateTransformsBeforePageLoad = $event->generate;
                }
                $url = $transformMethod->getTransformUrl($asset, $transform);
            }
        }
        Craft::endProfile('handleGetAssetThumbUrlEvent', __METHOD__);

        return $url;
    }

    /**
     * Handle responding to EVENT_GENERATE_TRANSFORM events
     *
     * @param GenerateTransformEvent $event
     *
     * @return null|string
     */
    public function handleGenerateTransformEvent(GenerateTransformEvent $event)
    {
        Craft::beginProfile('handleGenerateTransformEvent', __METHOD__);
        $tempPath = null;

        // Only do this for local Craft transforms
        if (ImageOptimize::$plugin->transformMethod instanceof CraftImageTransform && $event->asset !== null) {
            // Apply any filters to the image
            if ($event->transformIndex->transform !== null) {
                $this->applyFiltersToImage($event->transformIndex->transform, $event->asset, $event->image);
            }
            // Save the transformed image to a temp file
            $tempPath = $this->saveTransformToTempFile(
                $event->transformIndex,
                $event->image
            );
            $originalFileSize = @filesize($tempPath);
            // Optimize the image
            $this->optimizeImage(
                $event->transformIndex,
                $tempPath
            );
            clearstatcache(true, $tempPath);
            // Log the results of the image optimization
            $optimizedFileSize = @filesize($tempPath);
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
        Craft::endProfile('handleGenerateTransformEvent', __METHOD__);

        return $tempPath;
    }

    /**
     * Handle cleaning up any variant creator images
     *
     * @param AssetTransformImageEvent $event
     */
    public function handleAfterDeleteTransformsEvent(AssetTransformImageEvent $event)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Only do this for local Craft transforms
        if (ImageOptimize::$plugin->transformMethod instanceof CraftImageTransform && $event->asset !== null) {
            $this->cleanupImageVariants($event->asset, $event->transformIndex);
        }
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
        try {
            $image->saveAs($tempPath);
        } catch (ImageException $e) {
            Craft::error('Transformed image save failed: '.$e->getMessage(), __METHOD__);
        }
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
        Craft::beginProfile('optimizeImage', __METHOD__);
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active processors for the transform format
        $activeImageProcessors = $settings->activeImageProcessors;
        $fileFormat = $index->detectedFormat;
        // Special-case for 'jpeg'
        if ($fileFormat === 'jpeg') {
            $fileFormat = 'jpg';
        }
        if (!empty($activeImageProcessors[$fileFormat])) {
            // Iterate through all of the processors for this format
            $imageProcessors = $settings->imageProcessors;
            foreach ($activeImageProcessors[$fileFormat] as $processor) {
                if (!empty($processor) && !empty($imageProcessors[$processor])) {
                    $this->executeImageProcessor($imageProcessors[$processor], $tempPath);
                }
            }
        }
        Craft::endProfile('optimizeImage', __METHOD__);
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
        Craft::beginProfile('createImageVariants', __METHOD__);
        $settings = ImageOptimize::$plugin->getSettings();
        // Get the active image variant creators
        $activeImageVariantCreators = $settings->activeImageVariantCreators;
        $fileFormat = $index->detectedFormat ?? $index->format;
        // Special-case for 'jpeg'
        if ($fileFormat === 'jpeg') {
            $fileFormat = 'jpg';
        }
        if (!empty($activeImageVariantCreators[$fileFormat])) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings->imageVariantCreators;
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

                    if ($outputPath !== null) {
                        // Get info on the original and the created variant
                        $originalFileSize = @filesize($tempPath);
                        $variantFileSize = @filesize($outputPath);

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
        Craft::endProfile('createImageVariants', __METHOD__);
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

    // Protected Methods
    // =========================================================================

    /** @noinspection PhpUnusedParameterInspection
     * @param AssetTransform $transform
     * @param Asset          $asset
     * @param Image          $image
     */

    protected function applyFiltersToImage(AssetTransform $transform, Asset $asset, Image $image)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        // Only try to apply filters to Raster images
        if ($image instanceof Raster) {
            $imagineImage = $image->getImagineImage();
            if ($imagineImage !== null) {
                // Handle auto-sharpening scaled down images
                if ($settings->autoSharpenScaledImages) {
                    // See if the image has been scaled >= 50%
                    $widthScale = $asset->getWidth() / $image->getWidth();
                    $heightScale = $asset->getHeight() / $image->getHeight();
                    if (($widthScale >= 2.0) || ($heightScale >= 2.0)) {
                        $imagineImage->effects()
                            ->sharpen();
                        Craft::debug(
                            Craft::t(
                                'image-optimize',
                                'Image transform >= 50%, sharpened the transformed image: {name}',
                                [
                                    'name' => $asset->title,
                                ]
                            ),
                            __METHOD__
                        );
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
            // Output to a file name
            $outputFileName = '';
            if (!empty($thisProcessor['commandOutputFileName']) && $thisProcessor['commandOutputFileName']) {
                $outputFileName = ' '
                    .escapeshellarg($tempPath)
                    .' ';
            }
            // Build the command to execute
            $cmd =
                $thisProcessor['commandPath']
                .$commandOptions
                .$outputFileFlag
                .escapeshellarg($tempPath)
                .$outputFileName;
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
        if (!\function_exists('proc_open') && \function_exists('exec')) {
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
     * @param         $variantCreatorCommand
     * @param string  $tempPath
     * @param int     $imageQuality
     *
     * @return string|null the path to the created variant
     */
    protected function executeVariantCreator($variantCreatorCommand, string $tempPath, int $imageQuality)
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
            // Write to a file if necessary for this variantCreator
            $outputFileName = '';
            if (!empty($variantCreatorCommand['commandOutputFileName']) && $variantCreatorCommand['commandOutputFileName']) {
                $outputFileName = ' '
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
                .escapeshellarg($tempPath)
                .$outputFileName;
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
            $outputPath = null;
        }

        return $outputPath;
    }

    /**
     * @param Asset               $asset
     * @param AssetTransformIndex $transformIndex
     */
    protected function cleanupImageVariants(Asset $asset, AssetTransformIndex $transformIndex)
    {
        $settings = ImageOptimize::$plugin->getSettings();
        $assetTransforms = Craft::$app->getAssetTransforms();
        // Get the active image variant creators
        $activeImageVariantCreators = $settings->activeImageVariantCreators;
        $fileFormat = $transformIndex->detectedFormat ?? $transformIndex->format;
        if (!empty($activeImageVariantCreators[$fileFormat])) {
            // Iterate through all of the image variant creators for this format
            $imageVariantCreators = $settings->imageVariantCreators;
            if (!empty($activeImageVariantCreators[$fileFormat])) {
                foreach ($activeImageVariantCreators[$fileFormat] as $variantCreator) {
                    if (!empty($variantCreator) && !empty($imageVariantCreators[$variantCreator])) {
                        // Create the image variant in a temporary folder
                        $variantCreatorCommand = $imageVariantCreators[$variantCreator];
                        try {
                            $volume = $asset->getVolume();
                        } catch (InvalidConfigException $e) {
                            $volume = null;
                            Craft::error(
                                'Asset volume error: '.$e->getMessage(),
                                __METHOD__
                            );
                        }
                        try {
                            $variantPath = $asset->getFolder()->path.$assetTransforms->getTransformSubpath(
                                $asset,
                                $transformIndex
                            );
                        } catch (InvalidConfigException $e) {
                            $variantPath = '';
                            Craft::error(
                                'Asset folder does not exist: '.$e->getMessage(),
                                __METHOD__
                            );
                        }
                        $variantPath .= '.'.$variantCreatorCommand['imageVariantExtension'];
                        // Delete the variant file in case it is stale
                        try {
                            $volume->deleteFile($variantPath);
                        } catch (VolumeException $e) {
                            // We're fine with that.
                        }
                        Craft::info(
                            'Deleted variant: '.$variantPath,
                            __METHOD__
                        );
                    }
                }
            }
        }
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
            try {
                $volume = $asset->getVolume();
            } catch (InvalidConfigException $e) {
                $volume = null;
                Craft::error(
                    'Asset volume error: '.$e->getMessage(),
                    __METHOD__
                );
            }
            $assetTransforms = Craft::$app->getAssetTransforms();
            try {
                $transformPath = $asset->getFolder()->path.$assetTransforms->getTransformSubpath($asset, $index);
            } catch (InvalidConfigException $e) {
                $transformPath = '';
                Craft::error(
                    'Error getting asset folder: '.$e->getMessage(),
                    __METHOD__
                );
            }
            $variantPath = $transformPath.'.'.$variantCreatorCommand['imageVariantExtension'];

            // Delete the variant file in case it is stale
            try {
                $volume->deleteFile($variantPath);
            } catch (VolumeException $e) {
                // We're fine with that.
            }

            Craft::info(
                'Variant output path: '.$outputPath.' - Variant path: '.$variantPath,
                __METHOD__
            );

            clearstatcache(true, $outputPath);
            $stream = @fopen($outputPath, 'rb');
            if ($stream !== false) {
                // Now create it
                try {
                    $volume->createFileByStream($variantPath, $stream, []);
                } catch (VolumeException $e) {
                    Craft::error(
                        Craft::t('image-optimize', 'Failed to create image variant at: ')
                        .$outputPath,
                        __METHOD__
                    );
                }

                FileHelper::unlink($outputPath);
            }
        } else {
            Craft::error(
                Craft::t('image-optimize', 'Failed to create image variant at: ')
                .$outputPath,
                __METHOD__
            );
        }
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
