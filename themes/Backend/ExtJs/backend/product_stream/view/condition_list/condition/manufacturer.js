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
//{block name="backend/product_stream/view/condition_list/condition/manufacturer"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Manufacturer', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\ManufacturerCondition';
    },

    getLabel: function() {
        return '{s name=manufacturer_condition}Manufacturer condition{/s}';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback) {
        var field = this.createSelection();
        callback(field);
    },

    load: function(key, value) {
        if (key !== this.getName()) {
            return null;
        }
        var field = this.createSelection();
        field.setValue(value);
        return field;
    },

    createStore: function() {
        return Ext.create('Shopware.store.Search', {
            model: 'Shopware.apps.Base.model.Supplier',
            pageSize: 10,
            configure: function() {
                return { entity: "Shopware\\Models\\Article\\Supplier" }
            }
        });
    },

    createSelection: function() {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Grid', {
            name: 'condition.' + this.getName(),
            searchStore: this.createStore(),
            idsName: 'manufacturerIds',
            store: this.createStore(),
            getErrorMessage: function() {
                return 'No manufacturer selected';
            }
        });
    }
});
//{/block}
