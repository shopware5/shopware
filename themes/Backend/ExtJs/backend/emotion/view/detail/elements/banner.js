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
//{block name="backend/emotion/view/detail/elements/banner"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Banner', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-banner',

    componentCls: 'banner-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAABm0lEQVRYhe3YvYsTQRiA8V/OKAo2Wlp5WgmCKAjaiWKrvR8LFi5YWMg2WoggBxY6vUw56fwDbCwUS69TQS1VECv1SkXR4nISj2wmOZPsFnlgYWffYeZhhnfeZToh9n5rEUtNC2ymO/C+H2sNecBX/hVaq8rL3xqSEWIPLdyyhVCObq5DiGkJN3ANy3iPiPtVWfyauxAe4upAexn3cBiXpi00cstCTMc2yQxyMcR0cq5COJOJn52WyAY5oc60J8yRE3qaiT+bksdfRgpVZbGKXk34UVUWz+cq1OcKbuNTv/0Zd80gwxgj7ftnzQpWQkzdqix+zkJkg4lO6lnL0MLSsRDK0TqhcYrrlgkxHcJ564V4Nz7gCR7X/SmMJRRiOohT2Ifv+IiXeDcs80JMJ3AL54YMdx2vQ0wXqrJ4NZFQiOkoHuB0TZcfIaY3eIsv2IvjODBqXOsr9gK7xhIKMW3HHdzEthED78CR/jMpO4d9rFuh1S1O8t/UZVkjMrQw7RdCOVonVJdle+ZqMcBQoaosGrt0aN2WdRY3aBn+ALI1VmCKFXhZAAAAAElFTkSuQmCC',

    createPreview: function() {
        var me = this,
            preview = '',
            image = me.getConfigValue('file'),
            position = me.getConfigValue('bannerPosition') || 'center center',
            style;

        if (Ext.isDefined(image)) {
            style = Ext.String.format('background-image: url([0]); background-position: [1]; background-size: contain', image, position);

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);
        }

        return preview;
    }
});
//{/block}
