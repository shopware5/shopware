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

//{namespace name=backend/index/view/menu}

Ext.define('Shopware.notification.ExpiredLicence', {

    /**
     * Check if any plugins are expired
     */
    check: function() {
        var me = this;

        me.getExpiredLicences(function(data) {
            me.displayNotice(data);
        });
    },

    displayNotice: function(licences) {
        var text = (Ext.Object.getSize(licences) > 1) ? '{s name="licenses_expired_long"}{/s}:<br/>' : '{s name="license_expired_long"}{/s}:<br/>';

        Ext.each(licences, function(licence){
            var dateStr = Ext.util.Format.date(licence.expireDate);
            var snippet = '{s name="license_expired_line_text"}{/s}<br/>';
            text += Ext.String.format(snippet, licence.plugin, dateStr);
        });

        Shopware.Notification.createStickyGrowlMessage({
            title : (Ext.Object.getSize(licences) > 1) ? '{s name="licenses_expired"}{/s}' : '{s name="license_expired"}{/s}',
            text  : text,
            width : 440,
            height: 300
        });
    },

    getExpiredLicences: function(callback) {
        Ext.Ajax.request({
            url: '{url controller="base" action="getExpiredLicences"}',
            async: false,
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                if (Ext.isEmpty(responseData.data)) {
                    return;
                }

                if (responseData.success == true) {
                    callback(responseData.data);
                }
            }
        });
    }

});
