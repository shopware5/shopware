/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/newsletter_manager/view/components/target_selection}
Ext.define('Shopware.apps.NewsletterManager.view.components.fields.TargetSelection', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.newsletter-components-fields-target-selection',
    name: 'target_selection',

    /**
     * Snippets for the field.
     * @object
     */
    snippets: {
        fields: {
            please_select: '{s name=fields/please_select}Please select...{/s}',
            category_select: '{s name=fields/category_select}Select target{/s}'
        }
    },

    /**
     * Initiliaze the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            queryMode: 'local',
            triggerAction: 'all',
            fieldLabel: me.snippets.fields.category_select,
            valueField: 'target',
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
        var me = this,
            // we have only to possible values for the store here, so its defined locally
            store =  new Ext.data.ArrayStore({
                fields: [ 'id', 'target', 'name' ],
                data: [
                    [ 1, '_blank', 'Extern' ],
                    [ 2, '_parent', 'Shopware' ]
                ]
            });

        store.load({
            callback: function() {
                //if the component wasn't fully realized, yet, getValue() will fail.
                if(!this.displayTpl){
                    return;
                }
                var record, value = me.getValue();

                if(value) {
                    record = store.findRecord('target', value);
                }
                if(!record) {
                    record = store.first();
                }
                me.select(record);
            }
        });

        return store;
    }


});