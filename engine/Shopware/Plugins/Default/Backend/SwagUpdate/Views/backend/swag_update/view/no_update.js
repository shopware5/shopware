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

//{namespace name=backend/swag_update/main}
//{block name="backend/swag_update/view/no_update"}
Ext.define('Shopware.apps.SwagUpdate.view.NoUpdate', {

    extend: 'Enlight.app.Window',

    alias: 'widget.update-no-update',

    title: '{s name=window_title}Software Update{/s}',

    layout: 'fit',

    width: 350,

    height: 230,

    initComponent: function () {
        var me = this;

        me.items = [ me.createInfoPanel() ];

        return me.callParent(arguments);
    },

    createInfoPanel: function() {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            bodyPadding: 25,
            border: false,
            html: '<div style="text-align:center"><img style="margin-bottom:20px" src="data:image/png;base64,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">{s name="no_update_available"}<h1>Kein Update verfügbar</h1><br><p>Sie besitzen die aktuellste Shopware Version.</p>{/s}</div>'
        });
    }

});

//{/block}
