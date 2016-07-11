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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/emotion/view/components/youtube"}
//{namespace name=backend/emotion/view/components/youtube}
Ext.define('Shopware.apps.Emotion.view.components.Youtube', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-youtube',

    snippets: {
        video_id: '{s name=video_id}Youtube video id{/s}',
        video_hd: '{s name=video_hd}Use HD videos{/s}',
        video_autoplay: '{s name=video_autoplay}Autostart video{/s}',
        video_related: '{s name=video_related}Hide related{/s}',
        video_controls: '{s name=video_controls}Hide controls{/s}',
        video_start: '{s name=video_start}Start video at x-seconds{/s}',
        video_end: '{s name=video_end}End video after x-seconds{/s}',
        video_info: '{s name=video_info}Hide info{/s}',
        video_branding: '{s name=video_branding}Hide video branding{/s}',
        video_loop: {
            fieldLabel: '{s name=video_loop}Loop video{/s}',
            helpText: '{s name=video_loop_help}{/s}'
        }
    },

    initComponent: function() {
        var me = this,
            idField;

        me.callParent(arguments);

        idField = me.elementFieldset.down('field[name=video_id]');
        idField.getValue = function() {
            var val = this.rawToValue(this.processRawValue(this.getRawValue()));

            this.value = val.match('^https?://') ? me.extractVideoID(val) : val;
            return this.value;
        };
    },

    extractVideoID: function(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/,
            match = url.match(regExp);

        if (match && match[7].length == 11) {
            return match[7];
        }
        return url;
    }
});
//{/block}
