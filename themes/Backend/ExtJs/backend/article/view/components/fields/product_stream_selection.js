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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/article/view/main}
Ext.define('Shopware.apps.Article.view.components.fields.ProductStreamSelection', {
    extend: 'Shopware.form.field.PagingComboBox',
    alias: 'widget.article-components-fields-product-stream-selection',
    name: 'stream_selection',

    /**
     * Snippets for the field.
     * @object
     */
    snippets: {
        fields: {
            streamFieldLabel: '{s name=cross_selling/streams/field_label}Product stream{/s}',
            streamFieldSelection: '{s name=cross_selling/streams/field_selection}Please select...{/s}'
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

        me.store = me.createStore();

        Ext.apply(me, {
            pageSize: 15,
            triggerAction: 'all',
            valueField: 'id',
            fieldLabel: me.snippets.fields.streamFieldLabel,
            labelWidth: 155,
            displayField: 'formatted_name',
            emptyText: me.snippets.fields.streamFieldSelection,
            store: me.store
        });

        me.callParent(arguments);
    },

    /**
     * Creates a store which will be used
     * for the combo box.
     *
     * @public
     * @return [object] Ext.data.Store
     */
    createStore: function() {
        var me = this;

        var store = Ext.create('Shopware.store.Search', {
            fields: [ 'id', 'name', { name: 'formatted_name', convert: function (v, r) {
                return r.get('id') + " | " + r.get('name');
            }}],
            pageSize: 15,
            configure: function() {
                return { entity: "Shopware\\Models\\ProductStream\\ProductStream" }
            }
        });

        store.load({
            callback: function() {
                var record = store.getById(~~(1 * me.getValue()));
                me.select(record);
            }
        });

        return store;
    }
});
