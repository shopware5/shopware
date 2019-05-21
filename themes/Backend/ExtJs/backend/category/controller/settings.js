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
 * @package    Category
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware Controller - category management controller
 *
 * The category management controller handles the initialisation of the category tree.
 */
//{block name="backend/category/controller/settings"}
Ext.define('Shopware.apps.Category.controller.Settings', {
    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',
    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'settingsForm', selector: 'category-category-tabs-settings' },
        { ref: 'mainWindow', selector: 'category-main-window' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            'category-category-tabs-settings':{
                recordloaded : me.onRecordLoaded
            },
        });
    },
    /**
     * Reacts if the event recordloaded is fired and hides or shows the template selection based
     * on the parent ID of the loaded record.
     *
     * @event recordloaded
     * @param record [Ext.data.Model]
     * @return void
     */
    onRecordLoaded : function(record, treeRecord) {
        var me = this,
            form = me.getSettingsForm(),
            store = form.templateComboBox.getStore(),
            records = store.getRange(),
            customTpl = record.get('template'),
            i = 0,
            count = records.length,
            activeTab;

        // handle special cases for custom templates
        for (i; i < count; i++) {
            var rec = records[i];
            if (rec.customTpl == true) {
                store.remove(rec);
            }
        }

        if (customTpl !== '') {
            var tplRecord = store.findRecord('template', record.get('template'));
            if (!tplRecord) {
                tplRecord = store.model.create({
                    template: record.get('template'),
                    name: record.get('template') + ' (' +  form.snippets.defaultSettingsTemplateNotAvailable + ')'
                });
                tplRecord.customTpl = true;
                store.insert(1, tplRecord);
                form.templateComboBox.select(tplRecord);
            }
        }

        if(record.getId() != me.subApplication.defaultRootNodeId){
            if(~~(1 * record.get('parentId')) === 1 || store.count() === 1) {
                form.templateComboBox.hide();
            } else {
                form.templateComboBox.show();
            }

            form.streamSelection.enable();

            activeTab = me.getMainWindow().tabPanel.getActiveTab();

            //to select the settings if the tree element is not a leaf
            if(!treeRecord.get('leaf') && activeTab.name !== 'custom-listing'){
                me.getMainWindow().tabPanel.setActiveTab(0);
            }

            //show or hide the emotionNotice if an emotion is mapped to this category
            if(record.getEmotion().count()){
                form.emotionNotice.show();
            }
            else{
                form.emotionNotice.hide();
            }
            me.enableForm();
        }
        else {
            //if the main category is clicked disable the settings form
            me.disableForm();
        }
    },
    /**
     * Enables the form which is disabled by default
     *
     * @return void
     */
    enableForm : function() {
        var me   = this,
            form = me.getSettingsForm();
        form.defaultSettings.enable();
        form.createCategory.enable();
        form.cmsSettings.enable();
        form.metaInfo.enable();
        form.attributes.enable();
    },

    /**
     * Disables the form which is disabled by default
     *
     * @return void
     */
    disableForm : function() {
        var me   = this,
            form = me.getSettingsForm();
        form.defaultSettings.disable();
        form.createCategory.disable();
        form.cmsSettings.disable();
        form.metaInfo.disable();
        form.attributes.disable();
    }
});
//{/block}
