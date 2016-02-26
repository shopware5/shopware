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

//{block name="backend/emotion/view/components/html_element"}
//{namespace name=backend/emotion/view/components/html_element}
Ext.define('Shopware.apps.Emotion.view.components.HtmlElement', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-html-element',

    snippets: {
        text: {
            fieldLabel: '{s name=text/label}Text{/s}',
            supportText: '{s name=text/support}Do not add styling{/s}'

        },
        cms_title: '{s name=cms_title}Title{/s}',
        needsNoStyling: {
            fieldLabel: '{s name=needsNoStyling/label}Do not add styling{/s}',
            supportText: '{s name=needsNoStyling/support}If selected, no other layout styling is applied.{/s}'
        }
    }
});
//{/block}
