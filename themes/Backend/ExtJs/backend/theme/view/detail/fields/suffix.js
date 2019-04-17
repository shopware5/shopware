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

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/detail/fields/suffix"}

Ext.define('Shopware.apps.Theme.view.detail.fields.Suffix', {
    extend: 'Ext.form.field.Text',

    alias: 'widget.theme-suffix-field',

    suffix: Ext.undefined,
    fallbackValue: Ext.undefined,
    elementStyle: Ext.undefined,

    initComponent: function() {
        var me = this;

        me.on('blur', function() {
            me.valueChanged(me.getValue())
        });

        if (me.elementStyle !== Ext.undefined) {
            me.setFieldStyle(me.elementStyle);
        }

        return me.callParent(arguments);
    },

    valueChanged: function(value) {
        var me = this;

        value = Ext.String.trim(value) + '';

        if (value.length === 0 && me.fallbackValue !== Ext.undefined) {
            value = me.fallbackValue;
        }

        if (value.length > 0
                && me.suffix !== Ext.undefined
                && value.indexOf(me.suffix) == -1) {

            value = value + me.suffix;
        }

        this.setValue(value);
    }
});

//{/block}
