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
//{namespace name=backend/emotion/view/components/category_selection}
Ext.define('Shopware.apps.Emotion.view.components.fields.CategorySelection', {
    extend: 'Shopware.form.field.PagingComboBox',
    alias: 'widget.emotion-components-fields-category-selection',
    name: 'category_selection',

    /**
     * Snippets for the field.
     * @object
     */
    snippets: {
        fields: {
            please_select: '{s name=fields/please_select}Please select...{/s}'
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
            pageSize: 15,
            triggerAction: 'all',
            valueField: 'id',
            displayField: 'name',
            emptyText: me.snippets.fields.please_select,
            store: me.createStore()
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
        var me = this, store = Ext.create('Shopware.apps.Emotion.store.CategoryPath', { pageSize: 15 });
        store.getProxy().extraParams.parents = true;

        store.load({
            callback: function() {
                var record = store.getById(~~(1 * me.getValue()));
                me.select(record);
            }
        });

        return store;
    }
});
