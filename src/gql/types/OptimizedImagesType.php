<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace nystudio107\imageoptimize\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use nystudio107\imageoptimize\models\OptimizedImage;

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
        /** @var OptimizedImage $source */
        $fieldName = $resolveInfo->fieldName;

        switch ($fieldName) {
            // Special-case the `src` field with arguments
            case 'src':
                $width = $arguments['width'] ?? 0;
                return $source->src($width);

            // Special-case the `srcWebp` field with arguments
            case 'srcWebp':
                $width = $arguments['width'] ?? 0;
                return $source->srcWebp($width);

            // Special-case the `srcset` field with arguments
            case 'srcset':
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcset($dpr);

            // Special-case the `srcsetMinWidth` field with arguments
            case 'srcsetMinWidth':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcsetMinWidth($width, $dpr);

            // Special-case the `srcsetMaxWidth` field with arguments
            case 'srcsetMaxWidth':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcsetMaxWidth($width, $dpr);

            // Special-case the `srcsetWebp` field with arguments
            case 'srcsetWebp':
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcsetWebp($dpr);

            // Special-case the `srcsetMinWidthWebp` field with arguments
            case 'srcsetMinWidthWebp':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcsetMinWidthWebp($width, $dpr);

            // Special-case the `srcsetMaxWidthWebp` field with arguments
            case 'srcsetMaxWidthWebp':
                $width = $arguments['width'] ?? 0;
                $dpr = $arguments['dpr'] ?? false;
                return $source->srcsetMaxWidthWebp($width, $dpr);

            // Special-case the `maxSrcsetWidth` field
            case 'maxSrcsetWidth':
                return $source->maxSrcsetWidth();

            // Special-case the `placeholderImage` field
            case 'placeholderImage':
                return $source->placeholderImage();

            // Special-case the `placeholderBox` field
            case 'placeholderBox':
                $color = $arguments['color'] ?? null;
                return $source->placeholderBox($color);

            // Special-case the `placeholderSilhouette` field
            case 'placeholderSilhouette':
                return $source->placeholderSilhouette();

            // Special-case the `srcUrls` field
            case 'srcUrls':
                $result = [];
                foreach ($source->optimizedImageUrls as $width => $url) {
                    $result[] = ['width' => $width, 'url' => $url];
                }
                return $result;

            // Special-case the `colorPaletteRgb` field
            case 'colorPaletteRgb':
                return $source->colorPaletteRgb();

            // Default to just returning the field value
            default:
                return $source[$fieldName];
        }
    }
}
