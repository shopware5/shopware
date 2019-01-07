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
 */

//{namespace name=backend/performance/main}

/**
 * SEO fieldSet for
 */
//{block name="backend/performance/view/tabs/settings/fields/http_cache"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.HttpCache', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend: 'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-tabs-settings-http-cache',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/http_cache/title}HTTP Cache{/s}',

    layout: 'anchor',

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
                    me.createDescriptionContainer("{s name=fieldset/cache/info}{/s}")
                ]},
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/http_cache_warmer/title}Cache Warmer{/s}',
                items: [
                    {
                        xtype: 'performance-multi-request-button',
                        event: 'httpCacheWarmer',
                        showEvent: 'showMultiRequestTasks',
                        title: '{s name=button/title/http_cache/warmUp}Warm up http cache{/s}'
                    },
                    me.createDescriptionContainer("{s name=fieldset/cache_warmer/info}{/s}")
                ]},
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/configuration}Configuration{/s}',
                items: [
                    {
                        fieldLabel: '{s name=fieldset/http/enabled}Enable{/s}',
                        name: 'httpCache[enabled]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/http/ban}Enable Proxy BAN{/s}',
                        name: 'httpCache[HttpCache:proxyPrune]',
                        xtype: 'checkbox',
                        helpText: '{s name=fieldset/http/ban/help}{/s}',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/http/url}Alternate proxy URL{/s}',
                        name: 'httpCache[HttpCache:proxy]',
                        helpText: '{s name=fieldset/http/url/help}{/s}',
                        xtype: 'textfield'
                    },
                    {
                        fieldLabel: '{s name=fieldset/http/admin}Admin view{/s}',
                        name: 'httpCache[HttpCache:admin]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true,
                        helpText: '{s name=fieldset/http/admin/help}{/s}',
                        margin: '0 0 20 0'
                    },
                    {
                        xtype: 'performance-tabs-settings-elements-cache-time',
                        height: 250,
                        margin: '0 0 20 0'
                    },
                    {
                        xtype: 'performance-tabs-settings-elements-no-cache',
                        height: 250
                    }
                ]}
        ];
    }
});
//{/block}
