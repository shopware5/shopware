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
                items: [
                    me.createDecriptionContainer("Allgemeine Beschreibung für das Topseller-Modul <br>" +
                            "<br>" +
                            "<b>Wichtig: </b> Informationen"),
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'topseller',
                        title: 'Index aufbauen für "TopSeller"',
                    }],
            },
            {
                fieldLabel: 'Aktivieren',
                helpText: 'Sollen TopSeller im Frontend angezeigt werden?',
                name: 'topSeller[topSellerActive]',
                xtype: 'checkbox',
                uncheckedValue: false,
                inputValue: true
            },
            {
                fieldLabel: 'Gültigkeit',
                supportText: '(in Tagen)',
                name: 'topSeller[topSellerValidationTime]',
                xtype: 'numberfield',
                minValue: 1,
                maxValue: 365
            },
            {
                fieldLabel: 'Bestellungen',
                supportText: '(in Tagen)',
                helpText: 'Wie viele Tage sollen bei der TopSeller-Berechnung berücksichtigt werden.',
                name: 'topSeller[chartinterval]', // existing value
                xtype: 'numberfield',
                minValue: 10
            },
            {
                fieldLabel: 'Aktualisierungs-Strategie',
                helpText: 'Wie soll aktualisiert werden?<br><br>' +
                        '<b>Manuell</b>: Berechnung wird manuell über dieses Modul angestoßen<br>' +
                        '<b>CronJob</b>: Berechnung wir düber einen CronJob angestoßen (optimal)<br>' +
                        '<b>Live</b>: Berechnung erfolgt im LiveBetrieb (schlecht für große Jobs)',
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
                        { id: 1, name: 'Manuell' },
                        { id: 2, name: 'CronJob' },
                        { id: 3, name: 'Live' }
                    ]
                })
            },
            {
                fieldLabel: 'Pseudosales berücksichtigen',
                name: 'topSeller[topSellerPseudoSales]',
                xtype: 'checkbox',
                uncheckedValue: false,
                inputValue: true
            }
        ];
    }


});
//{/block}
