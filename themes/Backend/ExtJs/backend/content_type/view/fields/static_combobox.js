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
 */

// {block name="backend/content_type/view/fields/static_combobox"}
Ext.define('Shopware.apps.{$controllerName}.view.fields.StaticCombobox', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.static-combobox',

    initComponent: function () {
        this.queryMode = 'local';
        this.displayField = 'name';
        this.valueField = 'id';
        this.store = Ext.create('Ext.data.Store', {
            fields: [
                { name: 'id', type: 'string' },
                { name: 'name', type: 'string' }
            ],
            data: this.store
        });

        this.callParent(arguments);
    }
});
// {/block}
