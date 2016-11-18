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
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Filter fieldSet
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
                    me.createDescriptionContainer("{s name=fieldset/filter/info}Here you can adjust various settings which impact the performance of product filters.{/s}")]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/listings}Listings{/s}',
                items: [{
                    name: 'filters[showSupplierInCategories]',
                    fieldLabel: '{s name=fieldset/filter/text/showManufacturerFacet}Show manufacturer filter{/s}',
                    helpText:   '{s name=fieldset/filter/text/showManufacturerFacetHelp}Allow customers to filter product lists by manufacturer{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showImmediateDeliveryFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showImmediateDeliveryFacet}Show immediate delivery filter{/s}',
                    helpText:   '{s name=fieldset/filter/text/showImmediateDeliveryFacetHelp}Allows customers to show only products that are in stock.{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showShippingFreeFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showShippingFreeFacet}Show free shipping filter{/s}',
                    helpText:   '{s name=fieldset/filter/text/showShippingFreeFacetHelp}Allows customers to filter products by free shipping costs{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showPriceFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showPriceFacet}Show price filter{/s}',
                    helpText:   '{s name=fieldset/filter/text/showPriceFacetHelp}Allow customers to filter products by price{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showVoteAverageFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showVoteAverageFacet}Show rating filter{/s}',
                    helpText:   '{s name=fieldset/filter/text/showVoteAverageFacetHelp}Allow customers to filter products by average user rating{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[displayFiltersInListings]',
                    fieldLabel: '{s name=fieldset/filter/text/showPropertyFacet}Show property(ies) filter(s){/s}',
                    helpText:   '{s name=fieldset/filter/text/showPropertyFacetHelp}Allow customers to filter products by its property(ies){/s}',
                    cls: 'property-facet',
                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[generatePartialFacets]',
                    fieldLabel: '{s name=fieldset/filter/text/generatePartialFacets}Display only combinable filters{/s}',
                    helpText:   '{s name=fieldset/filter/text/generatePartialFacetsHelp}When the customer filters a listing, filters will be reloaded and will be deactivated if they would lead to no result with the activated filters.{/s}',
                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    xtype: 'numberfield',
                    minValue: 1,
                    name: 'filters[categoryFilterDepth]',
                    fieldLabel: '{s name=fieldset/filter/text/categoryFilterDepth}{/s}',
                    helpText:   '{s name=fieldset/filter/text/categoryFilterDepthHelp}{/s}'
                }]
            }
        ];
    }


});
//{/block}
