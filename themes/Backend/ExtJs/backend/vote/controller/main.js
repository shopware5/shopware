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

//{block name="backend/vote/controller/main"}
Ext.define('Shopware.apps.Vote.controller.Main', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;

        if (typeof me.subApplication.params === 'object' && me.subApplication.params.voteId) {
            var store = Ext.create('Shopware.apps.Vote.store.Vote', {
                filters: [{
                    property: 'id',
                    value: me.subApplication.params.voteId
                }]
            });

            store.on('load', function () {
                var record = store.getAt(0);
                if (record === null) {
                    return;
                }

                me.mainWindow = me.getView('detail.Window').create({
                    record: record
                }).show();
            });
            store.load();

            return;
        }

        me.mainWindow = me.getView('list.Window').create({ }).show();
    }
});
//{/block}
