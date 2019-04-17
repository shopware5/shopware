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
//{block name="backend/emotion/view/detail/elements/html_video"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.HtmlVideo', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-html-video',

    componentCls: 'emotion--element-video',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAADDElEQVRYhe3XS4gcVRQG4G+SGRnGZ0AYcBeQBCIYUUGJCw0MhrhQRHAhVkcJFIgbh1pERCagEB9wI4LZ3CzCdLlxJQjG1yzcuBCCGBIExSBEfIwgGiUiPkgWVS13iunqbjuNI+SHpg+3zjn/z6lzb90zFWL3gg2ETf+1gCamE3tLkXd+hhC71+GnCXP/w1dzXmADVmjDCZpKmvoX9OwpXDNh7pQPrmVtD40iYBXXY/MYgtblS1/ZTmytfzsHJHsXN+P9MQSlfFt7i2mFzjZ2WSuKvPMZ9oTYvR8BN44o6Gxjl+ESNHWRd97CTTiAX8fNlzb1R/irtqdxV0vccpF3HmsuhtidxyE8rtoYbUj54O4ecQ9tAoZCkXdWQ+weUvXHbQPc1+VLBT2K87V9JV4fRUyI3avxLJ7CFUOEpHzwZlPQ26M0dSJkEzp4EfPDxqV8dR5NQSMjxO4uvIrbx8mTIm3q4/iztmdwX0vce/gRj4zBnfLBA6ytUJuAJvaMIaSVLxX0JH6r7TkcuQSkbUj54BhrX9nl+9B6SCv0En6v7VnVp2CSSPngIGt7aNICmliXb1p1jlzG/waDrghDI8TyQezDE0Weffdv84y87UMsZ0IsZ9d5tA+78H3tNxtiOTNRQSGWe3EKS431zdiNlSLPesfIEk7VMUNjqK99iOV2vIK9+AYnGy53qKaID5K1k6qqHQ+xfAeLRZ59PoirtUIhlnMhlodxWlWB57G9yLM3Gq4L9f9Kb6H22VbH7MbpEMvDIZZzbZyDKrQNi/gS9xZ59lUfvwV8UeTZ1+likWfnsRRieUw1Mi2ii0/7EQ7qoR9wRjXiLIdY3tp0CLG8CnfqM6PVMd06x5k6Z1+0Ciry7FvsUB3zt+BEiOXREMv0qnqP6kK3ksaGWM6HWB7FCdWl/wB21Dn7YmBTF3n2B14OsVxWjTj7VXP4w7XLAv7Gh43Q1/CQ6p7zTJFnq4O4hhKUCFvF/hDLIziXPFrAx0WenWuEPI0Xijz7ZFgOxjypQyxvUB0DzxV5dnCcXD1cBEQb80QxYVyHAAAAAElFTkSuQmCC',

    createPreview: function() {
        var me = this,
            preview = '',
            style = '',
            image = me.getConfigValue('fallback_picture');

        if (Ext.isDefined(image)) {
            style = Ext.String.format('background-image: url([0]);', image);

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);
        }

        return preview;
    }
});
//{/block}
