/**
 * Shopware Overlay Module.
 *
 * Displays/Hides a fullscreen overlay with a certain color and opacity.
 *
 * @example
 *
 * Open th overlay:
 *
 * $.overlay.open({
     *     color: '#FF0000' //red
     *     opacity: 0.5
     * });
 *
 * Closing the overlay:
 *
 * $.overlay.close();
 *
 * @type {Object}
 */
;(function ($) {
    'use strict';

    var $overlay = $('<div>', {
            'class': 'js--overlay'
        }).appendTo('body'),

        isOpen = false,

        openClass = 'is--open',

        closableClass = 'is--closable',

        events = ['click', 'touchstart', 'MSPointerDown'].join('.overlay') + '.overlay',

        /**
         *
         * {
         *     // Whether or not the overlay should be closable by click.
         *     closeOnClick: {Boolean},
         *
         *     // Function that gets called every time the user clicks on the overlay.
         *     onClick: {Function},
         *
         *     // Function that gets called only when the overlay is closable and the user clicks on it.
         *     onClose: {Function}
         * }
         *
         * @param options
         */
        openOverlay = function (options) {
            if (isOpen) {
                updateOverlay(options);
                return;
            }
            isOpen = true;

            $overlay.addClass(openClass);

            if (options && options.closeOnClick !== false) {
                $overlay.addClass(closableClass);
            }

            $overlay.on(events, $.proxy(onOverlayClick, this, options));
        },

        closeOverlay = function () {
            if (!isOpen) {
                return;
            }
            isOpen = false;

            $overlay.removeClass(openClass + ' ' + closableClass);

            $overlay.off(events);
        },

        onOverlayClick = function (options) {
            if (options) {
                if (typeof options.onClick === 'function') {
                    options.onClick.call($overlay);
                }

                if (options.closeOnClick === false) {
                    return;
                }

                if (typeof options.onClose === 'function' && options.onClose.call($overlay) === false) {
                    return;
                }
            }

            closeOverlay();
        },

        updateOverlay = function (options) {
            $overlay.toggleClass(closableClass, options.closeOnClick !== false);

            $overlay.off(events);

            $overlay.on(events, $.proxy(onOverlayClick, this, options));
        };

    $overlay.on('mousewheel DOMMouseScroll', function (event) {
        event.preventDefault();
    });

    $.overlay = {
        open: openOverlay,

        close: closeOverlay,

        isOpen: function () {
            return isOpen;
        },

        getElement: function () {
            return $overlay;
        }
    };
})(jQuery);
