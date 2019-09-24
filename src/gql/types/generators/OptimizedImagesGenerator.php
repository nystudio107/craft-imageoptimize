<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace nystudio107\imageoptimize\gql\types\generators;

use nystudio107\imageoptimize\gql\resolvers\OptimizedImagesResolver;
use nystudio107\imageoptimize\fields\OptimizedImages;

use craft\fields\Table as TableField;
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

        $optimizedImagesProps = [
            'name' => Type::string(),
            'street' => Type::string(),
            'street2' => Type::string(),
            'postalCode' => Type::string(),
            'city' => Type::string(),
            'state' => Type::string(),
            'country' => Type::string(),
            'latitude' => Type::float(),
            'longitude' => Type::float(),
        ];
        $optimizedImagesProperty = GqlEntityRegistry::createEntity($typeName, new OptimizedImagesResolver([
            'name' => $typeName,
            'description' => 'This entity has all the OptimizedImages properties',
            'fields' => function () use ($optimizedImagesProps) {
                return $optimizedImagesProps;
            },
        ]));

        TypeLoader::registerType($typeName, function () use ($optimizedImagesProperty) {
            return $optimizedImagesProperty;
        });

        return [$optimizedImagesProperty];
    }

    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        /** @var TableField $context */
        return $context->handle.'_OptimizedImages';
    }
}
