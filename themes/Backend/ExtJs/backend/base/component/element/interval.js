//{namespace name=backend/config/view/main}

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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */
Ext.define('Shopware.apps.Base.view.element.Interval', {
    extend:'Ext.form.field.ComboBox',
    alias:[
        'widget.base-element-interval'
    ],

    queryMode: 'local',
    forceSelection: false,
    editable: true,

    store: [
        [0, '{s name=element/interval/empty_value}None (0 Sec.){/s}'],
        [120, '{s name=element/interval/2_minutes}2 Minutes (120 Sec.){/s}'],
        [300, '{s name=element/interval/5_minutes}5 Minutes (300 Sec.){/s}'],
        [600, '{s name=element/interval/10_minutes}10 Minutes (600 Sec.){/s}'],
        [900, '{s name=element/interval/15_minutes}15 Minutes (900 Sec.){/s}'],
        [1800, '{s name=element/interval/30_minutes}30 Minutes (1800 Sec.){/s}'],
        [3600, '{s name=element/interval/1_hour}1 Hour (3600 Sec.){/s}'],
        [7200, '{s name=element/interval/2_hours}2 Hours (7200 Sec.){/s}'],
        [14400, '{s name=element/interval/4_hours}4 Hours (14400 Sec.){/s}'],
        [28800, '{s name=element/interval/8_hours}8 Hours (28800 Sec.){/s}'],
        [43200, '{s name=element/interval/12_hours}12 Hours (43200 Sec.){/s}'],
        [86400, '{s name=element/interval/1_day}1 Day (86400 Sec.){/s}'],
        [172800, '{s name=element/interval/2_days}2 Days (172800 Sec.){/s}'],
        [604800, '{s name=element/interval/1_week}1 Week (604800 Sec.){/s}']
    ],

    initComponent:function () {
        var me = this;

        me.callParent(arguments);
    }
});
