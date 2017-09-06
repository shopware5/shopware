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
 * @package    Customer
 * @subpackage Address
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/address}

/**
 * Shopware UI - Customer address window backend module
 */
// {block name="backend/customer/view/address/detail/window"}
Ext.define('Shopware.apps.Customer.view.address.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.customer-address-detail-window',
    height: 600,
    width: '80%',
    title: '{s name="detail/window/title"}Address details{/s}',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        if (me.record && me.record.get('id')) {
            me.attributeForm.loadAttribute(me.record.get('id'));
        }

        Shopware.app.Application.on('address-save-successfully', me.onSaveSuccessful);
    },

    /**
     * Set title of first tab and add attribute tab
     *
     * @returns array
     */
    createTabItems: function() {
        var me = this,
            items = me.callParent(arguments);

        items[0].title = me.title;
        items.push(me.createAttributesTab());

        return items;
    },

    /**
     * Create the attribute tab
     *
     * @returns Ext.container.Container
     */
    createAttributesTab: function() {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            allowTranslation: false,
            table: 's_user_addresses_attributes'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            padding: 20,
            items: [
                me.attributeForm
            ]
        });
    },

    /**
     * Save attributes after saving the record
     *
     * @param controller
     * @param record
     * @param window
     */
    onSaveSuccessful: function(controller, record, window) {
        window.attributeForm.saveAttribute(record.get('id'), function(success) {
            if (!success) {
                window.close();
                return;
            }

            Ext.Ajax.request({
                url: '{url controller=Address action=syncAttribute}',
                params: {
                    id: record.get('id')
                },
                callback: function(options, success) {
                    if (success) {
                        window.close();
                    }
                }
            });
        });
    }
});
// {/block}
