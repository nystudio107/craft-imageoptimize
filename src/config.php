<?php
/**
 * ImageOptim plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

/**
 * ImageOptim config.php
 *
 * Completely optional configuration settings for ImageOptim if you want to
 * customize some of its more esoteric behavior, or just want specific control
 * over things.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'imageoptim.php'
 * and make your changes there.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [

    // Active image processors
    "activeProcessors" => [
        "jpg" => [
            "jpegoptim",
        ],
        "png" => [
            "optipng",
        ],
        "svg" => [
            "svgo",
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
    ],

];
