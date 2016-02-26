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

//{block name="backend/emotion/view/components/html_video"}
//{namespace name=backend/emotion/view/components/html_video}
Ext.define('Shopware.apps.Emotion.view.components.HtmlVideo', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-html-video',

    snippets: {
        'videoMode': {
            fieldLabel: '{s name=videoMode/label}Video Mode{/s}',
            supportText: '{s name=videoMode/support}Determine the behavior of the video. Determine whether the video should scale, fill or stretch the element.{/s}'
        },
        'webm_video': {
            fieldLabel: '{s name=webm_video/label}.webm video{/s}',
            supportText: '{s name=webm_video/support}Video for browsers with WebM support. External path also allowed.{/s}'
        },
        'ogg_video': {
            fieldLabel: '{s name=ogg_video/label}.ogv video{/s}',
            supportText: '{s name=ogg_video/support}Video for browsers with Ogg support. External path also allowed.{/s}'
        },
        'h264_video': {
            fieldLabel: '{s name=h264_video/label}.mp4 video{/s}',
            supportText: '{s name=h264_video/support}Video for browsers with MP4 support. External path also allowed.{/s}'
        },
        'fallback_picture': {
            fieldLabel: '{s name=fallback_picture/label}Preview image{/s}',
            supportText: '{s name=fallback_picture/support}The image which is shown before the video starts.{/s}'
        },
        'html_text': {
            fieldLabel: '{s name=html_text/label}Overlay text{/s}',
            supportText: '{s name=html_text/support}Text to be displayed in an overlay on the top of the video.{/s}'
        },
        'autoplay': {
            fieldLabel: '{s name=autoplay/label}Automatically play video{/s}'
        },
        'autobuffer': {
            fieldLabel: '{s name=autobuffer/label}Automatically load video{/s}'
        },
        'controls': {
            fieldLabel: '{s name=controls/label}Display video control{/s}',
            supportText: '{s name=controls/support}Will only be shown on normal scale mode. On the other modes small buttons are shown at the top.{/s}'
        },
        'loop': {
            fieldLabel: '{s name=loop/label}Loop video{/s}',
            supportText: '{s name=loop/support}The video will be displayed in a continuous loop.{/s}'
        },
        'muted': {
            fieldLabel: '{s name=muted/label}Mute video{/s}',
            supportText: '{s name=muted/support}The audio track of the video is muted.{/s}'
        },
        'scale': {
            fieldLabel: '{s name=scale/label}Zoom factor{/s}',
            supportText: '{s name=scale/support}Zoom factor to apply when using the \'fill\' mode.{/s}'
        },
        'overlay': {
            fieldLabel: '{s name=overlay/label}Overlay color{/s}',
            supportText: '{s name=overlay/support}Set a background color for the overlay. A RGBA value is recommended.{/s}'
        },
        'originTop': {
            fieldLabel: '{s name=originTop/label}Top scale origin{/s}',
            supportText: '{s name=originTop/support}Sets the top origin for the scaling of the video. The value is given in percentage.{/s}'
        },
        'originLeft': {
            fieldLabel: '{s name=originLeft/label}Left scale origin{/s}',
            supportText: '{s name=originLeft/support}Sets the left origin for the scaling of the video. The value is given in percentage.{/s}'
        }
}
});
//{/block}
