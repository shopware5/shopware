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

//{block name="backend/theme/app"}

Ext.define('Shopware.apps.Theme', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.Theme',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'List', 'Detail', 'Settings' ],

    views: [
        'list.Window',
        'list.Theme',
        'list.extensions.Info',

        'detail.Theme',
        'detail.Window',

        'create.Window',

        'config_sets.Window',

        'detail.containers.Tab',
        'detail.containers.TabPanel',
        'detail.containers.FieldSet',

        'detail.fields.Suffix',
        'detail.fields.PixelField',
        'detail.fields.CheckboxField',
        'detail.fields.ColorPicker',
        'detail.fields.DateField',
        'detail.fields.EmField',
        'detail.fields.MediaSelection',
        'detail.fields.PercentField',
        'detail.fields.TextAreaField',
        'detail.fields.TextField',
        'detail.fields.SelectField',

        'settings.Window',
        'settings.Settings'
    ],

    models: [ 'Theme', 'Element', 'ConfigValue', 'Layout', 'ConfigSet', 'Settings' ],
    stores: [ 'Theme', 'ConfigSets' ],

    launch: function() {
        return this.getController('List').mainWindow;
    }
});

//{/block}
