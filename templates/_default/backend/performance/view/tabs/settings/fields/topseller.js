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
    title: '{s name=tabs/settings/topseller/title}Topseller{/s}',

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

//        - Init Button
//    - Aktivieren / Deaktivieren
//    - Anzahl Tage bis Invalide

//    - Anzahl Tage von Bestellungen die berücksichtigt werden sollen
//    - Drop Down welche Strategy  (Manuell, CronJob, Live)
//    - Pseudo Sales berücksichtigen (ja / nein)
    getItems: function() {
        var me = this;

        return [
            me.createDecriptionContainer("Allgemeine Beschreibung für das Topseller-Modul <br>" +
            "<br>" +
            "<b>Wichtig: </b> Informationen"),
        {
            fieldLabel: 'Aktivieren',
            name: 'topseller[active]',
            xtype: 'checkbox',
            uncheckedValue: false,
            inputValue:true
        }, {
            fieldLabel: 'Gültigkeit (in Tagen)',
            name: 'topseller[days]',
            xtype: 'numberfield',
            minValue: 1,
            maxValue: 365
        }, {
            fieldLabel: 'Berücksichtigte Bestellungen',
            helpText: 'Anzahl der Bestellungen, die für die Auswertung berücksichtigt werden.',
            name: 'topseller[orders]',
            xtype: 'numberfield',
            minValue: 10
        },{
            fieldLabel: 'Strategie',
            helpText: 'Wie sollen die TopSeller berechnet werden?.<br>' +
                    'Manuell: Berechnung wird manuell über dieses Modul angestoßen<br>' +
                    'CronJob: Berechnung wir düber einen CronJob angestoßen (optimal)<br>' +
                    'Live: Berechnung erfolgt im LiveBetrieb (schlecht für große Jobs)',
            name: 'topseller[strategy]',
            xtype: 'combo'

        }];
    }


});
//{/block}
