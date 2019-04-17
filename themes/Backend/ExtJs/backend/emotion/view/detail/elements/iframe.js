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
//{block name="backend/emotion/view/detail/elements/iframe"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Iframe', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-iframe',

    componentCls: 'iframe-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAcCAYAAAAJKR1YAAACpklEQVRIie2XO2gVQRSGvxtXY2IgSR0LBW0tIjZqI5o2EbExciC3GTAgPkYk4gMLBUEGUojBsYhmiGiXYGFhFLRRlIjY+Ch8IVhIuBJQ0EhisbNmM+4mayLeW/jDcvee8+85/56dOXu2ZKybIQWtpJT+b6zrAQbJxqhWsjPgjwBdOfyyVnIl4M/JX5dzYdUQpc5bczjXgJEc31SGbS+wPIf/NcOW5K0AlJKShY/qXyPREZFfmSJBmoB1wDutpLJETa0AkVbyeQlBBLgIbMSXfLFIdEQLEQGMdZuBBq3kTuDaAUwATz1vtbdd1UpmWAQiv00Jt69P0AacB/YAT4grkfiWAduAMa1k2psPAhrYZ6w7oJU8LCok0RGR0TOMdSuBw8BxoB4YAE4GtHbi5347ZesDPgCngQfGuiGgTyv5WEBTF2T0IWNdA/AcOAs8Btq1kl6tZCKgdvjfscSglfzQSvqB9YAlXmOvjHWbCggiUxBxRdYQr41TWsmznGu3A6+1kjehQyv5BJwDHgFNQFtRQVmLehLoBc4A94x1N4CjWsn7hOCruAUYCi821jUCx4AjPn4/cLeooDqg7A8AtJJprWSAuL9cAHYDL4x16TW0lbiS6fWDsa4TeAmcAO4DG7SSQ1rJZAEtZaAchS+7lLAKsN9Yd4n4Ltem3B3ADL/feTPwDejUSm4WEJHOl6kjF8a6+tT5uLFuPINTMtat+KPAAUp+vCis0Ce8DtzSSi4vJXkQtwfiRZfMOoUEaSXfgV1/S0gKg1CD89B/QQshAkarLcKjVnTUOErGuhaYndiqAWNdibjLEzE7elZzyG9OdPx62+dVynfmxpxAU1rJl4C/ink+g3xjTfNbvCBg7ravkD2od6d84TGcwR+eh9+dwa8Ab7ME1QR+AoUN5CNgPa9JAAAAAElFTkSuQmCC',

    createPreview: function() {
        var me = this,
            preview = '',
            content = '',
            url = me.getConfigValue('iframe_url');

        if (Ext.isDefined(url)) {
            content += Ext.String.format('<div class="x-emotion-preview-title">[0]:</div>', me.getLabel());
            content += Ext.String.format('<div class="iframe-url">[0]</div>', url);

            preview = Ext.String.format('<div class="x-emotion-iframe-preview">[0]</div>', content);
        }

        return preview;
    }
});
//{/block}
