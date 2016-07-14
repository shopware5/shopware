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
 * ""Shopware" is a registered trademark of shopware AG.
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
 * SEO fieldSet for
 */
//{block name="backend/performance/view/tabs/settings/fields/topseller"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Topseller', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-topseller',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/topseller/title}Topseller{/s}',

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
                    me.createDescriptionContainer("{s name=fieldset/topseller/info}Das Anzeigen der Topseller ist besonders performancelastig, da hierzu eine Vielzahl von Daten ausgewertet werden müssen. Sie sollten in großen Shop-Umngebungen daher den Topseller-Index via Cronjob aktualisieren lassen.{/s}")
                ]
            },
            {
                xtype: 'fieldset',
                title: '{s name=fieldset/topseller/config}Configuration \'Topseller\'{/s}',
                defaults: me.defaults,
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'topseller',
                        title: '{s name=fieldset/topseller/buildIndex}Index neu aufbauen{/s}'
                    },
                    {
                        fieldLabel: '{s name=fieldset/topseller/enable}Topseller im Shop anzeigen{/s}',
                        helpText: '{s name=fieldset/topseller/enable/help}Do you want to show TopSellers in your shop?{/s}',
                        name: 'topSeller[topSellerActive]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/topseller/valid}Topseller gültig für [n] Tage{/s}',
                        name: 'topSeller[topSellerValidationTime]',
                        xtype: 'numberfield',
                        minValue: 1,
                        maxValue: 365
                    },
                    {
                        fieldLabel: '{s name=fieldset/topseller/orders}Bestellungen der letzten [n] Tage berücksichtigen{/s}',
                        helpText: '{s name=fieldset/topseller/orders/help}How many days of orders should be calculated?{/s}',
                        name: 'topSeller[chartinterval]', // existing value
                        xtype: 'numberfield',
                        minValue: 10
                    },
                    {
                        fieldLabel: '{s name=fieldset/refreshStrategy}Refresh strategy{/s}',
                        helpText: '{s name=fieldset/refreshStrategy/help}How do you want to refresh this information?<br><br>' +
                                '<b>Manually</b>: Refresh by clicking the *build Index* button<br>' +
                                '<b>CronJob</b>: Refresh with a CronJob (recommended)<br>' +
                                '<b>Live</b>: Refresh in live operation (not recommended for large shops){/s}',
                        name: 'topSeller[topSellerRefreshStrategy]',
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
                                { id: 1, name: '{s name=fieldset/refreshStrategy/manual}Manually{/s}' },
                                { id: 2, name: '{s name=fieldset/refreshStrategy/cronJob}CronJob{/s}' },
                                { id: 3, name: '{s name=fieldset/refreshStrategy/live}Live{/s}' }
                            ]
                        })
                    },
                    {
                        fieldLabel: '{s name=fieldset/topseller/pseudoSales}Take pseudo sales into account{/s}',
                        name: 'topSeller[topSellerPseudoSales]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    }
                ]}
        ];
    }


});
//{/block}
