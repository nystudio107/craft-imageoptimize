<?php
/**
 * ImageOptimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\models;

use craft\base\Model;

/**
 * ImageOptimize Settings model
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Active image processors
     *
     * @var array
     */
    public $activeImageProcessors = [
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
    ];

    /**
     * Preset image processors
     *
     * @var array
     */
    public $imageProcessors = [
        // jpeg optimizers
        'jpegoptim' => [
            'commandPath'    => '/usr/bin/jpegoptim',
            'commandOptions' => '-s',
        ],
        'mozjpeg'   => [
            'commandPath'    => '/usr/bin/mozjpeg',
            'commandOptions' => '-optimize -copy none',
        ],
        'jpegtran'  => [
            'commandPath'    => '/usr/bin/jpegtran',
            'commandOptions' => '-optimize -copy none',
        ],
        // png optimizers
        'optipng'   => [
            'commandPath'    => '/usr/bin/optipng',
            'commandOptions' => '-o7 -strip all',
        ],
        'pngcrush'  => [
            'commandPath'    => '/usr/bin/pngcrush',
            'commandOptions' => '-brute -ow',
        ],
        'pngquant'  => [
            'commandPath'    => '/usr/bin/pngquant',
            'commandOptions' => '--strip --skip-if-larger',
        ],
        // svg optimizers
        'svgo'      => [
            'commandPath'    => '/usr/bin/svgo',
            'commandOptions' => '',
        ],
        // gif optimizers
        'gifsicle'  => [
            'commandPath'    => '/usr/bin/gifsicle',
            'commandOptions' => '-O3 -k 256',
        ],
    ];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['activeImageProcessors', 'required'],
            ['imageProcessors', 'required'],
        ];
    }
}
