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

            // Create our action button menus
            var _this = this;
            this.$blockContainer.find('> > .actions > .settings').each(function (index, value) {
                var $value = $(value);
                var menuBtn;
                if ($value.data('menubtn')) {
                    menuBtn = $value.data('menubtn');
                } else {
                    menuBtn = new Garnish.MenuBtn(value);
                }
                menuBtn.menu.settings.onOptionSelect = $.proxy(function(option) {
                    this.onMenuOptionSelect(option, menuBtn);
                }, _this);
            });

            var $blocks = this.$blockContainer.children();

            this.blockSort = new Garnish.DragSort($blocks, {
                handle: '> .actions > .move',
                axis: 'y',
                collapseDraggees: true,
                magnetStrength: 4,
                helperLagBase: 1.5,
                helperOpacity: 0.9,
                onSortChange: $.proxy(function() {
                    this.resetVariantBlockOrder();
                }, this)
            });

            this.addListener(this.$addBlockBtnGroupBtns, 'click', function(ev) {
                var type = $(ev.target).data('type');
                this.addVariantBlock(type);
            });

        },

        onMenuOptionSelect: function(option, menuBtn) {
            var $option = $(option);
            var container = menuBtn.$btn.closest('.matrixblock');

            switch ($option.data('action')) {
                case 'add': {
                    this.addVariantBlock();
                    break;
                }
                case 'delete': {
                    this.deleteVariantBlock(container);
                    break;
                }
            }
        },

        getHiddenBlockCss: function($block) {
            return {
                opacity: 0,
                marginBottom: -($block.outerHeight())
            };
        },

        // Re-index all of the variant blocks
        reIndexVariants: function() {
            var $blocks = this.$blockContainer.children();
            $blocks.each(function (index, value) {
                var variantIndex = index;
                var $value = $(value);
                var elements = $value.find('div .field, label, input');

                // Re-index all of the element attributes
                $(elements).each(function (index, value) {
                    // id attributes
                    var str = $(value).attr('id');
                    if (str) {
                        str = str.replace(/\-([0-9]+)\-/g, "-" + variantIndex +"-");
                        $(value).attr('id', str);
                    }
                    // for attributes
                    str = $(value).attr('for');
                    if (str) {
                        str = str.replace(/\-([0-9]+)\-/g, "-" + variantIndex +"-");
                        $(value).attr('for', str);
                    }
                    // Name attributes
                    str = $(value).attr('name');
                    if (str) {
                        str = str.replace(/\[([0-9]+)\]/g, "[" + variantIndex +"]");
                        $(value).attr('name', str);
                    }
                });
            });
        },

        addVariantBlock: function(container) {
            this.reIndexVariants();
        },

        deleteVariantBlock: function(container) {
            container.velocity(this.getHiddenBlockCss(container), 'fast', $.proxy(function() {
                container.remove();
            }, this));
            this.reIndexVariants();
        },

        resetVariantBlockOrder: function() {
            this.reIndexVariants();
        }

    });