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

use Twig\Markup;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     5.0.0-beta.1
 */
interface TagInterface
{
    /**
     * Render the tag
     *
     * @return Markup
     */
    public function render(): Markup;
}
