/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name="backend/config/view/main"}
//{block name="backend/base/component/element/fieldset"}
Ext.define('Shopware.apps.Base.view.element.Fieldset', {
    extend: 'Ext.panel.Panel',
    alias: [
        'widget.base-element-fieldset',
        'widget.config-element-fieldset'
    ],

    bodyPadding: 10,
    border: false,

    layout: 'form',
    defaults: {
        anchor: '100%',
        labelWidth: 250,
        hideEmptyLabel: false
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
//{/block}
