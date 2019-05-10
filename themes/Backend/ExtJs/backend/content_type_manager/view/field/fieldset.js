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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/field/fieldset"}
Ext.define('Shopware.apps.ContentTypeManager.view.field.Fieldset', {
    extend: 'Ext.form.FieldSet',
    title: '{s name="handler/title"}{/s}',
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    getValues: function () {
        var values = {};

        this.items.each(function (field) {
            var val = field.getValue();

            if (Ext.isDefined(val) && val !== null) {
                values[field.getName()] = val;
            }
        });

        return values;
    },

    setValues: function (values) {
        values = values || {};

        this.items.each(function (field) {
            if (Ext.isDefined(values[field.name])) {
                field.setValue(values[field.name]);
            }
        });
    }
});
// {/block}
