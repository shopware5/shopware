/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Base
 * @subpackage Component
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

/**
 * Shopware Class cache
 *
 * The class provides an interface to the `localStorage` which
 * will be used for class caching in the Ext.Loader.
 *
 * The class provides a simliar API as the `Ext.util.MixedCollection`
 * and features different helper methods which will be useful for
 * later usage. The used storage could be changed using the `setStorage`
 * method. Selectable are `localStorage` or `localSession`
 *
 * The cache invalidation is unfortunately triggered through "Smarty". If
 * the localStorage reaches the size limit the oldest entry (first entry in the storage)
 * will be removed. After that the `add` method calls itself to retry to add the entry.
 *
 * @example - Add entry to the storage
 * <code>
 *   Shopware.data.ClassCache.add('key', 'value');
 * </code>
 *
 * @constructor
 * @singleton
 */
Ext.define('Shopware.data.ClassCache',
/** @lends Ext.Base */
{

    /**
     * When set to true, the class will be instantiated as singleton.
     * @boolean
     */
    singleton: true,

    /**
     * Type of storage which should be used by the class.
     * @string
     */
    storage: 'localStorage',

    /**
     * Add the `obversable` mixin to include basic
     * event handling.
     * @object
     */
    mixins: {
        observable: 'Ext.util.Observable'
    },

    /**
     * Constructor of the singleton class
     * which checks if the browser supports
     * the `localStorage`.
     *
     * @returns { Boolean }
     */
    constructor: function() {
        var me = this, key, i = 0;

        me.map = {};

        // Check if the browser supports the `localStorage`
        if(!me.hasStorageSupport()) {
            return false;
        }

        // Fill the keys array
        for(key in window[me.storage]) {
            me.map[i] = key;
            i++;
        }

        // Initialize the mixin'
        me.mixins.observable.constructor.call(me);
    },

    /**
     * Sets the used storage type for the collection.
     *
     * @param { String } storageType - localStorage or localSession
     * @returns { Boolean }
     */
    setStorage: function(storageType) {
        var me = this, old = me.storage;

        if (!storageType === 'localStorage'
            || !storageType === 'localSession') {
            return false;
        }

        if(me.hasStorageSupport(storageType)) {
            me.storage = storageType;
        }

        if(me.hasListeners.storagechange) {
            me.fireEvent('storagechange', me.storage, old);
        }

        return true;
    },

    /**
     * Returns the type of storage which is used right now.
     *
     * @returns { String } The type of the used storage
     */
    getStorageType: function() {
        return this.storage;
    },

    /**
     * Returns the used storage.
     *
     * @returns { Object } The used storage..
     */
    getStorage: function() {
        return window[this.storage];
    },

    /**
     * Checks if the browser supports the `localStorage`
     *
     * @returns { Boolean } Truthy if the browser supports the storage type, otherwise falsy.
     */
    hasStorageSupport: function(storageType) {
        var me = this;

        storageType = storageType || me.storage;
        try {
            return storageType in window && window[storageType] !== null;
        } catch(e) {
            return false;
        }
    },

    /**
     * Returns the number of items in the collection.
     * @returns { Number } the number of items in the collection.
     */
    getCount: function() {
        return window[this.storage].length;
    },

    /**
     * Returns the item associated with the passed key
     * @param { String } key - The key or index of the item.
     */
    get: function(key) {
        return window[this.storage].getItem(key);
    },

    /**
     * Returns the item at the specified index.
     * @param { Number } index - The index of the item
     * @returns { Object|String|Number } The item at the specific index.
     */
    getAt: function(index) {
        var me = this;
        return window[me.storage].getItem(me.map[index]);
    },

    /**
     * Returns the first item in the collection.
     *
     * @returns { Object } the first item in the collection.
     */
    first: function() {
        return window[this.storage][this.map[0]];
    },

    /**
     * Returns the last item in the collection.
     *
     * @returns { Object } the last item in the collection
     */
    last: function() {
        return window[this.storage][this.map[this.getCount() - 1]];
    },

    /**
     * Returns index within the collection of the passed item.
     *
     * @param { Object|String|Number } item The item to find the index of.
     * @returns { Number index of the passed item
     */
    indexOf: function(item) {
        var items = window[this.storage],
            key, foundKey;

        for(key in items) {
            if(items[key] === item) {
                foundKey = key;
                break;
            }
        }

        return (!foundKey) ? -1 : Ext.Object.getKey(this.map, foundKey);
    },

    /**
     * Executes the specified function once for every item in the collection.
     * The function should return a boolean value. Returning false from the
     * function will stop the iteration.
     *
     * @param { Function } fn - The function to execute for each item.
     * @param { Object } scope (optional) - The scope in which the function
     *        is executed. Defaults is the current item of iteration.
     * @returns { void }
     */
    each: function(fn, scope) {
        var items = [], i, len, item

        for(i in window[this.storage]) {
            items.push(window[this.storage][i]);
        }

        items = [].concat(items);  // each safe for removal
        len = items.length;
        for(i = 0; i < len; i++) {
            item = items[i];
            if(fn.call(scope || item, item, i, len) === false) {
                break;
            }
        }
    },

    /**
     * Executes the specified function once for every key in the collection, passing each
     * key, and its associated item as the first two parameters.
     *
     * @param { Function } fn - The function to execute for each item.
     * @param { Object } scope (optional) - The scope in which the function
     *        is executed. Defaults is the current item of iteration.
     * @returns { void }
     */
    eachKey: function(fn, scope) {
        var keys = this.map,
            items = window[this.storage],
            i, len = items.length;

        for(i in items) {
            fn.call(scope || window, keys[i], items[i], len);
        }
    },

    /**
     * Adds an item to the collection. The method
     * also checks the size of the item, checks the size
     * of the `localStorage` and cleans the last item(s)
     * to ensure that the item could be placed in the localStorage.
     *
     * @param { Number|String } key - The key or index of the item.
     * @param { Object|String|Number } data - The associated data to the key.
     * @return { Boolean }
     */
    add: function(key, data) {
        var me = this, item, index;

        try {
            item = window[me.storage].setItem(key, data);
            index = me.indexOf(data);
            if(index) {
                me.map[index] = key;
            } else {
                me.map[me.getCount() - 1] = key;
            }

        } catch(err) {
            me.clear();
            me.add.call(me, key, data);
        }

        if(this.hasListeners.add) {
            this.fireEvent('add', item);
        }

        return true;
    },

    /**
     * Remove all items in the passed array from the collection.
     *
     * @param { Array } items - An array of items to be removed.
     * @returns { Shopware.data.ClassCache } this object
     */
    removeAll: function(items) {
        items = [].concat(items);
        var i = 0, len = items.length;

        for( ;i < len; i++) {
            this.remove(items[i]);
        }

        return this;
    },

    /**
     * Removes the passed item from the collection.
     *
     * @param { Object|String|Number } item - The item which should be removed
     * @returns { Boolean }
     */
    remove: function(item) {
        var removed = this.removeAt(this.indexOf(item));
        if(this.hasListeners.remove) {
            this.fireEvent('remove', item, removed);
        }

        return removed;
    },

    /**
     * Removes an item at the given index
     * @param { Number } index - Index of the item
     * @returns { Boolean }
     */
    removeAt: function(index) {
        var me = this;

        window[me.storage].removeItem(this.map[index]);
        delete me.map[index];
        return true;
    },

    /**
     * Removes all items from the collection.
     * @returns { Boolean }
     */
    clear: function() {
        var me = this;
        window[this.storage].clear();
        me.map = {};

        if(this.hasListeners.clear) {
            this.fireEvent('clear');
        }

        return true;
    },

    /**
     * Messures the size of the `localStorage` due to
     * the limit of 5 MB per domain.
     *
     * @returns { Number } size of the `localStorage`
     */
    messureStorageSize: function() {
        return 1024 * 1024 * 5 - unescape(encodeURIComponent(JSON.stringify(window[this.storage]))).length;
    },

    /**
     * Clears the last entry in the `localStorage`. The method
     * is only used internal and should not be called directly.
     *
     * @private
     * @returns { Object|Boolean }
     */
    _clearLastItem: function() {
        var me = this,
            lastItem = me.first();
        return me.remove(lastItem);
    }
});
