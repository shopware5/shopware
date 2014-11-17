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
 * SEO fieldSet for
 */
//{block name="backend/performance/view/tabs/settings/fields/seo"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Seo', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend:'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-seo',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/seo/title}SEO{/s}',


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
                    me.createDescriptionContainer("{s name=fieldset/seo/info}Die SEO-Urls werden in bestimmten Abständen in Shopware aktualisiert. Sie können die Aktualisierung manuell starten oder aber zwischen der Aktualisierung im Live-Betrieb und der Aktualisierung via Cronjob wählen. <br><br>Sofern Sie viel Traffic haben, empfiehlt sich die Generierung der SEO-Routen über einen Cronjob durchführen zu lassen.{/s}")
                ]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'seo',
                        showEvent: 'showMultiRequestTasks',
                        title: '{s name=fieldset/seo/buildIndex}Rebuild seo url index{/s}'
                    },
                    {
                        fieldLabel: '{s name=fieldset/refreshStrategy}Refresh strategy{/s}',
                        helpText: '{s name=fieldset/refreshStrategy/help}How do you want to refresh this information?<br><br>' +
                                '<b>Manually</b>: Refresh by clicking the *build Index* button<br>' +
                                '<b>CronJob</b>: Refresh with a CronJob (recommended)<br>' +
                                '<b>Live</b>: Refresh in live operation (not recommended for large shops){/s}',
                        name: 'seo[seoRefreshStrategy]',
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
                                { id: 2, name: '{s name=fieldset/refreshStrategy/cronJob}Over cron job{/s}' },
                                { id: 3, name: '{s name=fieldset/refreshStrategy/live}Live{/s}' }
                            ]
                        })
                    },
                    {
                        fieldLabel: '{s name=fieldset/seo/routerCache}Router cache{/s}',
                        name: 'seo[routercache]',
                        xtype: 'numberfield',
                        minValue: 3600
                    } , {
                        fieldLabel: '{s name=fieldset/seo/lastUpdate}Last update{/s}',
                        name: 'seo[routerlastupdateDate]',
                        submitFormat: 'd.m.Y',
                        xtype: 'datefield'
                    } , {
                        fieldLabel: ' ',
                        labelSeparator: '',
                        name: 'seo[routerlastupdateTime]',
                        xtype: 'hidden'
                    }
                ]
            }
        ];
    }

});
//{/block}
