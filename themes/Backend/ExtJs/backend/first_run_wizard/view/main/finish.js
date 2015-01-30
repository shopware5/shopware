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

/**
 * Shopware First Run Wizard - Finish tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/finish"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Finish', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-finish',

    /**
     * Name attribute used to generate event names
     */
    name:'finish',

    snippets: {
        content: {
            title: '{s name=finish/content/title}Finished{/s}',
            message: '{s name=finish/content/message}The First Run Wizard is now complete and you are ready to start using your new Shopware shop. Information, help, and the latest Shopware news can be found in the following pages:<br><br><a href="http://en.wiki.shopware.com/" target="_blank">Shopware Wiki</a><br><a href="http://store.shopware.com/en/" target="_blank">Community Store</a><br><a href="http://en.forum.shopware.com/" target="_blank">Community Forum</a><br><a href="https://www.facebook.com/shopware" target="_blank">Shopware on Facebook</a><br><a href="https://twitter.com/shopware_ag" target="_blank">Shopware on Twitter</a><br>{/s}'
        },
        buttons: {
            finish: '{s name=finish/buttons/finish}Finish{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.html =
            '<h1>' + me.snippets.content.title + '</h1>' +
            '<p>' + me.snippets.content.message + '</p>';

        me.callParent(arguments);
    },

    getButtons: function() {
        var me = this;

        return { next: { text: me.snippets.buttons.finish }};
    }
});

//{/block}
