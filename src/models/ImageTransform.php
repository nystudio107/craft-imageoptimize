<?php
/**
 * Image Optimize plugin for Craft CMS
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\imageoptimize\models;

use craft\models\ImageTransform as CraftImageTransform;
use craft\models\ImageTransformIndex as CraftImageTransformIndex;

/**
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class ImageTransform extends CraftImageTransform
{
    // Public Properties
    // =========================================================================

    public ?string $watermark = null;
}
