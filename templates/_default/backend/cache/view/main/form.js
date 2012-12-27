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
 */

/**
 * todo@all: Documentation
 */

//{namespace name=backend/cache/view/main}

//{block name="backend/cache/view/main/form"}
Ext.define('Shopware.apps.Cache.view.main.Form', {

    extend: 'Ext.form.Panel',
    alias: 'widget.cache-form',

    title: '{s name=form/title}What areas are supposed to be cleared?{/s}',

    autoScroll: true,
    bodyPadding: 10,

    url: '{url action=clearCache}',
    waitMsg: '{s name=form/wait_message}Cache is cleared ...{/s}',
    waitMsgTarget: true,
    submitEmptyText: false,

    defaults: {
        xtype: 'checkbox',
        hideLabel:true
    },

    /**
     *
     */
    initComponent:function () {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems(),
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'shopware-ui',
                cls: 'shopware-toolbar',
                items: me.getButtons()
            }]
        });

        me.callParent(arguments);
    },

    /**
     * Apply url und wait message on submit
     * @param options
     */
    submit: function(options) {
        var me = this
            options = options || {};
        Ext.applyIf(options, {
            url: me.url,
            waitMsg: me.waitMsg
        });
        this.form.submit(options);
    },

    /**
     * @return array
     */
    getItems: function() {
        var me = this;
        return [{
            name: 'cache[config]',
            boxLabel: '{s name=form/items/config}Templates, settings, snippets, etc.{/s}'
        }, {
            name: 'cache[frontend]',
            boxLabel: '{s name=form/items/frontend}HttpProxy + Query-Cache (products, categories){/s}'
        }, {
            name: 'cache[backend]',
            boxLabel: '{s name=form/items/backend}Backend cache{/s}'
        }, {
            name: 'cache[router]',
            boxLabel: '{s name=form/items/router}SEO URL cache{/s}'
        }, {
            name: 'cache[search]',
            boxLabel: '{s name=form/items/search}Intelligent search (index / keywords){/s}'
        }, {
            name: 'cache[proxy]',
            boxLabel: '{s name=form/items/proxy}Proxy cache (For development purposes){/s}'
        }];
    },

    /**
     * @return array
     */
    getButtons: function() {
        var me = this;
        return ['->', {
            text: '{s name=form/buttons/select_all}Select all{/s}',
            action: 'select-all',
            cls: 'secondary'
        },{
            text: '{s name=form/buttons/submit}Clear{/s}',
            action: 'clear',
            cls: 'primary'
        }];
    }
});
//{/block}
