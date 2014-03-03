;(function($, window, document, undefined) {
    "use strict";

    /**
     * Global state manager
     *
     * The state manager helps to master different behaviors for different screen sizes. It provides you with the
     * ability to register different types, which has a enter size and exit size (either in EM or pixels values),
     * so you can mark a range where callback methods are called.
     *
     * The manager provides you multiple helper methods which helps you to master responsive design. Some of the
     * functions are created on-the-fly. If you register a new type, a new getter function will be created. Beside that
     * the manager uses the `evtParent` to fire custom events which will can be used to terminate the entering and
     * exiting of the type breakpoints.
     *
     * @example Register new types
     * ```
     *     StateManager.init([{
     *         type: 'smartphone',
     *         enter: '0em',
     *         exit: '47.5em'
     *      }, {
     *         type: 'tablet',
     *         enter: '47.5em',
     *         exit: '64em'
     *      }]);
     * ```
     *
     * @example Register breakpoint listeners
     * ```
     *     StateManager.registerListener([{
     *        type: 'smartphone',
     *        enter: function() { console.log('onEnter'); },
     *        exit: function() { console.log('onExit'); }
     *     }]);
     * ```
     *
     * @example Wildcard support
     * ```
     *     StateManager.registerListener([{
     *         type: '*',
     *         enter: function() { console.log('onGlobalEnter'); },
     *         exit: function() { console.log('onGlobalExit'); }
     *     }]);
     * ```
     */
    window.StateManager = (function() {
        var breakPoints,

            // Event observer
            evtParent = $('body'),

            // Collection for all registered listeners
            listeners = [],

            // Collection that corresponds to @{link listeners} and holds the current on / off state.
            listenersInit = [],

            // Resize timer speed
            tmrFastSpd = 100,
            tmrSlowSpd = 500,

            // Browser specific font size, used for converting EM based values to it's corresponding pixel value.
            defaultFontSize = Number(getComputedStyle(document.body, null).fontSize.replace(/[^\d]/g, '')),
            resizeWidth = 0,

            // Caches the current and previous state.
            prev = '',
            curr = '',

            // `matchMedia` small polyfill
            matchMedia = window.matchMedia || window.msMatchMedia,
            ret;

        /**
         * Returns the window window, supporting the W3C suggested implementation
         * as well as the implementation of the Internet Explorer in standard and quirks mode.
         *
         * @returns {Number} Window width in pixels.
         */
        var getWindowWidth = function() {
            var w = 0;

            // IE condition due to the weird quirks mode
            if(typeof(window.innerWidth) !== 'number') {

                // Handle the IE implementation *sigh*
                if(document.documentElement.clientWidth !== 0) {
                    // Strict mode
                    w = document.documentElement.clientWidth;
                } else {
                    // Quirks mode
                    w = document.body.clientWidth;
                }
            } else {
                w = window.innerWidth;
            }

            return w;
        };

        /**
         * Self-calling method that checks the browser width and delegate
         * if it detects a change on the width.
         *
         * @returns {Void}
         */
        var checkResize = function() {
            var width = getWindowWidth(),
                resizeTmrSpd;

            if(width !== resizeWidth) {
                resizeTmrSpd = tmrFastSpd;

                checkBreakpoints(width);
            } else {
                resizeTmrSpd = tmrSlowSpd;
            }

            resizeWidth = width;
            window.setTimeout(checkResize, resizeTmrSpd);
        };

        /**
         * Checks for a corresponding breakpoint against the {@link listeners} collection.
         *
         * @param {Number} width - Window width in pixels.
         * @returns {Void}
         */
        var checkBreakpoints = function(width) {
            var foundBreakpoint = false,
                i = 0,
                len = breakPoints.length;

            for(; i < len; i++) {
                var activeBreakpoint = breakPoints[i];

                if(width >= convertEmToPx(activeBreakpoint.enter) && width <= convertEmToPx(activeBreakpoint.exit)) {
                    foundBreakpoint = true;
                    break;
                }
            }

            if(foundBreakpoint && curr !== breakPoints[i].type) {
                var evtName;

                prev = curr;
                curr = breakPoints[i].type;

                // Fire event on the {@link evtParent}
                if(prev && prev.length) {
                    evtName = (prev === '*' ? 'Wildcard' : capitaliseFirstLetter(prev));
                    evtParent.trigger('exit' + evtName);
                }
                evtName = (curr === '*' ? 'Wildcard' : capitaliseFirstLetter(curr));
                evtParent.trigger('enter' + evtName);

                cycleThroughBreakpointListeners();
            } else if (!foundBreakpoint && curr !== '') {
                curr = '';

                cycleThroughBreakpointListeners();
            }
        };

        /**
         * Converts EM values to it's pixel counterparts based on the {@link defaultFontSize}
         * of the browser.
         *
         * @param {String} val - EM value which should be converted.
         * @returns {String|Number} Either the incoming value, if it's not a EM value or the converted value.
         */
        var convertEmToPx = function(val) {
            if(val.substr(val.length - 2, 2) !== 'em') {
                return val;
            }

            return parseFloat(val.substr(0, val.length -2)) * defaultFontSize;
        };

        /**
         * Capitialize the first letter of the incoming string.
         *
         * @param {String} str String which should be converted.
         * @returns {String} Converted string.
         */
        var capitaliseFirstLetter = function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        };

        /**
         * Cycles through all registered breakpoints listeners in the {@link listeners} array
         * and determines what should be fired.
         *
         * The method supports the usage of wildcard characters.
         *
         * @returns {Void}
         */
        var cycleThroughBreakpointListeners = function() {
            var enterFnArr = [],
                exitFnArr = [],
                i = 0,
                len = listeners.length;

            for(; i < len; i++) {
                var activeListener = listeners[i],
                    enterFn = activeListener.enter || undefined,
                    exitFn = activeListener.exit || undefined;

                // Wildcard support
                if(activeListener.type === '*') {
                    if(enterFn !== undefined) {
                        enterFnArr.push(enterFn);
                    }

                    if(exitFn !== undefined) {
                        exitFnArr.push(exitFn);
                    }
                } else if (testForCurrentBreakpoint(activeListener.type)) {
                    if(enterFn !== undefined && !listenersInit[i]) {
                        enterFnArr.push(enterFn);
                    }
                    listenersInit[i] = true;
                } else {
                    if(exitFn !== undefined && listenersInit[i]) {
                        exitFnArr.push(exitFn);
                    }
                    listenersInit[i] = false;
                }
            }

            // Create our event object
            var evtObj = { entering: curr, exiting: prev };

            // Loop through exit function to call
            for(var j = 0; j < exitFnArr.length; j++) {
                exitFnArr[j].call(null, evtObj);
            }

            // ...then loop through enter functions to call
            for(var k = 0; k < enterFnArr.length; k++) {
                enterFnArr[k].call(null, evtObj);
            }
        };

        /**
         * Takes a breakpoint(s) entry from the {@link listeners} collection and
         * tests it against the current active state / type.
         *
         * The method supports the wildcard as well.
         *
         * @param {String|Array} type Type which should be used for testing.
         * @returns {Boolean} Truthy, if the type matches, otherwise falsy.
         */
        var testForCurrentBreakpoint = function(type) {
            var ret = false;

            // We're dealing with a mulitple breakpoint listener
            if(type instanceof Array) {
                if(type.join().indexOf(curr) >= 0) {
                    ret = true;
                }

            // ...wildcard found
            } else if(type === '*') {
                ret = true;

            // ..just a single breakpoint listener
            } else if(typeof(type) === 'string') {
                if(type === curr) {
                    ret = true;
                }
            }

            return ret;
        };

        /**
         * Adds a listener to the {@link listeners} collection, so they get registered. It checks
         * as well if the newly added listener needs to be fired based on the {@link curr} state.
         *
         * @param {Object} listener Listener object which should be added.
         * @returns {Void}
         */
        var registerListenerToStack = function(listener) {
            var type = listener.type,
                enterFn = listener.enter || undefined;

            listeners.push(listener);
            listenersInit.push(false);

            if(testForCurrentBreakpoint(type)) {
                if(enterFn !== undefined) {
                    enterFn.call(null, { entering: curr, exiting: prev });
                }
                listenersInit[(listeners.length - 1)] = true;
            }
        };

        ret = {

            /**
             * Initializes the StateManager with the incoming breakpoint
             * declaration and starts the listing of the resize of the browser window.
             *
             * @param {Object|Array} userBreakPoints - User defined breakpoints.
             * @returns {Void}
             */
            init: function(userBreakPoints) {
                breakPoints = userBreakPoints;

                // Create getter methods for the different types
                $.each((breakPoints instanceof Array ? breakPoints : [ breakPoints ]), function() {
                    var type = this.type,
                        prettyType = capitaliseFirstLetter((type === '*' ? 'wildcard' : type));

                    ret['is' + prettyType] = function() {
                        return (type === curr);
                    };
                });

                checkResize();
            },

            /**
             * Registers one or multiple event listeners to the StateManager,
             * so they will be fired when the type matches the current active
             * state / type.
             *
             * @param {Object|Array} listener
             * @returns {Void}
             */
            registerListener: function(listener) {
                if(typeof(listener) === 'string') {
                    registerListenerToStack(listener);
                } else {
                    var i = 0,
                        len = listener.length;

                    for(; i < len; i++) {
                        registerListenerToStack(listener[i]);
                    }
                }
            },

            /**
             * Returns the current active type.
             *
             * @returns {String}
             */
            getCurrent: function() {
                return curr;
            },

            /**
             * Sets the event parent which should fire the state events.
             *
             * @param {jQuery} el jQuery object of the element which should fire the events.
             * @returns {Boolean}
             */
            setEventParent: function(el) {
                evtParent = el;
                return true;
            },

            /**
             * Checks if the device is currently running in portrait mode.
             *
             * @returns {Boolean} Truthy, if the device is in portrait mode, otherwise falsy
             */
            isPortraitMode: function() {
                return matchMedia('(orientation: portrait)').matches;
            },

            /**
             * Checks if the device is currently running in landscape mode.
             *
             * @returns {Boolean} Truthy, if the device is in landscape mode, otherwise falsy
             */
            isLandscapeMode: function() {
                return matchMedia('(orientation: landscape)').matches;
            },

            /**
             * Gets the viewport width.
             *
             * @returns {Number} The width of the viewport in pixels.
             */
            getViewportWidth: function() {
                return getWindowWidth();
            },

            /**
             * Gets the device pixel ratio. All retina displays should return a value > 1, all standard
             * displays like a desktop monitor will return 1.
             *
             * @returns {Number} The device pixel ratio.
             */
            getDevicePixelRatio: function() {
                return ( 'devicePixelRatio' in window && window.devicePixelRatio ? window.devicePixelRatio : 1);
            }
        };

        // Just return the public API instead of all available functions
        return ret;
    })();

    StateManager.init([{
        type: 'smartphone',
        enter: '0em',
        exit: '47.75em'
    }, {
        type: 'tablet',
        enter: '47.75em',
        exit: '64em'
    }, {
        type: 'desktop',
        enter: '64em',
        exit: '320em'
    }]);
})(jQuery, window, document);