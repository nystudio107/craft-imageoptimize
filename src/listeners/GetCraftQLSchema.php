<?php

namespace nystudio107\imageoptimize\listeners;

use Craft;

use markhuot\CraftQL\Events\GetFieldSchema;

use GraphQL\Type\Definition\Type;

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

        // Primary getter functions
        $fieldObject->addStringField('src');
        $fieldObject->addStringField('srcset');
        $fieldObject->addStringField('srcWebp');
        $fieldObject->addStringField('srcsetWebp');
        $fieldObject->addIntField('maxSrcsetWidth');
        $fieldObject->addStringField('placeholderImage');
        $fieldObject->addStringField('placeholderBox');
        $fieldObject->addStringField('placeholderSilhouette');

        // Object properties
        $fieldObject->addStringField('optimizedImageUrls')
            ->lists()
            ->type(Type::string());
        $fieldObject->addStringField('optimizedWebPImageUrls')
            ->lists()
            ->type(Type::string());
        $fieldObject->addIntField('variantSourceWidths')
            ->lists()
            ->type(Type::int());
        $fieldObject->addIntField('originalImageWidth');
        $fieldObject->addIntField('originalImageHeight');
        $fieldObject->addStringField('placeholder');
        $fieldObject->addStringField('placeholderSvg');
        $fieldObject->addStringField('colorPalette')
            ->lists()
            ->type(Type::string());
        $fieldObject->addIntField('placeholderWidth');
        $fieldObject->addIntField('placeholderHeight');

        // Add the field object to the schema
        $event->schema->addField($event->sender)
            ->type($fieldObject);
    }
}
