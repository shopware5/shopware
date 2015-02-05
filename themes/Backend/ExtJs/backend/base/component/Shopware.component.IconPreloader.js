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
 */
Ext.define('Shopware.component.IconPreloader', {

    /**
     * Defines the basic path which will be used for the preloading
     * @string
     * @default null
     */
    loadPath: null,

    /**
     * Extension of the stylesheet files
     * @string
     */
    extension: '.css',

    /**
     * The stylesheets which will be loaded by the preloader
     * @array
     */
    iconSheets: [
        'extra-icon-set-01', 'extra-icon-set-02', 'extra-icon-set-03', 'extra-icon-set-04',
        'extra-icon-set-05', 'extra-icon-set-06', 'extra-icon-set-07', 'extra-icon-set-08',
        'extra-icon-set-keys', 'extra-icon-set-devices', 'core-icon-set-new'
    ],

    /**
     * Time in milliseconds, which will be delay the preloading.
     * @Number
     */
    preloadDelay: 500,

    /**
     * Initialize the component and starts the preloading process.
     *
     * @param { Object } options - Component configuration. Needs to contain
     *        the property `loadPath` to define the basic loading path.
     * @returns { void }
     */
    constructor: function(options) {
        var me = this, task;

        if(!options.hasOwnProperty('loadPath')) {
            Ext.Error.raise({
                sourceClass: me.$className,
                sourceMethod: "constructor",
                msg: me.$className + " needs an loadPath to work correctly."
            });
        }
        me.loadPath = options.loadPath;

        // Starts the preloading after a given delay
        Ext.defer(me.startPreloading, me.preloadDelay, me);
    },

    /**
     * Returns the path which will be used to preload the defined
     * stylesheets.
     *
     * @returns { null|String } - Returns the path, otherwise `null`
     */
    getLoadPath: function() {
        return this.loadPath;
    },

    /**
     * Sets the loading path.
     *
     * @param { String } path - The new loading path
     * @returns { Boolean } Truthy if the path was setted, otherwise falsy
     */
    setLoadPath: function(path) {
        if(!path.length) {
            return false;
        }
        this.loadPath = path;
        return true;
    },

    /**
     * The starter method to trigger the preloading of the icons.
     *
     * @returns { void }
     */
    startPreloading: function() {
        var me = this;

        Ext.Array.each(me.iconSheets, function(sheet) {
            me.injectStylesheet(sheet);
        });
    },

    /**
     * Injects the stylesheets which should be preloaded. The
     * stylesheet will be injected in the `head`-node of the
     * document.
     *
     * @param { String } sheet - Name of the stylesheet
     * @returns { void }
     */
    injectStylesheet: function(sheet) {
        var me = this,
            el = document.createElement('link'),
            head = Ext.getHead(),
            basicOpts = {
                'rel': 'stylesheet',
                'type': 'text/css',
                'media': 'all'
            };

        basicOpts = Ext.apply(basicOpts, {
            'href': me.loadPath + '/' + sheet + me.extension + "?" + Ext.shopwareRevision
        });

        for(var key in basicOpts) {
            el.setAttribute(key, basicOpts[key]);
        }

        head.appendChild(el);
    }
});
