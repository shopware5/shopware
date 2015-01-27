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

//{block name="backend/performance/view/tabs/cache/form"}
Ext.define('Shopware.apps.Performance.view.tabs.cache.Form', {

    extend: 'Ext.form.Panel',
    alias: 'widget.performance-tabs-cache-form',

    title: '{s name=form/title}What areas are supposed to be cleared?{/s}',

    autoScroll: true,
    bodyPadding: 10,

    url: '{url controller=Cache action=clearCache}',
    waitMsg: '{s name=form/wait_message}Cache is clearing ...{/s}',
    waitMsgTarget: true,
    submitEmptyText: false,

    layout: 'column',

    /**
     * Init the component, load items
     */
    initComponent:function () {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    /**
     * Apply url und wait message on submit
     * @param options
     */
    submit: function(options) {
        var me = this;
            options = options || {};
        Ext.applyIf(options, {
            url: me.url,
            waitMsg: me.waitMsg
        });
        this.form.submit(options);
    },

    /**
     * @return Array
     */
    getItems: function() {
        var me = this;
        return [
            { xtype: 'container',
                columnWidth: '0.5',
                defaults: {
                    labelWidth: 155,
                    anchor: '100%',
                    xtype: 'checkbox',
                    margin: '10 0',
                    hideLabel: true
                },
                padding: '0 20 0 0',
                layout: 'anchor',
                items: [
                    {
                        name: 'cache[config]',
                        boxLabel: '{s name=form/items/config}Shopware configuration{/s}',
                        supportText: '{s name=form/items/config/support}Cache for settings and snippets etc.{/s}'
                    },
                    {
                        name: 'cache[template]',
                        boxLabel: '{s name=form/items/frontend/template}Template cache{/s}',
                        supportText: '{s name=form/items/frontend/template/support}Cache for compiled template files{/s}'
                    },
                    {
                        name: 'cache[theme]',
                        boxLabel: '{s name=form/items/frontend/theme}Theme cache{/s}',
                        supportText: '{s name=form/items/frontend/theme/support}Cache for compiled theme files{/s}'
                    },
                    {
                        name: 'cache[http]',
                        boxLabel: '{s name=form/items/backend}Http-Proxy-Cache{/s}',
                        supportText: '{s name=form/items/backend/support}Cache for the Http-Reverse-Proxy, if active{/s}'
                    }
                ] },
            { xtype: 'container',
                columnWidth: '0.5',
                defaults: {
                    labelWidth: 155,
                    anchor: '100%',
                    xtype: 'checkbox',
                    margin: '10 0',
                    hideLabel: true
                },
                padding: '0 20 0 0',
                layout: 'anchor',
                items: [
                    {
                        name: 'cache[proxy]',
                        boxLabel: '{s name=form/items/proxy}Doctrine Annotations and Proxies{/s}',
                        supportText: '{s name=form/items/proxy/support}Cache for proxy objects{/s}'
                    },
                    {
                        name: 'cache[search]',
                        boxLabel: '{s name=form/items/search}Cache search function{/s}',
                        supportText: '{s name=form/items/search/support}Cache for search results and index{/s}'
                    },
                    {
                        name: 'cache[router]',
                        boxLabel: '{s name=form/items/router}Index SEO-URLs{/s}',
                        supportText: '{s name=form/items/router/support}Cache for SEO-Routes and index{/s}'
                    }
                ]}
        ];
    }
});
//{/block}
