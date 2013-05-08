/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @package    Shopware_Performance
 * @subpackage Cache
 * @copyright  Copyright (c) 2013, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * The multi request dialog controller takes care of actual requests
 */

//{namespace name=backend/performance/main}
//{block name="backend/performance/controller/multi_request"}
Ext.define('Shopware.apps.Performance.controller.MultiRequest', {

    extend: 'Enlight.app.Controller',

    /**
     * Indicates if the operations should be canceled after the next request
     */
    cancelOperation: false,

    requestConfig: {
        topseller:  {
            title: 'Initialisiere TopSeller',
            totalCountUrl: '{url controller="TopSeller" action="getTopSellerCount"}',
            requestUrl: '{url controller="TopSeller" action="initTopSeller"}'
        }
    },

    init: function () {
        var me = this;

        me.control({
            'performance-tabs-settings-base': {
                'showMultiRequestDialog': me.onShowMultiRequestDialog
            },
            'performance-main-multi-request-dialog': {
                'multiRequestDialogCancelProcess': me.onCancelMultiRequestDialog
            }
       });

        me.callParent(arguments);
    },

    /**
     * Cancel the current process
     */
    onCancelMultiRequestDialog: function() {
        var me = this;

        me.cancelOperation = true;
    },

    /**
     *
     * @param type The actual dialog type (topseller, seo…)
     * @param fieldSet
     */
    onShowMultiRequestDialog: function(type, fieldSet) {
        var me = this,
            config = me.requestConfig[type];

        var window = me.getView('main.MultiRequestDialog').create({
            title: config.title
        }).show();


        Ext.Ajax.request({
            url: config.totalCountUrl,
            success: function(response) {
                var json = Ext.decode(response.responseText);
                config.totalCount = json.total;

                window.progressBar.updateProgress(0);

                window.startButton.enable();
            }
        });
    }




});
//{/block}
