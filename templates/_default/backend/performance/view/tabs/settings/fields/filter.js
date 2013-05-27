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
//{block name="backend/performance/view/tabs/settings/fields/filter"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Filter', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-filter',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/filter/title}Filters{/s}',

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
                    me.createDecriptionContainer("{s name=fieldset/filter/info}Here you can adjust various settings which impact the performance of product filters.{/s}")]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [
                    {
                        fieldLabel: '{s name=fieldset/filter/text/displayFiltersInListings}Display product filters in category listings{/s}',
                        name: 'filters[displayFiltersInListings]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/filter/text/displayFilterArticleCount}Display article count of each filter value{/s}',
                        name: 'filters[displayFilterArticleCount]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/filter/text/displayFiltersOnDetailPage}Display product filters on detail page{/s}',
                        name: 'filters[displayFiltersOnDetailPage]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/filter/text/sortProperties}Filter sort order{/s}',
                        name: 'filters[propertySorting]',
                        supportText: '{s name=fieldset/filter/text/sortProperties/support}In case that more than one filter group is configured in a category listing, this sort condition will be used to sort the filter values.{/s}',
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
                                { id: 0, name: '{s name=fieldset/filter/sort/alphanumeric}Sort by alphanumeric value{/s}' },
                                { id: 1, name: '{s name=fieldset/filter/sort/numeric}Sort by numeric value{/s}' },
                                { id: 2, name: '{s name=fieldset/filter/sort/article_count}Sort by article count{/s}' },
                                { id: 3, name: '{s name=fieldset/filter/sort/position}Sort by position{/s}' }
                            ]
                        })
                    }
                ]
            }
        ];
    }


});
//{/block}
