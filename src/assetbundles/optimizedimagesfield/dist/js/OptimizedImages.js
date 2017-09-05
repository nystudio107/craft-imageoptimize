/**
 * Image Optimize plugin for Craft CMS
 *
 * OptimizedImages Field JS
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2017 nystudio107
 * @link      https://nystudio107.com
 * @package   ImageOptimize
 * @since     1.2.0ImageOptimizeOptimizedImages
 */

 ;(function ( $, window, document, undefined ) {

    var pluginName = "ImageOptimizeOptimizedImages",
        defaults = {
        };

    // Plugin constructor
    function Plugin( element, options ) {
        this.element = element;

        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {

        init: function(id) {
            var _this = this;

            $(function () {

/* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */

            });
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin( this, options ));
            }
        });
    };

})( jQuery, window, document );

Craft.OptimizedImagesInput = Garnish.Base.extend(
    {
        id: null,
        inputNamePrefix: null,
        inputIdPrefix: null,

        $container: null,
        $blockContainer: null,
        $addBlockBtnContainer: null,
        $addBlockBtnGroup: null,
        $addBlockBtnGroupBtns: null,

        blockSort: null,
        blockSelect: null,

        init: function(id, inputNamePrefix) {
            this.id = id;
            this.inputNamePrefix = inputNamePrefix;
            this.inputIdPrefix = Craft.formatInputId(this.inputNamePrefix);

            this.$container = $('#' + this.id);
            this.$blockContainer = this.$container.children('.variant-blocks');
            this.$addBlockBtnContainer = this.$container.children('.buttons');
            this.$addBlockBtnGroup = this.$addBlockBtnContainer.children('.btngroup');
            this.$addBlockBtnGroupBtns = this.$addBlockBtnGroup.children('.btn');
            this.$addBlockMenuBtn = this.$addBlockBtnContainer.children('.menubtn');

            var $blocks = this.$blockContainer.children();

            this.blockSort = new Garnish.DragSort($blocks, {
                handle: '> .actions > .move',
                axis: 'y',
                filter: $.proxy(function() {
                    // Only return all the selected items if the target item is selected
                    if (this.blockSort.$targetItem.hasClass('sel')) {
                        return this.blockSelect.getSelectedItems();
                    }
                    else {
                        return this.blockSort.$targetItem;
                    }
                }, this),
                collapseDraggees: true,
                magnetStrength: 4,
                helperLagBase: 1.5,
                helperOpacity: 0.9,
                onSortChange: $.proxy(function() {
                    this.blockSelect.resetItemOrder();

                }, this)
            });

            this.addListener(this.$addBlockBtnGroupBtns, 'click', function(ev) {
                var type = $(ev.target).data('type');
                this.addBlock(type);
            });

            new Garnish.MenuBtn(this.$addBlockMenuBtn,
                {
                    onOptionSelect: $.proxy(function(option) {
                        var type = $(option).data('type');
                        this.addBlock(type);
                    }, this)
                });
        },


        getHiddenBlockCss: function($block) {
            return {
                opacity: 0,
                marginBottom: -($block.outerHeight())
            };
        },

    });