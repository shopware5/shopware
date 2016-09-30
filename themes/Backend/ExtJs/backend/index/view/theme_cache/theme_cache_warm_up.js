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
 * @package    Window
 * @subpackage Plugin
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Theme Cache Warm Up Window
 *
 * This component displays the "Theme Cache Warm Up" window that
 * is used in multiple locations across the backend
 */

//{namespace name=backend/index/view/theme_cache}
Ext.define('Shopware.apps.Index.view.themeCache.ThemeCacheWarmUp', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.SubWindow',

    alias : 'widget.theme-cache-warm-up-window',

    /**
     * Window title
     * @string
     */
    title: '{s name=window/title}Theme cache warm up{/s}',

    /**
     * Define window width
     * @integer
     */
    width: 360,

    /**
     * Define window height
     * @integer
     */
    height: 390,

    /**
     * Set anchor layout
     * @object
     */
    layout: 'anchor',

    defaults: {
        anchor: '100%'
    },

    /**
     * If the modal property is set to true, the user can't change the window focus to another window.
     * @boolean
     */
    modal: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Disable the close icon in the window header
     * @boolean
     */
    closable: false,

    /**
     * Disable window resize
     * @boolean
     */
    resizable: false,

    /**
     * Disables the maximize button in the window header
     * @boolean
     */
    maximizable: false,
    /**
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    /**
     * If single shop mode is on, this field will have the shop id value
     */
    singleShopId: null,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        cancel: '{s name=button/cancel}Cancel process{/s}',
        start: '{s name=button/start}Start process{/s}',
        close: '{s name=button/close}Close window{/s}',
        progressBar: '{s name=progress/progress_bar_text}0 shop(s) found{/s}',
        singleProgressBar: '{s name=progress/progress_bar_single_text}Warm up cache for [0]{/s}',
        loading: '{s name=progress/progress_bar_loading}Loading...{/s}',
        infoTitle: '{s name=fieldset/information/title}Information{/s}',
        infoDetail: '{s name=fieldset/information/detail}This will warm up the cache for your shop themes. This process will take a few seconds per shop{/s}'
    },

    initComponent: function () {
        var me = this;

        me.addEvents(
            /**
             * Fired when the cache warm up start button is pressed
             */
            'themeCacheWarmUpStartProcess',

            /**
             * Fired when the cache warm up cancel button is pressed
             */
            'themeCacheWarmUpCancelProcess'
        );

        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this;

        me.progressBar = me.createProgressBar();
        me.shopSelector = me.createShopSelector();

        return [
            {
                xtype: 'fieldset',
                height: 230,
                defaults: me.defaults,
                title: me.snippets.infoTitle,
                items: [
                    Ext.create('Ext.container.Container', {
                        style: 'color: #999; font-style: italic; margin: 0 0 15px 0;',
                        html: me.snippets.infoDetail
                    })
                ]
            },
            me.shopSelector,
            me.progressBar,
            me.createButtons()
        ];
    },

    /**
     * Creates the progress which displays the progress status for the cache generation.
     */
    createProgressBar: function() {
        var me = this;

        return Ext.create('Ext.ProgressBar', {
            animate: true,
            text: me.snippets.loading,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls:'left-align'
        });
    },

    createShopSelector: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            valueField: 'id',
            forceSelection: true,
            multiSelect: true,
            editable: false,
            emptyText: '{s name=fieldset/information/shopselection}Shop selection{/s}',
            listeners: {
                scope: me,
                select: function(combo, records) {
                    me.setShops(records);
                }
            }
        });
    },

    /**
     * Sets the shops and changes view accordingly
     * @param records
     */
    setShops: function(records) {
        var me = this;

        if (Ext.isEmpty(me.singleShopId) && records.length > 1) {
            me.progressBar.updateProgress(
                0, me.snippets.progressBar.replace('0', records.length)
            );
        } else {
            me.progressBar.updateProgress(
                0, Ext.String.format(me.snippets.singleProgressBar, records[0].get('name'))
            );
        }

        if (records.length > 0) {
            me.resetButtons();
        }
    },

    /**
     * If called, it means that the warm up process is intended specifically for a single shop
     */
    setSingleShopId: function(shopId) {
        var me = this;

        me.singleShopId = shopId;
    },

    /**
     * Resets buttons to "start" stage
     */
    resetButtons: function() {
        var me = this;

        me.startButton.show();
        me.startButton.enable();
        me.cancelButton.hide();
        me.closeButton.enable();
    },

    /**
     * Creates the cancel button which allows the user to cancel the cache generation in the
     * batch window. Event will be handled in the batch controller.
     */
    createStartButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.start,
            cls: 'primary',
            action: 'start',
            disabled: true,
            handler: function() {
                if (!Ext.isNumber(me.singleShopId)) {
                    this.hide();
                    me.cancelButton.enable();
                    me.cancelButton.show();
                } else {
                    this.disable();
                }
                me.closeButton.disable();
                me.filterThemes();
                me.shopSelector.disable();
                me.fireEvent('themeCacheWarmUpStartProcess');
            }
        });
    },

    /**
     * Filter shop store with selected shops before starting
     * warumup process.
     */
    filterThemes: function() {
        var me = this,
            selectedShops = me.shopSelector.getValue();
        if (Ext.isEmpty(selectedShops)) {
            return;
        }

        me.shopSelector.getStore().filterBy(function(record, id) {
            return Ext.Array.contains(selectedShops, id);
        });
    },

    /**
     * Creates the cancel button which allows the user to cancel the cache generation in the
     * batch window. Event will be handled in the batch controller.
     */
    createCancelButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            cls: 'primary',
            action: 'cancel',
            disabled: false,
            hidden: true,
            handler: function() {
                this.disable();
                me.shopSelector.enable();
                me.fireEvent('themeCacheWarmUpCancelProcess');
            }
        });
    },

    /**
     * Creates the close button which allows the user to close the window. The window closing is handled over this
     * button to prevent that the user close the window while the batch process is already working.
     * So the user have to wait until the process are finish or the user can clicks the cancel button.
     * The button will enabled after the batch process are finish or the cancel event are fired and the batch process
     * successfully canceled.
     */
    createCloseButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.close,
            flex: 1,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function() {
                me.destroy();
            }
        });
    },

    /**
     * Creates the button container for the close and cancel button
     *
     * @return Ext.container.Container
     */
    createButtons: function() {
        var me = this;

        me.startButton  = me.createStartButton();
        me.closeButton  = me.createCloseButton();
        me.cancelButton = me.createCancelButton();

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.startButton,
                me.cancelButton,
                me.closeButton
            ]
        });
    }
});
