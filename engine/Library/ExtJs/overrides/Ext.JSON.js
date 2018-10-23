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

(function () {
    var serverTimeZoneOffsetToUtcInSeconds = parseInt('{$serverTimeZoneOffsetToUtcInSeconds}', 10);
    var pad = function(number) {
        return number < 10 ? "0" + number : number;
    };

    Ext.JSON.encodeDate = function (date) {
        var dateShiftedByTimeZoneOffset = new Date(date.getTime());
        dateShiftedByTimeZoneOffset.setSeconds(dateShiftedByTimeZoneOffset.getSeconds() + serverTimeZoneOffsetToUtcInSeconds);

        return '"' + dateShiftedByTimeZoneOffset.getUTCFullYear() + "-"
            + pad(dateShiftedByTimeZoneOffset.getUTCMonth() + 1) + "-"
            + pad(dateShiftedByTimeZoneOffset.getUTCDate()) + "T"
            + pad(dateShiftedByTimeZoneOffset.getUTCHours()) + ":"
            + pad(dateShiftedByTimeZoneOffset.getUTCMinutes()) + ":"
            + pad(dateShiftedByTimeZoneOffset.getUTCSeconds()) + '"';
    }
})();
