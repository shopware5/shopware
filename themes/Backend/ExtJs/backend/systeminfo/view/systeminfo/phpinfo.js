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
 * @package    Systeminfo
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/systeminfo/view}

/**
 * Shopware UI - View for PHPInfo
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/systeminfo/view/systeminfo/phpinfo"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Phpinfo', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.Container',

    ui: 'shopware-ui',

    /**
     * ID to access the component out of other components
     */
    id: 'systeminfo-main-phpinfo',

    /**
     * Loads the phpinfo()-function
     */
    html: '<iframe frameborder="0" style="overflow-x: hidden;" height="100%" src="{url action=info}"></iframe>',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-list')
    * @string
    */
    alias: 'widget.systeminfo-main-phpinfo',

    listeners: {
        'afterrender': function () {
            var me = this;
            var win = me.up('window');

            this.getEl().dom.children[0].onload = function () {
                win.setWidth(win.getWidth() + 1);
            }
        }
    }
});
//{/block}
