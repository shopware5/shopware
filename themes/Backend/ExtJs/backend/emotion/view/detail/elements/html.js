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
//{block name="backend/emotion/view/detail/elements/html"}
Ext.define('Shopware.apps.Emotion.view.detail.elements.Html', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-components-html-element',

    componentCls: 'html-text-element',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAcCAYAAAAJKR1YAAAASUlEQVRIiWPsnbXoP8MgAkwD7QB0MOqgUTAKaA0YR8shAmDUQaNgFNAajJZDhMCog0bBKKA1oFc5tLE4LS6AGIWDLpeNOogQAADIjg11nUruLwAAAABJRU5ErkJggg==',

    createPreview: function() {
        var me = this,
            preview = '',
            text = me.getConfigValue('text');

        if (Ext.isDefined(text)) {
            preview = '<div class="x-emotion-html-element-preview">' + text + '</div>';
        }

        return preview;
    }
});
//{/block}
