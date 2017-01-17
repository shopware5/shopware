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

    listeners: {
        //@todo Find a way to add the custom filter if there is one
        beforequery: function(qe, eOpts) {
            var me = this;
            if (! me.store instanceof Ext.data.Store ||  ! me.store.remoteFilter)
                return;

            var queryString = qe.query;
            var delimiter = Ext.String.trim(me.delimiter + '');
            //@todo Find a way to keep the previous values selected after store filtering in multiSelect Mode
            if (me.multiSelect && queryString.indexOf(delimiter) !== -1 ) {
                values = queryString.split(delimiter);
                var filters = [],
                    i = 0,
                    len = values.length;
                for(; i < len; i++) {
                    value = Ext.String.trim(values[i] + '');
                    if (! value)
                        continue;
                    value = '%' + value + '%';
                    filters.push(new Ext.util.Filter({
                        property: me.displayField,
                        value: value,
                        operator : 'OR'
                    }));
                }
                me.store.filters.clear();
                me.store.filter(filters);
                me.getPicker().refresh();
                return false;
            }else {
                qe.query = '%' + qe.query + '%';
            }
        }
    },

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
        if (typeof(me.store) == 'string' && me.store.indexOf('new ') !== -1) {
            //me.value = parseInt(me.value);
            eval('me.store = ' + me.store + ';');
            // Remove value field for reasons of compatibility
            me.valueField = me.displayField;
        } else if (typeof(me.store) === 'string' && me.store.substring(0, 5) !== 'base.') {
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
        try {
            return Ext.create(storeId, {
                pageSize: 1000,
                autoLoad: true
            });
        } catch (e) {
            return null;
        }
    }
});
