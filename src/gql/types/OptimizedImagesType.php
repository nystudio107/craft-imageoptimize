<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace nystudio107\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.6.2
 */
class OptimizedImagesType extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;

        return $source[$fieldName];
    }
}
