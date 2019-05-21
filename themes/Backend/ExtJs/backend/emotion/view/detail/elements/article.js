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
//{block name="backend/emotion/view/detail/elements/article"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Article', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-article',

    componentCls: 'article-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAfCAYAAACPvW/2AAACuElEQVRYhe2XS0hVQRjHf/dqWhT0ABeh0QOCEswIiiyIHgRmr3t3rcbd7GtatG0lQbN1MTsHpHY+yrTEMImIaCEJkZsoekgP0qjQ0rwtZm7ndLrec64ibu4fzuKe+eb//eabOXNnUixS2tgK4AiQAbL+dTfQBYwoKX4vxjdVIsQa4KQHOAdsWiB0Euj1cPeUFNNJc1SWAgSsAtYBa4HqInEbgVb/TGtjB3DVu62k+FIsQcEKaWPrgPPAGeClNxtWUsyGYqpx1crgqlWTYEBzwAiucj1KijcLAmlj6715BthfwGwK6PNmd5UU30N9K4BDBOtpewI4gKf4daekeP4XSBvbAFwGzuLKHacZYNCb9SopPocbtbGNIbjGGK9J4BZwXUkx9s+UaWMrcV9OFjdlWxLAzQMPcZXrUlK8jnhu835Z4DCQBt76wXQDD5QUc/n4lDb2ojd6FTFKAftCZvUJ4ABGQ3BjEc8aoA4YVVLkCoGntLG5iFG3kuJZNIs2dqcHywAHid8ycsAN4JKS4kOhAG3snpDnXnAVyhWIzX9ZXcAjJcV8xGgzbkozwDGgyjfNAvcJ1tb7SL80bvHnIXZEEy8EFNYnoMfDDSkpfkaSrAda/M87SoqvkfZq4ATBuiy6PSQBCusb0O/h+qPJI5CnPEQLbjNNpFKBwvqFm56bSooOD9IKXACOE0xjSSr1ryOsKqAZ2A10+HdXga1L8CS9lM7LoTJQnMpAcSoDxakMFKcyUJzSwMRKQ4Q0kQZqgSbgGjC+AhDjPncTUFvpz7aP/XNFG7uL4ER3YJkgnhBcf16EG/47fviANqBNG1uLO+VlgaOF4hNqDhgmuCC+WyiwaALfsR1o18ZuAE57uGbcdbqYfgADHqJPSTGVhDzxiL1hJ9CpjV1NcI1uCIV9BIZw0zGopJhJ6p/XH0Dn4y629c4yAAAAAElFTkSuQmCC',

    typeSnippets: {
        'newcomer': '{s name="article/store/newcomer" namespace="backend/emotion/view/components/article"}{/s}',
        'topseller': '{s name="article/store/topseller" namespace="backend/emotion/view/components/article"}{/s}',
        'random_article': '{s name="article/store/random_article" namespace="backend/emotion/view/components/article"}{/s}'
    },

    createPreview: function() {
        var me = this,
            preview = '',
            content = '',
            type = me.getConfigValue('article_type');

        if (Ext.isDefined(type)) {
            content += Ext.String.format('<div class="x-emotion-preview-title">[0]:</div>', me.getLabel());

            if (type === 'selected_article') {
                content += Ext.String.format('<div class="article-ordernumber">[0]</div>', me.getConfigValue('article'));
            } else if (type === 'selected_variant') {
                content += Ext.String.format('<div class="article-ordernumber">[0]</div>', me.getConfigValue('variant'));
            } else {
                content += Ext.String.format('<div class="article-type">[0]</div>', me.typeSnippets[type]);
            }

            preview = Ext.String.format('<div class="x-emotion-article-element-preview">[0]</div>', content);
        }

        return preview;
    }
});
//{/block}
