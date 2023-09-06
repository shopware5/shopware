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
 * @package    Vote
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/vote/main"}

//{block name="backend/vote/view/list/window"}
Ext.define('Shopware.apps.Vote.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.vote-list-window',
    height: '90%',
    width: '80%',
    title: '{s name="window_title"}Vote listing{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.Vote.view.list.Vote',
            listingStore: 'Shopware.apps.Vote.store.Vote',

            extensions: [
                { xtype: 'vote-listing-info-panel' },
                { xtype: 'vote-listing-filter-panel' }
            ]
        };
    }
});
//{/block}
