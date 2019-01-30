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
    isCustomStore: false,

    initComponent:function () {
        var me = this;

        if (me.controller && me.action) {
            //me.value = parseInt(me.value);
            me.store = new Ext.data.Store({
                url:'{url controller=index}/' + me.controller + '/' + me.action,
                autoLoad:true,
                remoteFilter: true,
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
        if (typeof me.store === 'string' && me.store.indexOf('new ') !== -1) {
            eval('me.store = ' + me.store + ';');
            // if is no custom store remove value field for reasons of compatibility
            if(!me.isCustomStore) {
                me.valueField = me.displayField;
            }
        } else if (typeof me.store === 'string' && me.store.substring(0, 5) !== 'base.') {
            me.store = me.getStoreById(me.store);
        }

        if (me.store instanceof Ext.data.Store && me.filter) {
            // Apply the filter on the store
            me.store.clearFilter(true);
            me.store.filter(me.filter);
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
     * plugin example usage:
     * public function install()
     * {
     *     $form = $this->Form();
     *
     *     $form->setElement("select", "test123", [
     *         "valueField" => "id",
     *         "displayField" => "name",
     *         "queryMode" => "remote",
     *         "store" => "Shopware.apps.Base.store.Country"
     *     ]);
     *
     *     return true;
     * }
     *
     * @param storeId string
     * @return Ext.data.Store|null
     */
    getStoreById: function(storeId) {
        // Try to find any previously-loaded config select store for the given storeId
        var configSelectStoreId = 'Shopware.apps.Base.view.element.Select.store.' + storeId;
        // If filters are defined on this element, we need to use a different store for that filter combination
        // The filter gets applied in `initComponent()` above.
        if (this.filter) {
            configSelectStoreId += '.filter.' + JSON.stringify(this.filter);
        }
        var store = Ext.data.StoreManager.lookup(configSelectStoreId);

        if (store) {
            return store;
        }

        try {
            store = Ext.create(storeId, {
                storeId: configSelectStoreId,
                pageSize: 1000
            });
            // Override the load() method so the store is only ever loaded once
            Ext.override(store, {
                load: function () {
                    if (!this.loadCalled) {
                        this.loadCalled = true;
                        this.callParent(arguments);
                    } else {
                        this.fireEvent('load', this)
                    }
                }
            });

            return store;
        } catch (e) {
            // Store class was not found - no such store exists
            return null;
        }
    }
});
