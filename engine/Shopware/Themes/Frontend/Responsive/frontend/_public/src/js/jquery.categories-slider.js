;(function ($, Modernizr) {
    'use strict';

    /**
     * Categories Slider plugin
     *
     * The plugin provides an category slider inside the off canvas menu. The categories and sub categories
     * could be fetched by ajax calls and uses a CSS3 `transitions` to slide in or out. The main sidebar will not
     * be overwritten. The categories slider plugin uses two overlays to interact.
     *
     * @example usage
     * ```
     *    <div data-categories-slider="true"
     *      data-mainCategoryId="{$Shop->get('parentID')}"
     *      data-categoryId="{$sCategoryContent.id}"
     *      data-fetchUrl="{url module=widgets controller=listing action=getCategory categoryId={$sCategoryContent.id}}"></div>
     *
     *    $('*[data-categories-slider="true"]').categoriesSlider();
     * ```
     */
    $.plugin('categoriesSlider', {

        defaults: {

            /**
             * @property enabled
             * @type {Boolean}
             */
            'enabled': true,

            /**
             * @property eventName
             * @type {String}
             */
            'eventName': 'click',

            /**
             * @property categorySelector
             * @type {String}
             */
            'categorySelector': '.categories--children',

            /**
             * @property sidebarCategorySelector
             * @type {String}
             */
            'sidebarCategorySelector': '.sidebar--navigation',

            /**
             * @property backwardsSelector
             * @type {String}
             */
            'backwardsSelector': '.link--go-back',

            /**
             * @property forwardSelector
             * @type {String}
             */
            'forwardsSelector': '.link--go-forward',

            /**
             * @property mainMenuSelector
             * @type {String}
             */
            'mainMenuSelector': '.link--go-main',

            /**
             * @property sidebarWrapperSelector
             * @type {String}
             */
            'sidebarWrapperSelector': '.sidebar--categories-wrapper',

            /**
             * @property mainCategoryId
             * @type {Number}
             */
            'mainCategoryId': null,

            /**
             * @property categoryId
             * @type {Number}
             */
            'categoryId': null,

            /**
             * @property fetchUrl
             * @type {String}
             */
            'fetchUrl': '',

            /**
             * @property overlaySelector
             * @type {String}
             */
            'overlaySelector': '.offcanvas--overlay',

            /**
             * @property overlayOffCls
             * @type {String}
             */
            'overlayOffCls': 'offcanvas--overlay-off',

            /**
             * @property sidebarMainSelector
             * @type {String}
             */
            'sidebarMainSelector': '.sidebar-main',

            /**
             * @property loadingClass
             * @type {String}
             */
            'loadingClass': 'sidebar--ajax-loader'
        },

        /**
         * Default plugin initialisation function.
         * Handle all logic and events for the category slider
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;
            
            // Overwrite plugin configuration with user configuration
            me.applyDataAttributes();

            // return, if no category available
            if (!me.opts.enabled || !me.opts.categoryId || !me.opts.fetchUrl || !me.opts.mainCategoryId) {
                return;
            }

            me.fadeEffect = (Modernizr.csstransitions) ? 'transition' : 'animate';

            /**
             * @private
             * @property _$sidebar
             * @type {jQuery}
             */
            me._$sidebar = $(me.opts.sidebarMainSelector);
            me._$loadingIcon = $('<div>', {
                'class': me.opts.loadingClass
            });
            me._$sidebarWrapper = $(me.opts.sidebarWrapperSelector);

            // remove sub level unordered lists
            $('.sidebar--navigation ul').not('.navigation--level-high').css('display', 'none');

            me.addEventListener();

            // fetch menu by category id if actual category is not the main category
            if(me.opts.mainCategoryId == me.opts.categoryId) {
                return;
            }

            $.get(me.opts.fetchUrl, function (template) {

                me._$sidebarWrapper.css('display', 'none');

                me._$sidebar.append(template);

                // add background class
                $(me.opts.overlaySelector).addClass('background');
            });
        },

        /**
         * adding the event listeners
         *
         * @public
         * @method addEventListener
         */
        addEventListener: function () {
            var me = this,
                opts = me.opts,
                $sidebar = me._$sidebar,
                eventName = opts.eventName;

            $sidebar.on(me.getEventName(eventName), opts.backwardsSelector, $.proxy(me.onClickBackButton, me));

            $sidebar.on(me.getEventName(eventName), opts.forwardsSelector, $.proxy(me.onClickForwardButton, me));

            $sidebar.on(me.getEventName(eventName), opts.mainMenuSelector, $.proxy(me.onClickMainMenuButton, me));
        },

        /**
         * onBack method for loading old pages
         *
         * @public
         * @method onClickBackButton
         * @param {Object} event
         */
        onClickBackButton: function (event) {
            event.preventDefault();

            var me = this,
                $target = $(event.target),
                url = $target.attr('href'),
                parentId = $target.attr('data-parentId');

            // decide if there is a parent group or main sidebar
            if (!url || parentId === me.opts.mainCategoryId) {
                me.slideToMainMenu();
                return;
            }

            me.loadTemplate(url, me.slideOut, $target);
        },

        /**
         * forward method for fetching new pages
         *
         * @public
         * @method onClickForwardButton
         * @param {Object} event
         */
        onClickForwardButton: function (event) {
            event.preventDefault();

            var me = this,
                $target = $(event.target),
                url = $target.attr('data-fetchUrl');

            me.loadTemplate(url, me.slideIn, $target);
        },

        /**
         * main menu method for closing all overlays
         *
         * @public
         * @method onClickMainMenuButton
         * @param {Object} event
         */
        onClickMainMenuButton: function (event) {
            event.preventDefault();

            this.slideToMainMenu();
        },

        /**
         * loads a template via ajax call
         *
         * @public
         * @method loadTemplate
         * @param {String} url
         * @param {Function} callback
         * @param {jQuery.Event} $loadingTarget
         */
        loadTemplate: function (url, callback, $loadingTarget) {
            var me = this;

            if (!$loadingTarget) {
                $.get(url, callback.bind(me));
                return;
            }

            $loadingTarget.find('.is--icon-right').fadeOut('fast');

            $loadingTarget.append(me._$loadingIcon);

            me._$loadingIcon.fadeIn();

            $.get(url, function (template) {
                me._$loadingIcon.hide();
                callback.call(me, template);
            });
        },

        /**
         * sliding out the first level overlay
         * and removes the slided overlay
         *
         * @public
         * @method slideOut
         * @param {String} template
         */
        slideOut: function (template) {
            var me = this,
                $overlay = $(me.opts.overlaySelector + '.background');

            /** fetch the template in the background, but on the target position */
            me._$sidebar.append(template);

            // change class to
            $(me.opts.overlaySelector).not('.background').addClass('background');

            $overlay.removeClass('background');

            $overlay[me.fadeEffect]({ left: 280 }, 250, function() {
                $overlay.remove();
            });
        },

        /**
         * sliding in the invisible container
         * and removes the background overlay
         *
         * @public
         * @method slideIn
         * @param {String} template
         */
        slideIn: function (template) {
            var me = this,
                $overlay;

            me._$sidebar.append(template);

            $overlay = $(me.opts.overlaySelector).not('.background').css('left', 280);

            $overlay[me.fadeEffect]({ left: 0 }, 250, function() {
                // remove background layer
                $(me.opts.overlaySelector + '.background').remove();

                $overlay.addClass('background');

                // hide main menu
                me._$sidebarWrapper.css('display', 'none');
            });
        },

        /**
         * sliding all overlays out
         * and removes all overlays
         *
         * @public
         * @method slideToMainMenu
         */
        slideToMainMenu: function () {
            var me = this,
                $overlay = $(me.opts.overlaySelector);

            // make the main menu visible
            me._$sidebarWrapper.css('display', 'block');

            // fade in arrow icons
            me._$sidebarWrapper.find('.is--icon-right').fadeIn('slow');

            $overlay[me.fadeEffect]({ left: 280 }, 250, function() {
                $overlay.remove();
            });
        },

        /**
         * destroys the categories slider plugin
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                $sidebar = me._$sidebar;

            $sidebar.off(me.getEventName(me.opts.eventName));

            me._destroy();

            // make category children visible
            $('.sidebar--navigation ul').not('.navigation--level-high').css('display', 'block');

            // force sidebar to be shown
            me._$sidebarWrapper.css('display', 'block');

            // clear overlay
            $('.offcanvas--overlay').remove();
        }
    });
}(jQuery, Modernizr));