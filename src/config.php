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
 * Don't edit this file, instead copy it to 'craft/config' as 'imageoptimize.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [

    // Active image processors
    "activeImageProcessors" => [
        "jpg" => [
            "jpegoptim",
        ],
        "png" => [
            "optipng",
        ],
        "svg" => [
            "svgo",
        ],
        "gif" => [
            "gifsicle",
        ],
        "webp" => [
            "cwebp",
        ],
    ],

    // Preset image processors
    "imageProcessors" => [
        // jpeg optimizers
        "jpegoptim" => [
            "commandPath" => "/usr/bin/jpegoptim",
            "commandOptions" => "-s",
        ],
        "mozjpeg" => [
            "commandPath" => "/usr/bin/mozjpeg",
            "commandOptions" => "-optimize -copy none",
        ],
        "jpegtran" => [
            "commandPath" => "/usr/bin/jpegtran",
            "commandOptions" => "-optimize -copy none",
        ],
        // png optimizers
        "optipng" => [
            "commandPath" => "/usr/bin/optipng",
            "commandOptions" => "-o7 -strip all",
        ],
        "pngcrush" => [
            "commandPath" => "/usr/bin/pngcrush",
            "commandOptions" => "-brute -ow",
        ],
        "pngquant" => [
            "commandPath" => "/usr/bin/pngquant",
            "commandOptions" => "--strip --skip-if-larger",
        ],
        // svg optimizers
        "svgo" => [
            "commandPath" => "/usr/bin/svgo",
            "commandOptions" => "",
        ],
        // gif optimizers
        "gifsicle" => [
            "commandPath" => "/usr/bin/gifsicle",
            "commandOptions" => "-O3 -k 256",
        ],
        // webp optimizers
        "cwebp" => [
            "commandPath" => "/usr/bin/cwebp",
            "commandOptions" => "",
        ],
    ],

];
