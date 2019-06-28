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
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/condition_list/condition/property"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Property', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\PropertyCondition';
    },

    getLabel: function() {
        return '{s name=property_condition}Property condition{/s}';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback, container, conditions) {
        var me = this;

        Ext.create('Shopware.apps.ProductStream.view.condition_list.field.PropertyWindow', {
            subApp: me.subApp,
            applyCallback: function(group) {
                var field = me.createField(group.get('id'), group.get('name'));
                callback(field);
                me.updateTitle(container, group.get('name'));
            }
        }).show();
    },

    load: function(key, value, container) {
        var me = this;

        if (key.indexOf(this.buildName(value.groupId)) < 0) {
            return;
        }

        var field = this.createField(value.groupId, value.groupName);
        me.updateTitle(container, value.groupName);
        container.fixToggleTool();
        field.setValue(value);
        return field;
    },

    createField: function(groupId, groupName) {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Property', {
            name: 'condition.' + this.buildName(groupId),
            searchStore: this.createStore(groupId),
            store: this.createStore(groupId),
            groupId: groupId,
            groupName: groupName,
            flex: 1
        });
    },

    buildName: function(groupId) {
        return this.getName() + '|' + groupId;
    },

    createStore: function(groupId) {
        var store = Ext.create('Shopware.store.Search', {
            fields: [
                { name: 'id', type: 'int' },
                { name: 'name', type: 'string', mapping: 'value'}
            ],
            configure: function() {
                return { entity: "Shopware\\Models\\Property\\Value" }
            }
        });
        store.getProxy().extraParams.groupId = groupId;
        return store;
    },


    updateTitle: function(container, name) {
        container.setTitle(this.getLabel() + ': ' + name);
    }
});
//{/block}
