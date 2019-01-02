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
 * Overrides the Ext.button.Button to provide
 * an additional HTML5 data attribute to provide
 * a better adressing in selenium ui tests.
 */
//{block name="extjs/overrides/tab"}
Ext.override(Ext.tab.Tab, {

	/** Suffix fpr the data attribute */
	dataSuffix: 'card',

	afterRender: function() {
		var me = this;
        me.callOverridden(me);

        if(me.card && me.card.action) {
            var dom = me.getEl().dom.children[0].children[0];
            dom.setAttribute('data-' + me.dataSuffix, me.card.action);
        }
	}
});
//{/block}