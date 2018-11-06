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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/config/controller/main}

/**
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/config/controller/document"}
Ext.define('Shopware.apps.Config.controller.Document', {

    extend: 'Enlight.app.Controller',

    views: [
        'form.Document'
    ],

    stores:[
        'form.Document',
        'detail.Document',
        'form.Number'
    ],

    models:[
        'form.Document',
        'form.DocumentElement'
    ],

    refs: [
        { ref: 'detail', selector: 'config-base-detail' }
    ],

    init: function () {
        var me = this;

        me.control({
            'config-form-document config-base-table': {
                selectionchange: function(table, records) {
                    var me = this,
                        elementFieldSet = me.getDetail().down('fieldset[name=elementFieldSet]'),
                        contentField = elementFieldSet.down('tinymce[name$=Value]'),
                        styleField = elementFieldSet.down('textarea[name$=Style]');

                    Ext.each(elementFieldSet.items.items, function(item){
                        if(item.xtype === 'tinymce' || item.xtype === 'textarea'){
                            item.hide();
                            item.setValue(null);
                        }
                    });
                }
            },
            'config-base-detail combo[name=elements]': {
                change: me.onSelectElement
            },
            'config-form-document config-base-detail': {
                recordchange: me.onRecordChange
            }
        });

        me.callParent(arguments);
    },

    onSelectElement: function(combo, newValue, oldValue){
        //If there is no new value selected, so the event got fired otherwise
        if(!newValue){
            return;
        }
        var me = this,
            elementFieldSet = me.getDetail().down('fieldset[name=elementFieldSet]'),
            elementComboBox = elementFieldSet.down('combo'),
            elementStore = elementComboBox.getStore();
        //Checks if there was an value selected before changing it
        //Needed to save the values to the record
        if(oldValue){
            var oldRecord = elementStore.getById(oldValue),
                oldFieldName = oldRecord.get('name'),
                oldContentField = elementFieldSet.down('tinymce[name=' + oldFieldName + '_Value]'),
                oldStyleField = elementFieldSet.down('textarea[name=' + oldFieldName + '_Style]');

            oldRecord.set('value', oldContentField.getValue());
            oldRecord.set('style', oldStyleField.getValue());
            oldContentField.hide();
            oldStyleField.hide();
        }

        var newRecord = elementStore.getById(newValue),
            newFieldName = newRecord.get('name'),
            newContentField = elementFieldSet.down('tinymce[name=' + newFieldName + '_Value]'),
            newStyleField = elementFieldSet.down('textarea[name=' + newFieldName + '_Style]');


        //Show the dynamical fields and fill them
        newContentField.show();
        newStyleField.show();
        newContentField.setValue(newRecord.get('value'));
        newStyleField.setValue(newRecord.get('style'));
    },

    /**
     * Hide the document boxes field set when creating a new document
     *
     * @param { Ext.form.Panel } formPanel
     * @param { Shopware.apps.Config.model.form.Document } record
     */
    onRecordChange: function (formPanel, record) {
        if (!record) {
            return;
        }
        var showDocumentBoxesFieldSet = record.get('id') !== 0;
        formPanel.down('[name=elementFieldSet]').setDisabled(!showDocumentBoxesFieldSet);
    }

});
//{/block}
