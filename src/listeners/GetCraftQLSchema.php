<?php

namespace nystudio107\imageoptimize\listeners;

use markhuot\CraftQL\Events\GetFieldSchema;

class GetCraftQLSchema
{
    // Constants
    // =========================================================================

    const EVENT_GET_FIELD_SCHEMA = 'craftQlGetFieldSchema';

    // Public methods
    // =========================================================================

    /**
     * Handle the request for the schema
     *
     * @param GetFieldSchema $event
     *
     * @return void
     */
    public function handle($event)
    {
        $event->handled = true;
        $field = $event->sender;

        $fieldObject = $event->schema->createObjectType('OptimizedImagesData');

        $srcObject = $event->schema->createObjectType('OptimizedImageSrc');
        $srcObject->addStringField('url');
        $srcObject->addIntField('width');

        // Primary getter functions
        $fieldObject->addStringField('src')
            ->arguments(function (\markhuot\CraftQL\Builders\Field $field) {
                $field->addIntArgument('width');
            })
            ->resolve(function ($root, $args) {
                return $root->src(@$args['width'] ?: 0);
            });
        $fieldObject->addField('srcUrls')
            ->lists()
            ->type($srcObject)
            ->resolve(function ($root, $args) {
                $result = [];
                foreach ($root->optimizedImageUrls as $width => $url) {
                    $result[] = ['width' => $width, 'url' => $url];
                }
                return $result;
            });
        $fieldObject->addStringField('srcset');
        $fieldObject->addStringField('srcWebp');
        $fieldObject->addStringField('srcsetWebp');
        $fieldObject->addIntField('maxSrcsetWidth');
        $fieldObject->addStringField('placeholderImage');
        $fieldObject->addStringField('placeholderBox');
        $fieldObject->addStringField('placeholderSilhouette');

        // Object properties
        $fieldObject->addStringField('optimizedImageUrls')->lists();
        $fieldObject->addStringField('optimizedWebPImageUrls')->lists();
        $fieldObject->addIntField('variantSourceWidths')->lists();
        $fieldObject->addIntField('variantHeights')->lists();
        $fieldObject->addIntField('originalImageWidth');
        $fieldObject->addIntField('originalImageHeight');
        $fieldObject->addStringField('placeholder');
        $fieldObject->addStringField('placeholderSvg');
        $fieldObject->addStringField('colorPalette')->lists();
        $fieldObject->addIntField('placeholderWidth');
        $fieldObject->addIntField('placeholderHeight');

        // Add the field object to the schema
        $event->schema->addField($event->sender)
            ->type($fieldObject);
    }
}
