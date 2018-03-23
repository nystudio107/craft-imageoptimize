<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

/**
 * ImageOptimize config.php
 *
 * This file exists only as a template for the ImageOptimize settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as
 * 'image-optimize.php' and make your changes there to override default
 * settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just
 * as
 * you do for 'general.php'
 */

return [
    //  What transform method should be used for image transforms?
    'transformMethod' => 'craft',

    // Domain for the Imgix transform service
    'imgixDomain' => '',

    // API key for the Imgix transform service
    'imgixApiKey' => '',

    // The optional security token used to sign image URLs from Imgix
    'imgixSecurityToken' => '',

    // Should the image variants in an Asset Volume be automatically re-saved when saving
    // an OptimizedImages field, saving an Asset Volume that has an OptimizedImages field
    // in its layout, or saving the ImageOptimized settings. Set this to false only if
    // you will be manually using the CLI console command to resave image variants
    'automaticallyResaveImageVariants' => true,

    // Should image variant be created on Asset save (aka BeforePageLoad)
    'generateTransformsBeforePageLoad' => true,

    // Set to false to disable all placeholder generation
    'generatePlaceholders' => true,

    // Controls whether a dominant color palette should be created for image variants
    // It takes a bit of time, so if you never plan to use it, you can turn it off
    'createColorPalette' => true,

     // Controls whether SVG placeholder silhouettes should be created for image variants
     // It takes a bit of time, so if you never plan to use them, you can turn it off
    'createPlaceholderSilhouettes' => true,

    // Controls whether retina images are automatically created with reduced quality
    // as per https://www.netvlies.nl/tips-updates/design-interactie/design-interactie/retina-revolution/
    'lowerQualityRetinaImageVariants' => true,

    // Controls whether Optimized Image Variants are created that would be up-scaled
    // to be larger than the original source image
    'allowUpScaledImageVariants' => false,

    // Controls whether images scaled down >= 50% should be automatically sharpened
    'autoSharpenScaledImages' => true,

    // Default aspect ratios
    'defaultAspectRatios' => [
        ['x' => 16, 'y' => 9],
        ['x' => 8, 'y' => 5],
        ['x' => 4, 'y' => 3],
        ['x' => 5, 'y' => 4],
        ['x' => 1, 'y' => 1],
        ['x' => 9, 'y' => 16],
        ['x' => 5, 'y' => 8],
        ['x' => 3, 'y' => 4],
        ['x' => 4, 'y' => 5],
    ],

    // Default image variants
    'defaultVariants'            => [
        [
            'width'          => 1200,
            'useAspectRatio' => true,
            'aspectRatioX'   => 16.0,
            'aspectRatioY'   => 9.0,
            'retinaSizes'    => ['1'],
            'quality'        => 82,
            'format'         => 'jpg',
        ],
        [
            'width'          => 992,
            'useAspectRatio' => true,
            'aspectRatioX'   => 16.0,
            'aspectRatioY'   => 9.0,
            'retinaSizes'    => ['1'],
            'quality'        => 82,
            'format'         => 'jpg',
        ],
        [
            'width'          => 768,
            'useAspectRatio' => true,
            'aspectRatioX'   => 4.0,
            'aspectRatioY'   => 3.0,
            'retinaSizes'    => ['1'],
            'quality'        => 60,
            'format'         => 'jpg',
        ],
        [
            'width'          => 576,
            'useAspectRatio' => true,
            'aspectRatioX'   => 4.0,
            'aspectRatioY'   => 3.0,
            'retinaSizes'    => ['1'],
            'quality'        => 60,
            'format'         => 'jpg',
        ],
    ],

    // Active image processors
    'activeImageProcessors'      => [
        'jpg' => [
            'jpegoptim',
        ],
        'png' => [
            'optipng',
        ],
        'svg' => [
            'svgo',
        ],
        'gif' => [
            'gifsicle',
        ],
    ],

    // Active image variant creators
    'activeImageVariantCreators' => [
        'jpg' => [
            'cwebp',
        ],
        'png' => [
            'cwebp',
        ],
        'gif' => [
            'cwebp',
        ],
    ],

    // Preset image processors
    'imageProcessors'            => [
        // jpeg optimizers
        'jpegoptim' => [
            'commandPath'           => '/usr/bin/jpegoptim',
            'commandOptions'        => '-s',
            'commandOutputFileFlag' => '',
        ],
        'mozjpeg'   => [
            'commandPath'           => '/usr/bin/mozjpeg',
            'commandOptions'        => '-optimize -copy none',
            'commandOutputFileFlag' => '-outfile',
        ],
        'jpegtran'  => [
            'commandPath'           => '/usr/bin/jpegtran',
            'commandOptions'        => '-optimize -copy none',
            'commandOutputFileFlag' => '',
        ],
        // png optimizers
        'optipng'   => [
            'commandPath'           => '/usr/bin/optipng',
            'commandOptions'        => '-o3 -strip all',
            'commandOutputFileFlag' => '',
        ],
        'pngcrush'  => [
            'commandPath'           => '/usr/bin/pngcrush',
            'commandOptions'        => '-brute -ow',
            'commandOutputFileFlag' => '',
        ],
        'pngquant'  => [
            'commandPath'           => '/usr/bin/pngquant',
            'commandOptions'        => '--strip--skip -if-larger',
            'commandOutputFileFlag' => '',
        ],
        // svg optimizers
        'svgo'      => [
            'commandPath'           => '/usr/bin/svgo',
            'commandOptions'        => '',
            'commandOutputFileFlag' => '',
        ],
        // gif optimizers
        'gifsicle'  => [
            'commandPath'           => '/usr/bin/gifsicle',
            'commandOptions'        => '-O3 -k 256',
            'commandOutputFileFlag' => '',
        ],
    ],

    'imageVariantCreators' => [
        // webp variant creator
        'cwebp' => [
            'commandPath'           => '/usr/bin/cwebp',
            'commandOptions'        => '',
            'commandOutputFileFlag' => '-o',
            'commandQualityFlag'    => '-q',
            'imageVariantExtension' => 'webp',
        ],
    ],

];
