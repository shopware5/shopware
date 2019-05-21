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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/elements/youtube"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Youtube', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-youtube',

    componentCls: 'youtube-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAXCAYAAABj7u2bAAABXUlEQVRIic2WsUrEQBRFz6qgYqE/ICw2Fgo2NhZb+BNWMlgNdhZTibWdg4WNTLeDlYWCsI2F/oQf4B+sW4hayFqY4mUckkw2wdzqETJzD+/dTKZnnZ/SIc39N0CoBVH3gUlWrwKvLXtLP4BxCDQxWr0BWOdbZsn7Sc/OjawnQv0CfGf1PLDdsrf0A9iB/MhSAD6A5RmBon4S6BB4z+oV4KZgs1vgDrgENmoCST+A+xBoJEK9Vrab0erBOv8IGOCM9I6NGg+10erTaHUObPLbtZklQ30NfGX1InBcsG5otDoKH1rn94ErYKuCt/QDOIH8yIoAKslo9Wyd3wWegL2S16N+EiilQ1E13aHTINSVgazz68AFcFB1jfTL9vgDlCzr/BL1v7KoZKhTzqEhLZ1DvZr3oSZO6qjkyFL+ZU3AlP7LBkGoxw2YFmkQhHoKHb9+9OnYjbFtgFBRv86N7AcbfH15kazd6AAAAABJRU5ErkJggg==',

    createPreview: function() {
        var me = this,
            preview = '',
            style = '',
            teaserImage = '',
            videoID = me.getConfigValue('video_id');

        if (Ext.isDefined(videoID)) {
            teaserImage = Ext.String.format('https://img.youtube.com/vi/[0]/0.jpg', videoID);
            style = Ext.String.format('background-image: url([0]);', teaserImage);

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);
        }

        return preview;
    }
});
//{/block}
