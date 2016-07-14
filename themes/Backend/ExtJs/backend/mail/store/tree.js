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
 * @package    Mail
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/store/tree"}
Ext.define('Shopware.apps.Mail.store.Tree', {
    extend: 'Ext.data.TreeStore',
    batch: true,
    clearOnLoad: false,
    model : 'Shopware.apps.Mail.model.Mail',

    toRestore: [],

    myFilter: function(string) {
        var me = this;

        me.restoreNodes();

        Ext.Object.each(me.tree.nodeHash, function(key, node) {
            if (!node.isLeaf()) {
                return true;
            }

            if (node.data.name.toLowerCase().indexOf(string.toLowerCase()) < 0) {
                me.toRestore.push({ node: node, parent: node.parentNode });
                node.remove();
            }
        });

        me.hasFilter = true;
    },

    restoreNodes: function () {
        var me = this;

        Ext.Array.each(me.toRestore, function(object) {
            var parent = object.parent,
                child = object.node;

            parent.appendChild(child);
        });
    }
});
//{/block}
