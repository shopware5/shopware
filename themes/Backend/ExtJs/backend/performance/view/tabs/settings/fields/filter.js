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
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [

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
                                { id: 3, name: '{s name=fieldset/filter/sort/position}Sort by position{/s}' }
                            ]
                        })
                    }
                ]
            }, {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/listings}Listings{/s}',
                items: [{
                    name: 'filters[showSupplierInCategories]',
                    fieldLabel: '{s name=fieldset/filter/text/showManufacturerFacet}Hersteller Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showManufacturerFacetHelp}Ermöglicht dem Kunden, die angezeigten Produkte nach Ihren Herstellern zu filtern{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showImmediateDeliveryFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showImmediateDeliveryFacet}Sofort lieferbar Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showImmediateDeliveryFacetHelp}Ermöglicht dem Kunden, nur Produkte anzuzeigen, die sofort lieferbar sind.{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showShippingFreeFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showShippingFreeFacet}Versandkostenfrei Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showShippingFreeFacetHelp}Ermöglicht dem Kunden, nur Produkte anzuzeigen, welche als Versandkostenfrei markiert wurden{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showPriceFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showPriceFacet}Preis Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showPriceFacetHelp}Ermöglicht dem Kunden, die angezeigten Produkte nach Ihren Preisen zu filtern{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[showVoteAverageFacet]',
                    fieldLabel: '{s name=fieldset/filter/text/showVoteAverageFacet}Bewertungs Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showVoteAverageFacetHelp}Ermöglicht dem Kunden, die angezeigten Produkte nach Ihren Durchschnitts-Bewertungen zu filtern{/s}',

                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }, {
                    name: 'filters[displayFiltersInListings]',
                    fieldLabel: '{s name=fieldset/filter/text/showPropertyFacet}Eigenschaften Filter anzeigen{/s}',
                    helpText:   '{s name=fieldset/filter/text/showPropertyFacetHelp}Ermöglicht dem Kunden, die angezeigten Produkte nach Ihren Eigenschaften zu filtern{/s}',
                    cls: 'property-facet',
                    xtype: 'checkbox',
                    uncheckedValue: false,
                    inputValue: true
                }]
            }
        ];
    }


});
//{/block}
