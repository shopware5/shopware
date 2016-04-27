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
 * @version    $Id$
 * @author shopware AG
 */
Ext.define('Shopware.apps.Base.view.element.Select', {
    extend:'Ext.form.field.ComboBox',
    alias:[
        'widget.base-element-select',
        'widget.base-element-combo',
        'widget.base-element-combobox',
        'widget.base-element-comboremote'
    ],

    queryMode:'local',
    forceSelection: false,
    editable: true,
    valueField: 'id',
    displayField: 'name',

    initComponent:function () {
        var me = this;

        if (me.controller && me.action) {
            //me.value = parseInt(me.value);
            me.store = new Ext.data.Store({
                url:'{url controller=index}/' + me.controller + '/' + me.action,
                autoLoad:true,
                reader:new Ext.data.JsonReader({
                    root:me.root || 'data',
                    totalProperty:me.count || 'total',
                    fields:me.fields
                })
            });
            // Remove value field for reasons of compatibility
            me.valueField = me.displayField;
        }
        // Eval the store string if it contains a statement.
        if (typeof(me.store) == 'string' && me.store.indexOf('new ') !== -1) {
            //me.value = parseInt(me.value);
            eval('me.store = ' + me.store + ';');
            // Remove value field for reasons of compatibility
            me.valueField = me.displayField;
        } else if (typeof(me.store) === 'string') {
            me.store = me.getStoreById(me.store);
        }

        me.callParent(arguments);
    },

    setValue:function (value) {
        var me = this;

        if (value !== null && !me.store.loading && me.store.getCount() == 0) {
            me.store.load({
                callback:function () {
                    if(me.store.getCount() > 0) {
                        me.setValue(value);
                    } else {
                        me.setValue(null);
                    }
                }
            });
            return;
        }

        me.callParent(arguments);
    },

    /**
     * Tries to find the customised copy of the store specified by the given 'storeId'.
     * If such a store does not exist, the respective original store for the 'storeId'
     * is looked up and its settings are copied to a new store, which is then loaded.
     * This has two advantages: Firstly we only have to load the customized store once
     * for all config elements and secondly we don't run into problems when e.g. using
     * a Shopware 'base' store, which is also used by a plugin that e.g. changed the
     * base store's settings like 'pageSize'.
     *
     * @param string storeId
     * @return Ext.data.Store|null
     */
    getStoreById: function(storeId) {
        // Try to find the customised store with the respective ID
        var customisedStoreId = 'Shopware.apps.Base.view.element.Select.store.' + storeId;
        var store = Ext.data.StoreManager.lookup(customisedStoreId);
        if (store) {
            return store;
        }

        // Customised store has not been created yet, hence create one by copying
        // the settings of the original store
        var originalStore = Ext.data.StoreManager.lookup(storeId);
        if (!originalStore) {
            return null;
        }
        store = Ext.create('Ext.data.Store', {
            storeId: customisedStoreId,
            model: originalStore.model.getName(),
            pageSize: 1000,
            autoLoad: true,
            proxy: {
                type: originalStore.getProxy().type,
                url: originalStore.getProxy().url,
                reader: {
                    type: originalStore.getProxy().reader.type,
                    root: originalStore.getProxy().reader.root,
                    totalProperty: originalStore.getProxy().reader.totalProperty
                }
            }
        });

        return store;
    }
});
