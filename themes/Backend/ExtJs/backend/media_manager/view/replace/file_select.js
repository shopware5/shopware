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
 */

//{namespace name=backend/media_manager/view/replace}
//{block name="backend/media_manager/view/replace/FileSelect"}
Ext.define('Shopware.apps.MediaManager.view.replace.FileSelect', {
    extend: 'Ext.form.field.File',

    anchor: '100%',
    margin: '10 0 20 0',
    name: 'images[]',
    labelStyle: 'font-weight: 700',
    labelWidth: 0,
    allowBlank: true,

    buttonConfig: {
        cls: 'secondary small',
        iconCls: 'sprite-inbox-image'
    },

    /**
     * init´s the component
     */
    initComponent: function() {
        var me = this;

        if (me.maxFileUpload > 1) {
            me.on('render', function() {
                me.fileInputEl.set({ multiple: true });
            });
        }

        me.callParent(arguments);
    },

    /**
     * @override
     */
    onFileChange: function(button, e, value) {
        this.duringFileSelect = true;

        var me = this,
            upload = me.fileInputEl.dom,
            files = upload.files,
            names = [];

        if (files) {
            for (var i = 0; i < files.length; i++)
                names.push(files[i].name);
            value = names.join(', ');
        }

        Ext.form.field.File.superclass.setValue.call(this, value);

        delete this.duringFileSelect;
    }
});
//{/block}
