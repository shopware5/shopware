/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name="backend/product_stream/main"}
//{block name="backend/product_stream/view/condition_list/condition/attribute"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Attribute', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\ProductAttributeCondition';
    },

    getLabel: function() {
        return '{s name="attribute_condition"}Attribute condition{/s}';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback, container, conditions) {
        var me = this;

        Ext.create('Shopware.apps.ProductStream.view.condition_list.field.AttributeWindow', {
            subApp: me.subApp,
            applyCallback: function(attribute) {
                var field = me.createField(attribute);
                callback(field);
                me.updateTitle(container, attribute);
            }
        }).show();
    },

    load: function(key, value, container) {
        if (key.indexOf(this.getName()) < 0) {
            return;
        }

        var field = this.createField(value);
        field.setValue(value);

        this.updateTitle(container, value);
        container.fixToggleTool();

        return field;
    },

    createField: function(attribute) {
        switch (attribute.type) {
            case "date":
                return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.AttributeDate', {
                    name: 'condition.' + this.getName() + '|' + attribute.column,
                    attributeField: attribute.column,
                    type: attribute.type
                });
            case "datetime":
                return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.AttributeDateTime', {
                    name: 'condition.' + this.getName() + '|' + attribute.column,
                    attributeField: attribute.column,
                    type: attribute.type
                });
            default:
                return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Attribute', {
                    name: 'condition.' + this.getName() + '|' + attribute.column,
                    attributeField: attribute.column,
                    type: attribute.type
                });
        }
    },

    updateTitle: function(container, name) {
        if (name.column !== undefined) {
            container.setTitle(this.getLabel() + ': ' + name.column);
        } else {
            container.setTitle(this.getLabel() + ': ' + name.field);
        }
    }
});
//{/block}
