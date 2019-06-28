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
//{block name="backend/emotion/view/detail/elements/banner_slider"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.BannerSlider', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-banner-slider',

    componentCls: 'banner-slider-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAfCAYAAACPvW/2AAACEElEQVRYhd2XP2gUQRSHv9O9QsWYC9onNnbmsFAI2kkQbMTiSIrdwmKEgIgMxC74pxCEQQsbH6l2ilgKNkYE06VIc9dYWYRgn6CkVYvMHpvl3J095nDxV+2+eW/mY+a9t7MtSmTE/i4bH1daxa2/jUUe8Y+AfiCWLvCqzMEHqK9VvBWCxoit9DkRYqGQ8tmhYzKSXgW0i32jVfIlJFCtHTKSzgJbQA+4C2waSef/GRBwCziVe28Dt8Ph1AfaG2H7HgIkU12gj0C+VD4AG+Fwaia1VskvIDGSrgGRVsm3kDC1gTJplewG5hiqcX2ocUDjNMY2cBk4A+wVj89IehN4ANxwph3gsVaJ1/fQG8hI2gLuA8+B8zn7T+ArsA9cAuYKoYvAFeBCUCCOSnxUEzwLXKuIbfsuUieHrtfwHVuNS+rGAXnnkFbJ9CRBMvkAdX1uep7qVjn4AJXegf8bGbEj/zyigtNpYB1Y0So+mDDTOSP2LXBPq/gwMw6rzIi9CGwDyxMGyasHbLu1AWg5mEXgHdBx9nngR8lEB1U7aMTOAFMlLlPAwD3vAz2t4s+REbsKvOB4TxoUowt6Cjyp8FkDHlb4ZOoAm0bsatMa48lIq/ilEdvn6G484wYqj8xj8mfA65Lx4pEtaRV/GpaeETsLvHcwnUlXmRE77UAGwB2t4l3I5Y0zLBD4L6JCG8BCBgPwB6BfgVu8f9cIAAAAAElFTkSuQmCC',

    createPreview: function() {
        var me = this,
            preview = '',
            image = me.getConfigValue('banner_slider');

        if (Ext.isDefined(image) && image[0]) {
            preview = '<div class="x-emotion-banner-element-preview" style="background-image: url(' + image[0].path + ');"></div>';
        }

        return preview;
    }
});
//{/block}
