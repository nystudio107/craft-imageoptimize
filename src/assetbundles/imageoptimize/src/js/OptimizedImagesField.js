/**
 * Image Optimize plugin for Craft CMS
 *
 * OptimizedImages Field JS
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2017 nystudio107
 * @link      https://nystudio107.com
 * @package   ImageOptimize
 * @since     1.2.0
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
                this.addVariantBlock(null);
            });

            this.addAspectRatioHandlers();
            this.reIndexVariants();
        },

        onMenuOptionSelect: function(option, menuBtn) {
            var $option = $(option);
            var container = menuBtn.$btn.closest('.matrixblock');

            switch ($option.data('action')) {
                case 'add': {
                    this.addVariantBlock(container);
                    break;
                }
                case 'delete': {
                    if (!$option.hasClass('disabled')) {
                        this.deleteVariantBlock(container);
                    }
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
            this.$blockContainer = this.$container.children('.variant-blocks');
            var $blocks = this.$blockContainer.children();
            $blocks.each(function (index, value) {
                var variantIndex = index;
                var $value = $(value);
                var elements = $value.find('div .field, label, input, select');

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
            var disabledDeleteItem = false;
            // If we only have one block, don't allow it to be deleted
            if ($blocks.length == 1) {
                disabledDeleteItem = true;
            }
            $blocks.find('> .actions > .settings').each(function (index, value) {
                var $value = $(value);
                var menuBtn;
                if ($value.data('menubtn')) {
                    menuBtn = $value.data('menubtn');
                    $menuItem = $(menuBtn.menu.$menuList[1]);
                    if (disabledDeleteItem) {
                        $menuItem.find("> li > a").addClass('disabled').disable();
                    } else {
                        $menuItem.find("> li > a").removeClass('disabled').enable();
                    }
                }
            });

        },

        addAspectRatioHandlers: function () {
            this.addListener($('.lightswitch'), 'click', function(ev) {
                var $target = $(ev.target);
                var $block = $target.closest('.matrixblock');
                $block.find('.io-aspect-ratio-wrapper').slideToggle();
            });
            this.addListener($('.io-select-ar-box'), 'click', function(ev) {
                var $target = $(ev.target);
                var x = $(ev.target).data('x'),
                    y = $(ev.target).data('y'),
                    custom = $(ev.target).data('custom'),
                    field, $block;
                // Select the appropriate aspect ratio
                $block = $target.closest('.matrixblock');
                $block.find('.io-select-ar-box').each(function (index, value) {
                    $(value).removeClass('io-selected-ar-box');
                });
                $target.addClass('io-selected-ar-box');

                // Handle setting the actual field values
                if (custom) {
                    $block.find('.io-custom-ar-wrapper').slideDown();
                } else {
                    $block.find('.io-custom-ar-wrapper').slideUp();
                    field = $block.find('input')[2];
                    $(field).val(x);
                    field = $block.find('input')[3];
                    $(field).val(y);
                }
            });
        },

        addVariantBlock: function(container) {
            var _this = this;
            $block = $(this.$blockContainer.children()[0]).clone();
            // Reset to default values
            $block.find('.io-select-ar-box').each(function (index, value) {
                if (index === 0) {
                    $(value).addClass('io-selected-ar-box');
                } else {
                    $(value).removeClass('io-selected-ar-box');
                }
            });
            $block.find('.io-custom-ar-wrapper').hide();
            field = $block.find('input')[0];
            $(field).val(1200);
            field = $block.find('input')[1];
            $(field).val(1);
            field = $block.find('input')[2];
            $(field).val(16);
            field = $block.find('input')[3];
            $(field).val(9);
            field = $block.find('select')[0];
            $(field).val(82);
            field = $block.find('select')[1];
            $(field).val('jpg');
            $block.css(this.getHiddenBlockCss($block)).velocity({
                opacity: 1,
                'margin-bottom': 10
            }, 'fast', $.proxy(function() {

                // Insert the block in the right place
                if (container) {
                    $block.insertBefore(container);
                } else {
                    $block.appendTo(this.$blockContainer);
                }
                // Update the Garnish UI controls
                this.blockSort.addItems($block);
                this.addAspectRatioHandlers();
                $block.find('.settings').each(function (index, value) {
                    var $value = $(value),
                        menuBtn,
                        menu;

                    menu = _this.$container.find('.io-menu-clone > .menu').clone();
                    $(menu).insertAfter($value);
                    menuBtn = new Garnish.MenuBtn(value);

                    menuBtn.menu.settings.onOptionSelect = $.proxy(function(option) {
                        _this.onMenuOptionSelect(option, menuBtn);
                    }, this);
                });
                this.reIndexVariants();
            }, this));
        },

        deleteVariantBlock: function(container) {
            var _this = this;
            container.velocity(this.getHiddenBlockCss(container), 'fast', $.proxy(function() {
                container.remove();
                _this.reIndexVariants();
            }, this));
        },

        resetVariantBlockOrder: function() {
            this.reIndexVariants();
        }

    });