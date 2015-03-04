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
            supportText: '{s name=videoMode/support}Determine the behavior of the video. Determine whether the video should be shown scaling, filling or stretched.{/s}'
        },
        'webm_video': {
            fieldLabel: '{s name=webm_video/label}WebM Video{/s}',
            supportText: '{s name=webm_video/support}Video for Google Chrome{/s}'
        },
        'ogg_video': {
            fieldLabel: '{s name=ogg_video/label}Ogg Theora Video{/s}',
            supportText: '{s name=ogg_video/support}Video for Firefox{/s}'
        },
        'h264_video': {
            fieldLabel: '{s name=h264_video/label}H264 Video{/s}',
            supportText: '{s name=h264_video/support}H.264 Video for Safari{/s}'
        },
        'fallback_picture': {
            fieldLabel: '{s name=fallback_picture/label}Fallback image{/s}',
            supportText: '{s name=fallback_picture/support}Fallback image for when the video is loading{/s}'
        },
        'html_text': {
            fieldLabel: '{s name=html_text/label}Text{/s}',
            supportText: '{s name=html_text/support}Text to be displayed with the video.{/s}'
        },
        'autoplay': {
            fieldLabel: '{s name=autoplay/label}Automatically play video{/s}'
        },
        'autobuffer': {
            fieldLabel: '{s name=autobuffer/label}Automatically load video{/s}'
        },
        'controls': {
            fieldLabel: '{s name=controls/label}Display video control{/s}',
            supportText: '{s name=controls/support}Not recommended for filling or stretching mode.{/s}'
        },
        'loop': {
            fieldLabel: '{s name=loop/label}Loop video{/s}',
            supportText: '{s name=loop/support}The video will be displayed in a continuous loop{/s}'
        },
        'muted': {
            fieldLabel: '{s name=muted/label}Video stumm schalten{/s}',
            supportText: '{s name=muted/support}Die Ton-Spur des Videos wird stumm geschaltet{/s}'
        },
        'scale': {
            fieldLabel: '{s name=scale/label}Zoom-Faktor{/s}',
            supportText: '{s name=scale/support}Wenn Sie den Modus Füllen gewählt haben können Sie den Zoom-Faktor mit dieser Option ändern.{/s}'
        },
        'overlay': {
            fieldLabel: '{s name=overlay/label}Overlay color{/s}',
            supportText: '{s name=overlay/support}Set a background color for the overlay. A RGBA value is recommended.{/s}'
        },
        'originTop': {
            fieldLabel: '{s name=originTop/label}Top starting point{/s}',
            supportText: '{s name=originTop/support}Sets the top starting point for the scaling of the video. The value is given in percentage.{/s}'
        },
        'originLeft': {
            fieldLabel: '{s name=originLeft/label}Left starting point{/s}',
            supportText: '{s name=originLeft/support}Sets the left starting point for the scaling of the video. The value is given in percentage.{/s}'
        }
}
});
//{/block}
