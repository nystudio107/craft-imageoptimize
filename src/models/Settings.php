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
use craft\validators\ArrayValidator;

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
     * What transform method should be used for image transforms?
     *
     * @var string
     */
    public $transformMethod = 'craft';

    /**
     * Domain for the Imgix transform service
     *
     * @var string
     */
    public $imgixDomain = '';

    /**
     * API Key for the Imgix transform service
     *
     * @var string
     */
    public $imgixApiKey = '';

    /**
     * Should image variant be created on Asset save (aka BeforePageLoad)
     *
     * @var bool
     */
    public $generateTransformsBeforePageLoad = true;

    /**
     * Controls whether a dominant color palette should be created for image
     * variants It takes a bit of time, so if you never plan to use it, you can
     * turn it off
     *
     * @var bool
     */
    public $createColorPalette = true;

    /**
     * Controls whether SVG placeholder silhouettes should be created for image
     * variants It takes a bit of time, so if you never plan to use them, you
     * can turn it off
     *
     * @var bool
     */
    public $createPlaceholderSilhouettes = true;

    /**
     * Controls whether retina images are automatically created with reduced quality
     * as per https://www.netvlies.nl/tips-updates/design-interactie/design-interactie/retina-revolution/
     *
     * @var bool
     */
    public $lowerQualityRetinaImageVariants = true;

    /**
     * Controls whether Optimized Image Variants be created that would be up-scaled
     * to be larger than the original source image
     *
     * @var bool
     */
    public $allowUpScaledImageVariants = false;

    /**
     * Default aspect ratios
     *
     * @var array
     */
    public $defaultAspectRatios = [
        ['x' => 16, 'y' => 9],
        ['x' => 8, 'y' => 5],
        ['x' => 4, 'y' => 3],
        ['x' => 5, 'y' => 4],
        ['x' => 1, 'y' => 1],
        ['x' => 2, 'y' => 2],
        ['x' => 9, 'y' => 16],
        ['x' => 5, 'y' => 8],
        ['x' => 3, 'y' => 4],
        ['x' => 4, 'y' => 5],
    ];

    /**
     * Default variants
     *
     * @var array
     */
    public $defaultVariants = [
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
    ];

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
     * Active image variant creators
     *
     * @var array
     */
    public $activeImageVariantCreators = [
        'jpg' => [
            'cwebp',
        ],
        'png' => [
            'cwebp',
        ],
        'gif' => [
            'cwebp',
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
            'commandOptions'        => '--strip --skip-if-larger',
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
    ];

    public $imageVariantCreators = [
        // webp variant creator
        'cwebp' => [
            'commandPath'           => '/usr/bin/cwebp',
            'commandOptions'        => '',
            'commandOutputFileFlag' => '-o',
            'commandQualityFlag'    => '-q',
            'imageVariantExtension' => 'webp',
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
            ['transformMethod', 'string'],
            ['transformMethod', 'default', 'value' => 'craft'],
            ['imgixDomain', 'string'],
            ['imgixDomain', 'default', 'value' => ''],
            ['imgixApiKey', 'string'],
            ['imgixApiKey', 'default', 'value' => ''],
            [
                [
                    'generateTransformsBeforePageLoad',
                    'createColorPalette',
                    'createPlaceholderSilhouettes',
                ],
                'boolean'
            ],
            ['generateTransformsBeforePageLoad', 'default', 'value' => true],
            ['createColorPalette', 'default', 'value' => true],
            ['createPlaceholderSilhouettes', 'default', 'value' => true],
            [
                [
                    'defaultVariants',
                    'activeImageProcessors',
                    'activeImageVariantCreators',
                    'imageProcessors',
                    'imageVariantCreators',
                ],
                'required',
            ],
            [
                [
                    'defaultVariants',
                    'activeImageProcessors',
                    'activeImageVariantCreators',
                    'imageProcessors',
                    'imageVariantCreators',
                ],
                ArrayValidator::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        // Only return user-editable settings
        $fields = [
            'transformMethod',
            'imgixDomain',
            'imgixApiKey'
        ];

        return $fields;
    }

}
