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
 */

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/form"}
Ext.define('Shopware.apps.Snippet.view.main.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.snippet-main-form',
    autoScroll: true,
    monitorValid: true,
    layout: 'anchor',
    bodyPadding: 10,

    /**
     * Array containing the records
     *
     * @array
     */
    snippets: [],

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.buttons = me.getButtons();
        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me            = this,
            formItems     = [],
            namespaces    = [],
            fieldsetItems = [];

        Ext.each(me.snippets, function(record) {
            if (!Ext.Array.contains(namespaces, record.get('namespace'))) {
                namespaces.push(record.get('namespace'));
            }
        });

        Ext.each(namespaces, function(namespace) {
            fieldsetItems = [];

            Ext.each(me.snippets, function(record) {
                if (record.get('namespace') !== namespace) {
                    return;
                }

                fieldsetItems.push({
                    fieldLabel: record.get('name'),
                    supportText: record.get('defaultValue'),
                    name: record.internalId,
                    value: record.get('value')
                });
            });

            formItems.push({
                xtype: 'fieldset',
                title: namespace,
                defaultType: 'textarea',
                defaults: {
                    layout: 'anchor',
                    grow: true,
                    growMin: 0,
                    labelWidth: 200,
                    anchor: '100%'
                },
                items: fieldsetItems
            });
        });

        return formItems;
    },

    /**
     * Creates buttons shown in form panel
     *
     * @return array
     */
    getButtons: function() {
        return [{
            cls: 'primary',
            text: '{s name=button_save_form}Save{/s}',
            action: 'save',
            formBind: true
        }];
    }
});
//{/block}
