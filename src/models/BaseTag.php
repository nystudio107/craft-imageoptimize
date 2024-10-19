<?php
/**
 * Image Optimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) nystudio107
 */

namespace nystudio107\imageoptimize\models;

use craft\base\Model;
use craft\helpers\Template;
use Twig\Markup;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     5.0.0-beta.1
 */
abstract class BaseTag extends Model implements TagInterface
{
    use TagTrait;

    /**
     * Attributes that are allowed to be an empty string
     */
    protected const ALLOWED_EMPTY_ATTRS = ['alt'];

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Render the tag
     *
     * @return Markup
     */
    public function render(): Markup
    {
        return Template::raw('');
    }

    /**
     * Filter out attributes with empty values in them, so they don't get rendered
     *
     * @param array $attrs
     * @return array
     */
    public function filterEmptyAttributes(array $attrs): array
    {
        // Keep certain attributes even if they are empty
        return array_filter($attrs, static fn($value, $key) => in_array($key, self::ALLOWED_EMPTY_ATTRS, true) || !empty($value), ARRAY_FILTER_USE_BOTH);
    }
}
