/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Mail
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/mail/view/info"}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/info"}
Ext.define('Shopware.apps.Mail.view.main.Info', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,
    title: '{s name="title"}Information on variables{/s}',
    autoScroll: true,
    width: 260,
    alias: 'widget.mail-main-info',

    /**
     * Init the main detail component.
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.tpl = me.getTemplate();

        me.callParent(arguments);
    },

    /**
     * Update template variables
     *
     * @param [object]
     * @return void
     */
    updateContext: function(context) {
        var me            = this,
            viewVariables = [];

        Ext.iterate(context, function(key, value) {
            viewVariables.push({
                /* {literal} */
                name: "{$" + key + "}",
                /* {/literal} */
                value: value
            });
        });

        me.update(viewVariables);
    },

    /**
     * Returns an array of strings.
     * @return array of string
     */
    getTemplate: function() {
        return [
            '{literal}',
            '<pre style="margin: 10px">',
            '<tpl for=".">',
            '<div>{name}: {value}</div>',
            '</tpl>',
            '</pre>',
            '{/literal}'
        ];
    }
});
//{/block}
