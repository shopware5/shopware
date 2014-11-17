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
 * @package    Banner
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/banner/view/main}*/

/**
 * Shopware UI - Banner View Main Panel
 *
 * View component which features the main panel
 * of the module. It displays the banners.
 */
//{block name="backend/banner/view/main"}
Ext.define('Shopware.apps.Banner.view.Main', {
    extend: 'Enlight.app.Window',
    layout: 'fit',
    alias: 'widget.bannermanager',
    width: 1000,
    height: '90%',
    maximizable: true,
    stateful: true,
    stateId: 'BannerManager',
    border: 0,
    title: '{s name=main_title}Banner Management{/s}'
});
//{/block}
