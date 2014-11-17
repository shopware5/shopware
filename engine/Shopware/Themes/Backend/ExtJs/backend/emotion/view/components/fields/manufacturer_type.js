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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/emotion/view/components/manufacturer_type}
Ext.define('Shopware.apps.Emotion.view.components.fields.ManufacturerType', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-manufacturer-type',
    name: 'manufacturer_type',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'manufacturer_type': '{s name=manufacturer_type/fields/manufacturer_type}Manufacturer type{/s}',
            'empty_text': '{s name=manufacturer_type/fields/empty_text}Please select...{/s}'
        },
        store: {
            'manufacturers_by_cat': '{s name=manufacturer_type/store/manufacturer_by_cat}Manufacturer by category{/s}',
            'selected_manufacturers': '{s name=manufacturer_type/store/selected_manufacturers}Selected manufacturers{/s}'
        }
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            emptyText: me.snippets.fields.empty_text,
            fieldLabel: me.snippets.fields.manufacturer_type,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: me.createStore()
        });

        me.callParent(arguments);
    },

    /**
     * Creates a local store which will be used
     * for the combo box. We don't need that data.
     *
     * @public
     * @return [object] Ext.data.Store
     */
    createStore: function() {
        var me = this, snippets = me.snippets.store;

        return Ext.create('Ext.data.JsonStore', {
            fields: [ 'value', 'display' ],
            data: [{
                value: 'manufacturers_by_cat',
                display: snippets.manufacturers_by_cat
            }, {
                value: 'selected_manufacturers',
                display: snippets.selected_manufacturers
            }]
        });
    }
});
