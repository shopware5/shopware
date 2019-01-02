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
 * Shopware UI - BoundList Override
 *
 * This files provides an override to address the
 * boundlist easier in our Selenium Tests.
 *
 * The override adds an addtional HTML5 "data"-attrbute
 * to the element. The value of the attribute is
 * either the "name" property of the ComboBox or
 * the action
 */
//{block name="extjs/overrides/boundList"}
Ext.override(Ext.view.BoundList,
/** @lends Ext.view.BoundList# */
{
    /**
     * Suffix for the added data-attribute.
     * @string
     */
    dataSuffix: 'action',

    /**
     * Adds an additional HTML5 "data"-attribute
     * to easier address the element in our
     * selenium tests.
     *
     * @public
     * @return void
     */
    afterRender: function() {
        var me = this;
        me.callOverridden(arguments);

        if(me.el.dom && me.pickerField) {
            var dom = me.el.dom,
                value = me.action || me.pickerField.name;

            dom.setAttribute('data-' + me.dataSuffix, value);
        }
    }
});
//{/block}