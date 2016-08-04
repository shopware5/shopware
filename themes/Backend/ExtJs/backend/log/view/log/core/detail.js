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
 * @package    Log
 * @subpackage View
 * @author VIISON GmbH
 */

//{namespace name=backend/log/core}

//{block name="backend/log/view/log/core/detail"}
Ext.define('Shopware.apps.Log.view.log.core.Detail', {
    extend: 'Shopware.apps.Log.view.log.shared.Detail',
    alias: 'widget.log-core-detail-window',
    cls: Ext.baseCSSPrefix + 'log-core-detail',
    title: '{s name=title}Core{/s}'
});
//{/block}
