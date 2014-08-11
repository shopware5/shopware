;jQuery(function($) {
    'use strict';

    /**
     * Shopware Add Article Plugin
     *
     * @example Button Element (can be pretty much every element)
     *
     * HTML:
     *
     * <button data-add-article="true" data-addArticleUrl="{url controller='checkout' action='addArticle' sAdd=$sArticle.ordernumber}">
     *     Jetzt bestellen
     * </button>
     *
     * @example Form
     *
     * HTML:
     *
     * <form data-add-article="true" data-eventName="submit">
     *     <input type="hidden" name="sAdd" value="SW10165"> // Contains the ordernumber of the article
     *     <input type="hidden" name="sQuantity" value"10"> // Optional (Default: 1). Contains the amount of articles to be added (Can also be an select box)
     *
     *     <button>In den Warenkorb</button>
     * </form>
     *
     *
     * You can either add an article by giving a specific url to the property "addArticleUrl" (First example)
     * or you can add hidden input fields to the element with name "sAdd" and "sQuantity" (Second example).
     *
     * JS:
     *
     * $('*[data-add-article="true"]').addArticle();
     *
     */
    $.plugin('addArticle', {

        defaults: {
            /**
             * Event name that the plugin listens to.
             *
             * @type {String}
             */
            'eventName': 'click',

            /**
             * The ajax url that the request should be send to.
             *
             * Default: myShop.com/(Controller:)checkout/(Action:)addArticle
             *
             * @type {String}
             */
            'addArticleUrl': jQuery.controller['ajax_add_article']
        },

        /**
         * Default plugin initialisation function.
         * Registers an event listener on the change event.
         * When it's triggered, the parent form will be submitted.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            // Applies HTML data attributes to the current options
            me.applyDataAttributes();

            // Will be automatically removed when destroy() is called.
            me._on(me.$el, me.opts.eventName, $.proxy(me.sendSerializedForm, me));
        },

        /**
         * Serializes the plugin element {@link $el} and sends it to the given url.
         * When the ajax request was successful, the {@link initModalSlider} will be called.
         *
         * @public
         * @method sendSerializedForm
         * @param {jQuery.Event} event
         */
        sendSerializedForm: function (event) {
            event.preventDefault();

            var me = this,
                $el = me.$el,
                ajaxData = $el.serialize(),
                $modal;

            $.loadingIndicator.open({
                'closeOverlay': false
            });

            $.ajax({
                'data': ajaxData,
                'dataType': 'jsonp',
                'url': me.opts.addArticleUrl,
                'success': function (result) {
                    $.loadingIndicator.close(function() {
                        $modal = $.modal.open(result, {
                            width: 750,
                            sizing: 'content'
                        });

                        picturefill();

                        me.initModalSlider($modal);
                    });
                }
            });
        },

        /**
         * When the modal content contains a product slider, it will be initialized.
         *
         * @param {jQuery} $modal
         */
        initModalSlider: function ($modal) {
            var $slider = $('.js--modal').find('.product-slider');

            if (!$slider || !$slider.length) {
                return;
            }

            StateManager.registerListener([{
                'type': 'smartphone',
                'enter': function () {
                    $slider.productSlider({
                        'perPage': 1,
                        'perSlide': 1,
                        'touchControl': true
                    });
                }
            }, {
                'type': 'tablet',
                'enter': function () {
                    $slider.productSlider({
                        'perPage': 2,
                        'perSlide': 1,
                        'touchControl': true
                    });
                }
            }, {
                'type': 'tabletLandscape',
                'enter': function () {
                    $slider.productSlider({
                        'perPage': 3,
                        'perSlide': 1,
                        'touchControl': true
                    });
                }
            }, {
                'type': 'desktop',
                'enter': function () {
                    $slider.productSlider({
                        'perPage': 3,
                        'perSlide': 1,
                        'touchControl': true
                    });
                }
            }, {
                'type': '*',
                'enter': function () {
                    setTimeout(function () {
                        var $slider = $modal.find('.product-slider');

                        if(!$slider || !$slider.length) {
                            return;
                        }

                        $slider.data('plugin_productSlider').setSizes();
                    }, 10);
                },
                'exit': function () {
                    var $slider = $modal.find('.product-slider');

                    if(!$slider || !$slider.length) {
                        return;
                    }

                    $slider.data('plugin_productSlider').destroy();
                }
            }]);
        }
    });
});