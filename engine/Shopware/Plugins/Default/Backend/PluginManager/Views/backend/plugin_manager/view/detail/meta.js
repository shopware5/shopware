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
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/detail/meta"}
Ext.define('Shopware.apps.PluginManager.view.detail.Meta', {
    extend: 'Ext.container.Container',

    cls: 'store-plugin-detail-meta-data',
    defaults: {
        xtype: 'component',
        cls: 'item',
        height: 40
    },

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this, items = [], commentCount = 0;

        if (me.plugin['getCommentsStore']) {
            commentCount = me.plugin['getCommentsStore'].getCount();
        }

        items.push({
            html: '<div class="label">{s name="version"}Version{/s}:</div>' +
            '<div class="value">'+ me.plugin.get('version') +'</div>'
        });

        items.push({
            html: '<div class="label">{s name="rating_short"}Rating{/s}:</div>' +
                '<div class="value">'+
                    '<div class="store-plugin-rating star' + me.plugin.get('rating') + '">('+commentCount+')</div>' +
                '</div>'
        });

        if (me.plugin['getLicenceStore']) {

            try {
                var licence = me.plugin['getLicenceStore'].first();

                var price = licence['getPriceStore'].first();

                var type = me.getTextForPriceType(price.get('type'));

                var expiration = licence.get('expirationDate');

                var result = type;

                if (expiration) {
                    var date = me.formatDate(expiration.date);
                    result += '<span class="date"> ({s name="till"}until{/s}: '+ Ext.util.Format.date(date) + ')</span>';
                }

                items.push({
                    html: '<div class="label">{s name="licence"}License{/s}:</div><div class="value">'+result+'</div>'
                });
            } catch (e) {

            }
        }

        me.items = items;

        me.callParent(arguments);
    }
});
//{/block}