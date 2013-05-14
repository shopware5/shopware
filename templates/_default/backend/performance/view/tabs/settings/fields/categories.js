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
 * @package    Customer
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Categories fieldSet
 */
//{block name="backend/performance/view/tabs/settings/fields/categories"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Categories', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-categories',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/categories/title}Categories{/s}',

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.items = me.getItems();
        me.callParent(arguments);

    },

    getItems: function() {
        var me = this;

        return [
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/information}Information{/s}',
                items: [
                    me.createDecriptionContainer("Allgemeine Beschreibung für das Kategorien-Modul <br>" +
                            "<br>" +
                            "<b>Wichtig: </b> Informationen")]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'category',
                        title: '{s name=fieldset/categories/repair}Repair Categories{/s}'
                    },
                    {
                        fieldLabel: '{s name=fieldset/categories/text/perPage}Articles per page{/s}',
                        helpText: '{s name=fieldset/categories/help/perPage}How many articles should be shown per page?{/s}',
                        name: 'categories[articlesperpage]',
                        xtype: 'numberfield',
                        minValue: 1
                    },
                    {
                        fieldLabel: '{s name=fieldset/categories/text/sort}Default sort order for listing{/s}',
                        helpText: '{s name=fieldset/categories/help/sort}In which order do you want to sort articles in category listing?{/s}',
                        name: 'categories[orderbydefault]',
                        xtype: 'textfield'
                    },
                    {
                        fieldLabel: '{s name=fieldset/categories/text/showSupplier}Show category supplier{/s}',
                        helpText: '',
                        name: 'categories[showSupplierInCategories]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/categories/text/sortProperties}Property sort order{/s}',
                        name: 'categories[propertySorting]',
                        xtype: 'combo',
                        valueField: 'id',
                        editable: false,
                        displayField: 'name',
                        store: Ext.create('Ext.data.Store', {
                            fields: [
                                { name: 'id', type: 'int' },
                                { name: 'name', type: 'string' }
                            ],
                            data: [
                                { id: 1, name: '{s name=fieldset/categories/sort/all}Use all functions{/s}' },
                                { id: 2, name: '{s name=fieldset/categories/sort/name}Sort by name{/s}' },
                                { id: 3, name: '{s name=fieldset/categories/sort/position}Sort by position{/s}' }
                            ]
                        })
                    }
                ]
            }
        ];
    }


});
//{/block}
