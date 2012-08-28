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
 * @package    Countries
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/countries/view/main}

/**
 *
 * todo@all: Documentation
 */
//{block name="backend/countries/view/main/properties"}
Ext.define('Shopware.apps.Countries.view.main.Properties', {
    extend: 'Ext.form.Panel',
    alias : 'widget.country-properties',
    bodyPadding : 5,
    height: 400,
    unstyled: true,
    autoScroll:true,

    /**
     * Initialize the view components
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.areaStore = Ext.create('Shopware.apps.Countries.store.Areas').load();
        me.items = [
           me.getMetaProperties(),
           me.getFlags()

        ];
        me.dockedItems= [{
            xtype: 'toolbar',
            dock: 'bottom',
            style: 'background: #F9FAFA',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items:  ['->', {
                text: '{s name="country_properties/save"}Save{/s}',
                action: 'save',
                   cls: 'primary',
                   handler: function() {
                       me.fireEvent('saveCountry', me.record, me);
                   }
            }]
        }];
        me.callParent(arguments);
    },
    getFlags: function(){
        return Ext.create('Ext.form.FieldSet',
        {
            title: 'Flags',
            bodyPadding : 10,
            defaults    : {
                labelWidth: '155px',
                labelStyle: 'font-weight: 700; text-align: right;'
            },
            items: [{
                  // Implementiert das Column Layout
                  xtype: 'container',
                  unstyled: true,
                  layout: 'column',
                  items: [
                  {
                       // Linke Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                               xtype: 'checkbox',
                               boxLabel: '{s name=country_properties/shippingfree}Free of shipping costs{/s}',
                               anchor: '100%',
                               name: 'shippingFree',
                               uncheckedValue:0,
                               inputValue:1,
                               supportText: '{s name=country_properties/shippingfree_info}If selected, all orders for this country are free of shipping costs.{/s}'
                           },
                           {
                             xtype: 'checkbox',
                             boxLabel: '{s name=country_properties/taxfree}Tax free{/s}',
                             anchor: '100%',
                             name: 'taxFree',
                             uncheckedValue:0,
                             inputValue:1,
                             supportText: '{s name=country_properties/taxfree_info}If selected, no tax will be charged for this country.{/s}'
                          }
                       ]
                  },
                  {
                       // Rechte Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                               xtype: 'checkbox',
                               boxLabel: '{s name=country_properties/taxfree_ustid}Tax free with valid VAT ID{/s}',
                               anchor: '100%',
                               name: 'taxFreeUstId',
                               uncheckedValue:0,
                               inputValue:1,
                               supportText: '{s name=country_properties/taxfree_ustid_info}Tax free if customer enters a valid VAT ID{/s}'
                           },
                           {
                             xtype: 'checkbox',
                             boxLabel: '{s name=country_properties/taxfree_ustid_checked}Tax free with valid VAT ID{/s}',
                             anchor: '100%',
                             name: 'taxFreeUstIdChecked',
                             uncheckedValue:0,
                             inputValue:1,
                             supportText: '{s name=country_properties/taxfree_ustid_validated_info}Tax free if customer enters a valid VAT ID.{/s}'
                          }
                       ]
                  }
                  ]
                 }]
        }
        );
    },
    getMetaProperties: function() {
            return Ext.create('Ext.form.FieldSet',
            {
                title: 'Metadata',
                bodyPadding : 10,
                defaults    : {
                    labelWidth: '155px',
                    labelStyle: 'font-weight: 700; text-align: right;'
                },
                items: [{
                      // Implementiert das Column Layout
                      xtype: 'container',
                      unstyled: true,
                      layout: 'column',
                      items: [
                      {
                           // Linke Spalte im Column Layout
                           xtype: 'container',
                           unstyled: true,
                           columnWidth: 0.5,
                           items: [
                               {
                                   xtype: 'hidden',
                                   anchor: '100%',
                                   name: 'id',
                                   allowBlank: false

                               },
                               {
                                  xtype:'combobox',
                                  triggerAction:'all',
                                  name:'areaId',
                                  fieldLabel: '{s name=country_properties/areaId}Assigned area{/s}',
                                  store: this.areaStore,
                                  valueField:'id',
                                  displayField:'name',
                                  queryMode: 'local',
                                  mode: 'local',
                                  required:true,
                                  editable:false,
                                  allowBlank:false,
                                  listConfig: {
                                    action: 'area'
                                  }

                               },
                               {
                                    xtype: 'textfield',
                                    fieldLabel: '{s name=country_properties/countryname}Name{/s}',
                                    anchor: '100%',
                                    name: 'name',
                                    allowBlank: false
                               },
                               {
                                  xtype: 'textfield',
                                  fieldLabel: '{s name=country_properties/international}International name{/s}',
                                  anchor: '100%',
                                  name: 'isoName',
                                  allowBlank: false
                               },
                               {
                                   xtype: 'checkbox',
                                   name: 'enabled',
                                   fieldLabel: '{s name=country_properties/enabled}Enabled{/s}',
                                   anchor: '100%',
                                   name: 'active',
                                   uncheckedValue:0,
                                   inputValue:1,
                                   boxLabel: '{s name=country_properties/enabled_info}Enable or disable this country{/s}'
                               }
                           ]
                      },
                      {
                           // Rechte Spalte im Column Layout
                           xtype: 'container',
                           unstyled: true,
                           columnWidth: 0.5,
                           items: [
                              {
                                   xtype: 'textfield',
                                   fieldLabel: '{s name=country_properties/countryiso}Iso-code{/s}',
                                   anchor: '100%',
                                   name: 'iso',
                                   allowBlank: false

                              },
                              {
                                   xtype: 'textfield',
                                   fieldLabel: '{s name=country_properties/countryiso3}Iso-code3{/s}',
                                   anchor: '100%',
                                   name: 'iso3',
                                   allowBlank: false

                              },

                              {
                                   xtype: 'textfield',
                                   fieldLabel: '{s name=country_properties/position}Pos. in select-fields{/s}',
                                   anchor: '100%',
                                   name: 'position',
                                   allowBlank: false
                              },
                              {
                                  xtype: 'textarea',
                                  fieldLabel: '{s name=country_properties/notice}Information displayed in the frontend{/s}',
                                  anchor: '100%',
                                  name: 'notice',
                                  allowBlank: true
                              }
                           ]
                      }
                      ]
                     }]
            }
            );

        }
});
//{/block}