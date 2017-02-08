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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/mail/view/navigation}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/navigation"}
Ext.define('Shopware.apps.Mail.view.main.Navigation', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.mail-main-navigation',
    rootVisible: false,
    title: '{s name=title}Templates{/s}',
    collapsed: false,
    collapsible: true,
    width: 240,
    expanded: true,
    useArrows: true,
    displayField: 'name',

    /**
     * Configure the root node of the tree panel. This is necessary
     * due to the fact that the ExtJS 4.0.7 build fires the load
     * event to often if no root node is configured.
     *
     * @object
     */
    root: {
        text: 'Mail',
        expanded: true
    }
});
//{/block}
