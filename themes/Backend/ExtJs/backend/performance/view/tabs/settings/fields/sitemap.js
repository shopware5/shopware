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
 * @package    Performance
 * @subpackage Sitemap
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Sitemap fieldset
 */
//{block name="backend/performance/view/tabs/settings/fields/sitemap"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Sitemap', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend: 'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-tabs-settings-sitemap',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/sitemap/title}Sitemap{/s}',

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

    /**
     * Creates the sitemap tab items
     *
     * @return { array }
     */
    getItems: function() {
        return [
            {
                xtype: 'fieldset',
                defaults: this.defaults,
                title: '{s name=fieldset/information}Information{/s}',
                items: [
                    this.createDescriptionContainer("{s name=fieldset/sitemap/info}{/s}")
                ]
            }, {
                xtype: 'fieldset',
                defaults: this.defaults,
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [
                    this.getRefreshButton(),
                    this.getRefreshStrategyField(),
                    this.getRefreshTimeField(),
                    this.getLastUpdateField()
                ]
            }
        ];
    },

    /**
     * @returns { Shopware.apps.Performance.view.tabs.settings.elements.MultiRequestButton }
     */
    getRefreshButton: function() {
        return {
            xtype: 'performance-multi-request-button',
            event: 'sitemap',
            title: '{s name=fieldset/sitemap/refresh}{/s}'
        };
    },

    /**
     * Creates refresh strategy field
     *
     * @return { Ext.form.field.ComboBox }
     */
    getRefreshStrategyField: function() {
        return {
            fieldLabel: '{s name=fieldset/refreshStrategy}Refresh strategy{/s}',
            helpText: '{s name=fieldset/refreshStrategy/help}How do you want to refresh this information?<br><br>' +
            '<b>Manually</b>: Refresh by clicking the *build Index* button<br>' +
            '<b>CronJob</b>: Refresh with a CronJob (recommended)<br>' +
            '<b>Live</b>: Refresh in live operation (not recommended for large shops){/s}',
            name: 'sitemap[sitemapRefreshStrategy]',
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
        };
    },

    /**
     * Creates refresh strategy time field
     *
     * @return { Ext.form.field.Text }
     */
    getRefreshTimeField: function () {
        return {
            fieldLabel: '{s name=fieldset/search/cache_time}Cache time{/s}',
            name: 'sitemap[sitemapRefreshTime]',
            xtype: 'textfield',
            minValue: 3600
        };
    },

    /**
     * Creates last update field
     *
     * @return { Ext.form.field.Display }
     */
    getLastUpdateField: function () {
        return {
            fieldLabel:  '{s name=fieldset/search/last_update}Last index update{/s}',
            name: 'sitemap[sitemapLastRefresh]',
            xtype: 'displayfield',
            renderer: function(value) {
                if (typeof value === Ext.undefined || value === 0 ) {
                    return value;
                }
                value = new Date(value * 1000);

                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
            }
        };
    }
});
//{/block}
