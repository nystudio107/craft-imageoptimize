<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace nystudio107\imageoptimize\gql\types\generators;

use nystudio107\imageoptimize\gql\types\OptimizedImagesType;
use nystudio107\imageoptimize\fields\OptimizedImages;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;

use GraphQL\Type\Definition\Type;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.2
 */
class OptimizedImagesGenerator implements GeneratorInterface
{
    // Public Static methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        /** @var OptimizedImages $context */
        $typeName = self::getName($context);

        $optimizedImagesFields = [
            // static fields
            'optimizedImageUrls' => [
                'name' => 'optimizedImageUrls',
                'description' => 'An array of optimized image variant URLs',
                'type' => Type::listOf(Type::string()),
            ],
            'optimizedWebPImageUrls' => [
                'name' => 'optimizedWebPImageUrls',
                'description' => 'An array of optimized .webp image variant URLs',
                'type' => Type::listOf(Type::string()),
            ],
            'variantSourceWidths' => [
                'name' => 'variantSourceWidths',
                'description' => 'An array of the widths of the optimized image variants',
                'type' => Type::listOf(Type::int()),
            ],
            'variantHeights' => [
                'name' => 'variantHeights',
                'description' => 'An array of the heights of the optimized image variants',
                'type' => Type::listOf(Type::int()),
            ],
            'focalPoint' => [
                'name' => 'focalPoint',
                'description' => 'An array of the x,y image focal point coords, ranging from 0.0 to 1.0',
                'type' => Type::listOf(Type::float()),
            ],
            'originalImageWidth' => [
                'name' => 'originalImageWidth',
                'description' => 'The width of the original source image',
                'type' => Type::int(),
            ],
            'originalImageHeight' => [
                'name' => 'originalImageHeight',
                'description' => 'The height of the original source image',
                'type' => Type::int(),
            ],
            'placeholder' => [
                'name' => 'placeholder',
                'description' => 'The base64 encoded placeholder LQIP image',
                'type' => Type::string(),
            ],
            'placeholderSvg' => [
                'name' => 'placeholderSvg',
                'description' => 'The base64 encoded placeholder LQIP SVG image',
                'type' => Type::string(),
            ],
            'colorPalette' => [
                'name' => 'colorPalette',
                'description' => 'An array the 5 most dominant colors in the image',
                'type' => Type::listOf(Type::string()),
            ],
            'colorPaletteRgb' => [
                'name' => 'colorPaletteRgb',
                'description' => 'An array the 5 most dominant colors in the image in RGB format',
                'type' => Type::listOf(Type::listOf(Type::int())),
            ],
            'lightness' => [
                'name' => 'lightness',
                'description' => 'The overall lightness of the image, from 0..100',
                'type' => Type::int(),
            ],
            'placeholderWidth' => [
                'name' => 'placeholderWidth',
                'description' => 'The width of the placeholder image',
                'type' => Type::int(),
            ],
            'placeholderHeight' => [
                'name' => 'placeholderHeight',
                'description' => 'The height of the placeholder image',
                'type' => Type::int(),
            ],
            // Dynamic fields
            'srcUrls' => [
                'name' => 'srcUrls',
                'description' => 'Return the first image variant URL or the specific one passed in via `width`',
                'type' => Type::listOf(Type::listOf(Type::string())),
            ],
            'maxSrcsetWidth' => [
                'name' => 'maxSrcsetWidth',
                'description' => 'Work around issues with `<img srcset>` returning sizes larger than are available',
                'type' => Type::int(),
            ],
            'placeholderImage' => [
                'name' => 'placeholderImage',
                'description' => 'Return a base64-encoded placeholder image',
                'type' => Type::string(),
            ],
            'placeholderSilhouette' => [
                'name' => 'placeholderSilhouette',
                'description' => 'Return a silhouette of the image as an SVG placeholder',
                'type' => Type::string(),
            ],
            // Dynamic fields with arguments
            'src' => [
                'name' => 'src',
                'description' => 'Return the first image variant URL or the specific one passed in via `width`',
                'args' => [
                    'width' => [
                        'name' => 'width',
                        'type' => Type::int(),
                        'description' => 'Width of the image'
                    ],
                ],
                'type' => Type::string(),
            ],
            'srcWebp' => [
                'name' => 'srcWebp',
                'description' => 'Return the first webp image variant URL or the specific one passed in via `width`',
                'args' => [
                    'width' => [
                        'name' => 'width',
                        'type' => Type::int(),
                        'description' => 'Width of the image'
                    ],
                ],
                'type' => Type::string(),
            ],
            'srcset' => [
                'name' => 'srcset',
                'description' => 'Return a string of image URLs and their sizes',
                'args' => [
                    'dpr' => [
                        'name' => 'dpr',
                        'type' => Type::boolean(),
                        'description' => 'Include dpr images?'
                    ],
                ],
                'type' => Type::string(),
            ],
            'srcsetWebp' => [
                'name' => 'srcsetWebp',
                'description' => 'Return a string of webp image URLs and their sizes',
                'args' => [
                    'dpr' => [
                        'name' => 'dpr',
                        'type' => Type::boolean(),
                        'description' => 'Include dpr images?'
                    ],
                ],
                'type' => Type::string(),
            ],
            'placeholderBox' => [
                'name' => 'placeholderBox',
                'description' => 'Return an SVG box as a placeholder image',
                'args' => [
                    'color' => [
                        'name' => 'color',
                        'type' => Type::string(),
                        'description' => 'The color for the placeholder box'
                    ],
                ],
                'type' => Type::string(),
            ],
        ];
        $optimizedImagesType = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new OptimizedImagesType([
            'name' => $typeName,
            'description' => 'This entity has all the OptimizedImages properties',
            'fields' => function () use ($optimizedImagesFields) {
                return $optimizedImagesFields;
            },
            ]));

        TypeLoader::registerType($typeName, function () use ($optimizedImagesType) {
            return $optimizedImagesType;
        });

        return [$optimizedImagesType];
    }

    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        /** @var OptimizedImages $context */
        return $context->handle.'_OptimizedImages';
    }
}
