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
 */

//{namespace name=backend/emotion/view/components/content_type}
//{block name="backend/emotion/store/content_type_mode"}
Ext.define('Shopware.apps.Emotion.store.ContentTypeMode', {
    extend: 'Ext.data.Store',
    fields: ['id', 'name'],
    data: [
        {
            id: 0,
            name: '{s name="mode/0"}{/s}'
        },
        {
            id: 1,
            name: '{s name="mode/1"}{/s}'
        },
        {
            id: 2,
            name: '{s name="mode/2"}{/s}'
        },
    ]
});
//{/block}
