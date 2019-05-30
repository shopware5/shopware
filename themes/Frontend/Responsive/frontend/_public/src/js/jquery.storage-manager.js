;(function (window, document) {
    'use strict';

    /**
     * Global storage manager
     *
     * The storage manager provides a unified way to store items in the localStorage and sessionStorage.
     * It uses a polyfill that uses cookies as a fallback when no localStorage or sessionStore is available or working.
     *
     * @example
     *
     * Saving an item to localStorage:
     *
     * StorageManager.setItem('local', 'key', 'value');
     *
     * Retrieving it:
     *
     * var item = StorageManager.getItem('local', 'key'); // item === 'value'
     *
     * Basically you can use every method of the Storage interface (http://www.w3.org/TR/webstorage/#the-storage-interface)
     * But notice that you have to pass the storage type ('local' | 'session') in the first parameter for every call.
     *
     * @example
     *
     * Getting the localStorage/sessionStorage (polyfill) object
     *
     * var localStorage = StorageManager.getStorage('local');
     * var sessionStorage = StorageManager.getStorage('session');
     *
     * You can also use its shorthands:
     *
     * var localStorage = StorageManager.getLocalStorage();
     * var sessionStorage = StorageManager.getSessionStorage();
     */
    window.StorageManager = (function () {
        var storage = {
            }, p;

        var enableBlackHoleStorage = function () {
            var blackHoleStorage = {
                length: 0,
                clear: function () {},
                getItem: function () { return null; },
                key: function () { return null; },
                removeItem: function () { return null; },
                setItem: function () { return null; }
            };

            storage = {
                local: blackHoleStorage,
                session: blackHoleStorage
            };
        };

        try {
            if (window.StateManager.hasCookiesAllowed()) {
                storage = {
                    local: window.localStorage,
                    session: window.sessionStorage
                };
            } else {
                enableBlackHoleStorage();
            }
        } catch (err) {
            // User has blocked local storage in browser settings
            enableBlackHoleStorage();
        }

        /**
         * Helper function to detect if cookies are enabled.
         * @returns {boolean}
         */
        function hasCookiesSupport() {
            // if cookies are already present assume cookie support
            if ('cookie' in document && (document.cookie.length > 0)) {
                return true;
            }

            document.cookie = 'testcookie=1;';
            var writeTest = (document.cookie.indexOf('testcookie') !== -1);
            document.cookie = 'testcookie=1' + ';expires=Sat, 01-Jan-2000 00:00:00 GMT';

            return writeTest;
        }

        // test for safari's "QUOTA_EXCEEDED_ERR: DOM Exception 22" issue.
        for (p in storage) {
            if (!storage.hasOwnProperty(p)) {
                continue;
            }

            try {
                storage[p].setItem('storage', '');
                storage[p].removeItem('storage');
            } catch (err) {
            }
        }

        // Just return the public API instead of all available functions
        return {
            /**
             * Returns the storage object/polyfill of the given type.
             *
             * @returns {Storage|StoragePolyFill}
             */
            getStorage: function (type) {
                return storage[type];
            },

            /**
             * Returns the sessionStorage object/polyfill.
             *
             * @returns {Storage|StoragePolyFill}
             */
            getSessionStorage: function () {
                return this.getStorage('session');
            },

            /**
             * Returns the localStorage object/polyfill.
             *
             * @returns {Storage|StoragePolyFill}
             */
            getLocalStorage: function () {
                return this.getStorage('local');
            },

            /**
             * Calls the clear() method of the storage from the given type.
             *
             * @param {String} type
             */
            clear: function (type) {
                this.getStorage(type).clear();
            },

            /**
             * Calls the getItem() method of the storage from the given type.
             *
             * @param {String} type
             * @param {String} key
             * @returns {String}
             */
            getItem: function (type, key) {
                return this.getStorage(type).getItem(key);
            },

            /**
             * Calls the key() method of the storage from the given type.
             *
             * @param {String} type
             * @param {Number|String} i
             * @returns {String}
             */
            key: function (type, i) {
                return this.getStorage(type).key(i);
            },

            /**
             * Calls the removeItem() method of the storage from the given type.
             *
             * @param {String} type
             * @param {String} key
             */
            removeItem: function (type, key) {
                this.getStorage(type).removeItem(key);
            },

            /**
             * Calls the setItem() method of the storage from the given type.
             *
             * @param {String} type
             * @param {String} key
             * @param {String} value
             */
            setItem: function (type, key, value) {
                this.getStorage(type).setItem(key, value);
            },

            /**
             * Helper function call to check if cookies are enabled.
             */
            hasCookiesSupport: hasCookiesSupport()
        };
    })();
})(window, document);
