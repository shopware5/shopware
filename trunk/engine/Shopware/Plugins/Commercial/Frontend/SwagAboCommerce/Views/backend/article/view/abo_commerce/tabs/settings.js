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
 * @package    SwagAboCommerce
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name="backend/abo_commerce/article/view/main"}
Ext.define('Shopware.apps.Article.view.abo_commerce.tabs.Settings', {
    /**
     * The parent class that this class extends.
     */
    extend: 'Ext.form.Panel',

    title: 'Einstellungen',

    autoScroll: true,

    bodyPadding: 10,

    cls: 'shopware-form',

    layout: 'anchor',

    border: 0,

    defaults: {
        labelWidth: 155,
        labelStyle: 'font-weight: bold'
    },

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-tab-settings',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.items = me.createFormItems();

        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
                /**
                 * @Event
                 * Custom component event.
                 * Fired when the customer select a customer group in the toolbar combo box.
                 * @param Ext.data.Model The selected record
                 */
                'addPrice'
        );
    },

    createFormItems: function() {
        var me = this;
        var mainArticleOrdernumber = me.article.getMainDetail().first().get('number');
        var unitsStore = me.getUnitsComboStore();

        return [
            {
                xtype: 'container',
                html: 'In dieser Sektion finden Sie alle Artikelspezifischen Abo-Einstellungen. Alle globalen Einstellungen wie der Ausschluss von Zahlungsarten finden Sie im Abonnements-Modul unter den Menüpunkt "Marketing".',
                margin: '0 0 10',
                style: 'color: #999; font-style: italic'
            },
            {
                xtype: 'textfield',
                name: 'ordernumber',
                anchor: '100%',
                // todo@bc unique validator
                value: mainArticleOrdernumber + ".ABO",
                fieldLabel: 'Abo-Bestellnummer:'
            },
            {
                xtype: 'container',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 155,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    {
                        xtype: 'numberfield',
                        name: 'minDuration',
                        allowDecimals: false,
                        minValue: 0,
                        maxValue: 52,
                        allowBlank: false,
                        margins: '',
                        height: 22,
                        width: 447,
                        value: 2,
                        fieldLabel: 'Mindestlaufzeit:'
                    },
                    {
                        xtype: 'combobox',
                        name: 'durationUnit',
                        flex: 1,
                        margins: '0 0 0 10',
                        fieldLabel: '',
                        store: unitsStore,
                        editable: false,
                        displayField: 'label',
                        valueField: 'id'
                    }
                ]
            },
            {
                xtype: 'container',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 155,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    {
                        xtype: 'numberfield',
                        name: 'maxDuration',
                        allowDecimals: false,
                        minValue: 0,
                        maxValue: 52,
                        allowBlank: false,
                        margins: '',
                        width: 447,
                        value: 24,
                        fieldLabel: 'Maximale Laufzeit'
                    },
                    {
                        xtype: 'combobox',
                        flex: 1,
                        margins: '0 0 0 10',
                        fieldLabel: '',
                        store: unitsStore,
                        editable: false,
                        displayField: 'label',
                        valueField: 'id'
                    },
                    {
                        xtype: 'checkboxfield',
                        margin: '0 0 0 10',
                        fieldLabel: '',
                        boxLabel: 'Unbegrenzt'
                    }
                ]
            },
            {
                xtype: 'container',
                html: 'Verwenden Sie die Option "Unbegrenzt", damit der Kunde nach Ablauf der Mindestlaufzeit das Abo im Kundenkonto nach jederzeit fristgerecht stornieren kann.',
                style: 'color: #999; font-style: italic; font-size: 11px; margin: -6px 0 8px 157px;'
            },
            {
                xtype: 'container',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 155,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    {
                        xtype: 'displayfield',
                        width: 197,
                        value: 'alle',
                        fieldLabel: 'min. Lieferzyklus'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'minDeliveryInterval',
                        allowDecimals: false,
                        minValue: 0,
                        maxValue: 52,
                        allowBlank: false,
                        width: 250,
                        value: 2,
                        fieldLabel: ''
                    },
                    {
                        xtype: 'combobox',
                        name: 'deliveryIntervalUnit',
                        flex: 1,
                        margins: '0 0 0 10',
                        fieldLabel: '',
                        store: unitsStore,
                        editable: false,
                        displayField: 'label',
                        valueField: 'id'
                    }
                ]
            },
            {
                xtype: 'container',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 155,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    {
                        xtype: 'displayfield',
                        width: 197,
                        value: 'alle',
                        fieldLabel: 'max. Lieferzyklus'
                    },
                    {
                        xtype: 'numberfield',
                        name: 'maxDeliveryInterval',
                        allowDecimals: false,
                        minValue: 0,
                        maxValue: 52,
                        allowBlank: false,
                        width: 250,
                        value: 4,
                        fieldLabel: ''
                    },
                    {
                        xtype: 'combobox',
                        flex: 1,
                        margins: '0 0 0 10',
                        fieldLabel: '',
                        store: unitsStore,
                        editable: false,
                        displayField: 'label',
                        valueField: 'id'
                    }
                ]
            },
            {
                xtype: 'container',
                html: 'Die Lieferzyklen müssen definiert werden, da sich hiernach die Berechnung der prozentualen Rabatte richtet.',
                style: 'color: #999; font-style: italic; font-size: 11px; margin: -6px 0 8px 157px;'
            },
            {
                xtype: 'checkboxfield',
                name: 'limited',
                inputValue: 1,
                uncheckedValue: 0,
                anchor: '100%',
                margin: '0 0 8',
                fieldLabel: 'Limitiert',
                boxLabel: 'Lieferbarkeit des Spar-Abos ist auf eine gewisse Stückzahl pro mindest Lieferzyklus limitiert'
            },
            {
                xtype: 'numberfield',
                name: 'maxUnitsPerWeek',
                anchor: '100%',
                value: 50,
                fieldLabel: 'Verfügbare Artikel pro Woche'
            },
            {
                xtype: 'textareafield',
                name: 'description',
                anchor: '100%',
                fieldLabel: 'Hinweis-Text auf Detailseite'
            }
        ];
    },

    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getUnitsComboStore: function() {
        var me = this;

        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['weeks', 'Weeks'],
                ['months', 'Months']
            ]
        });
    }
});
