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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/common/settings"}
Ext.define('Shopware.apps.ProductStream.view.common.Settings', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.product-stream-settings',
    title: '{s name=settings}Settings{/s}',
    height: 170,
    bodyPadding: 10,
    cls: 'shopware-form',
    layout: 'anchor',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        return [
            this.createNameField(),
            this.createDescriptionField(),
            this.createSortingCombo()
        ];
    },

    createSortingCombo: function() {
        var me = this,
            store = me.createEntitySearchStore(
                "Shopware\\Models\\Search\\CustomSorting",
                'Shopware.apps.Base.model.CustomSorting'
            );

        store.remoteFilter = true;
        store.filter({
            property: 'sortings',
            expression: 'NOT LIKE',
            value: '%ManualSorting%'
        });

        me.sortingCombo = Ext.create('Shopware.form.field.SingleSelection', {
            fieldLabel: '{s name=sorting}Sorting{/s}',
            name: 'sortingId',
            allowBlank: false,
            forceSelection: true,
            anchor: '100%',
            store: store
        });

        return me.sortingCombo;
    },

    createNameField: function() {
        this.nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            anchor: '100%',
            allowBlank: false,
            fieldLabel: '{s name=name}Name{/s}',
            translatable: true,
        });

        return this.nameField;
    },

    createDescriptionField: function() {
        this.descriptionField = Ext.create('Ext.form.field.TextArea', {
            name: 'description',
            anchor: '100%',
            rows: 3,
            fieldLabel: '{s name=description}Description{/s}',
            translatable: true,
        });

        return this.descriptionField;
    }
});
//{/block}
