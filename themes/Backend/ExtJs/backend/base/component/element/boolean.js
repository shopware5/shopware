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
Ext.define('Shopware.apps.Base.view.element.Boolean', {
    extend: 'Ext.form.field.Checkbox',
    alias: [
        'widget.base-element-boolean',
        'widget.base-element-checkbox',
        'widget.config-element-boolean',
        'widget.config-element-checkbox'
    ],
    inputValue: true,
    uncheckedValue: false,

    initComponent: function () {
        var me = this;

        if (me.value) {
            me.setValue(!!me.value);
        }

        // Move support text to box label
        if (me.supportText) {
            me.boxLabel = me.supportText;
            delete me.supportText;
        } else if (me.helpText) {
            me.boxLabel = me.helpText;
            delete me.helpText;
        }

        me.callParent(arguments);
    }
});
