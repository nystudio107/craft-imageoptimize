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
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        /** @var OptimizedImages $context */
        $typeName = self::getName($context);

        $optimizedImagesFields = [
            'optimizedImageUrls' => [
                'name' => 'optimizedImageUrls',
                'description' => 'An array of optimized image variant URLs',
                'type' => Type::listOf(Type::string())
            ],
            'optimizedWebPImageUrls' => [
                'name' => 'optimizedWebPImageUrls',
                'description' => 'An array of optimized .webp image variant URLs',
                'type' => Type::listOf(Type::string())
            ],
            'variantSourceWidths' => [
                'name' => 'variantSourceWidths',
                'description' => 'An array of the widths of the optimized image variants',
                'type' => Type::listOf(Type::int())
            ],
            'variantHeights' => [
                'name' => 'variantHeights',
                'description' => 'An array of the heights of the optimized image variants',
                'type' => Type::listOf(Type::int())
            ],
            'focalPoint' => [
                'name' => 'focalPoint',
                'description' => 'An array of the x,y image focal point coords, ranging from 0.0 to 1.0',
                'type' => Type::listOf(Type::float())
            ],
            'originalImageWidth' => [
                'name' => 'originalImageWidth',
                'description' => 'The width of the original source image',
                'type' => Type::int()
            ],
            'originalImageHeight' => [
                'name' => 'originalImageHeight',
                'description' => 'The height of the original source image',
                'type' => Type::int()
            ],
            'placeholder' => [
                'name' => 'placeholder',
                'description' => 'The base64 encoded placeholder LQIP image',
                'type' => Type::string()
            ],
            'placeholderSvg' => [
                'name' => 'placeholderSvg',
                'description' => 'The base64 encoded placeholder LQIP SVG image',
                'type' => Type::string()
            ],
            'colorPalette' => [
                'name' => 'colorPalette',
                'description' => 'An array the 5 most dominant colors in the image',
                'type' => Type::listOf(Type::string())
            ],
            'lightness' => [
                'name' => 'lightness',
                'description' => 'The overall lightness of the image, from 0..100',
                'type' => Type::int()
            ],
            'placeholderWidth' => [
                'name' => 'placeholderWidth',
                'description' => 'The width of the placeholder image',
                'type' => Type::int()
            ],
            'placeholderHeight' => [
                'name' => 'placeholderHeight',
                'description' => 'The height of the placeholder image',
                'type' => Type::int()
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
