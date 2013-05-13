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
//{block name="backend/performance/view/tabs/settings/fields/customers"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Customers', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-customers',

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
                title: 'Information',
                items: [
                    me.createDecriptionContainer("Allgemeine Beschreibung für Crossselling<br>" +
                            "<br>" +
                            "<b>Wichtig: </b> Informationen"), ]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: 'Konfiguration "Kunden haben auch gekauft"',
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'alsoBought',
                        title: 'Index aufbauen'
                    },
                    {
                        fieldLabel: 'Anzeigen',
                        name: 'customer[alsoBoughtShow]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    }
                ]},
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: 'Konfiguration "Kunden haben auch gesehen"',
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'similarShown',
                        title: 'Index aufbauen'
                    },
                    {
                        fieldLabel: 'Aktivieren',
                        name: 'customer[similarActive]',
                        helpText: 'Soll diese Information erhoben werden?',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },                    {
                        fieldLabel: 'Anzeigen',
                        helpText: 'Das Anzeigen vermindert die Wirkung des HTTPCaches auf der Artikel-Detailseite',
                        name: 'customer[similarViewedShow]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: 'Gültigkeit',
                        supportText: '(in Tagen)',
                        name: 'customer[similarValidationTime]',
                        xtype: 'numberfield',
                        minValue: 1,
                        maxValue: 365
                    },
                    {
                        fieldLabel: 'Aktualisierungs-Strategie',
                        helpText: 'Wie soll aktualisiert werden?<br><br>' +
                                '<b>Manuell</b>: Berechnung wird manuell über dieses Modul angestoßen<br>' +
                                '<b>CronJob</b>: Berechnung wir düber einen CronJob angestoßen (optimal)<br>' +
                                '<b>Live</b>: Berechnung erfolgt im LiveBetrieb (schlecht für große Jobs)',
                        name: 'customer[similarRefreshStrategy]',
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
                    }
                ]
            }
        ];
    }


});
//{/block}
