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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Combobox Tree From Field
 *
 * todo@all: Documentation
 *
 * Inspired by wendora
 * http://wendoratech.blogspot.de/2012/01/test-post.html
 *
 * @example
 * Ext.create('Shopware.form.field.ComboTree', {
 * xtype:'combotree',
 * name:'categoryId',
 * valueField: 'id',
 * * displayField: 'name',
 * treeField: 'categoryId',
 * fieldLabel:'Category',
 * store: me.comboTreeCategoryStore,
 *  selectedRecord : me.record
 * }
 * });
 */
Ext.define('Shopware.form.field.ComboTree', {
    extend:'Ext.form.Picker',
    alias:'widget.combotree',
    requires: [ 'Ext.tree.Panel' ],
    matchFieldWidth: false,

    /**
     * init the component
     */
    initComponent: function() {
        var me = this;

        me.store.on({
            scope: me,
            load: me.onStoreHasLoaded
        });

        me.callParent(arguments)
    },

    /**
     * will be called by auto magic and creates the tree panel
     *
     * @return Ext.tree.Panel
     */
    createPicker:function () {
        var me = this;

        var treeConfig = Ext.apply({
            floating: true,
            hidden: true,
            width:me.bodyEl.getWidth(),
            store: me.store,
            displayField: 'name',
            useArrows: true,
            rootVisible: false,
            autoScroll: true,
            queryMode: 'remote',
            height: 300,
            listeners:{
                scope:me,
                itemclick:me.onItemClick
            },
            flex: 1,
            root: {
                id: 1,
                expanded: true
            }
        }, me.treeConfig);
        me.treePanel = Ext.create('Ext.tree.Panel',treeConfig);
        return me.treePanel;
    },

    /**
     * executes when a tree item is clicked
     *
     * sets the value and the name of an element into the form element
     *
     * @param view
     * @param record
     * @param item
     * @param index
     * @param e
     * @param eOpts
     */
    onItemClick:function(view, record, item, index, e, eOpts){
        this.setFieldValue(record.data.id, record.data.name);
        this.fireEvent('select', this, record.data.name);
        this.collapse();
    },

    /**
     * sets the default value to the form element
     */
    afterRender: function() {
        var me = this;

        if(me.selectedRecord) {
            var value = me.selectedRecord.get(me.treeField || me.displayField);
            me.inputEl.dom.value = value;
            me.setFieldValue(value);
        }
        me.callParent(arguments);
    },

    /**
     * set the field value
     *
     * @param value
     * @param label
     */
    setFieldValue: function(value, label) {
        var me = this;
        if(!label) {
            label = value;
        }
        me.setValue(value);
        me.setRawValue(label);
    },

    /**
     * removes the empty class and set the value
     *
     * @param value
     */
    setValue:function(value){
        var me = this,
            inputEl = me.inputEl;

        if (inputEl && me.emptyText && !Ext.isEmpty(value)) {
            inputEl.removeCls(me.emptyCls);
        }
        me.value = value;
        
        if (inputEl && !Ext.isEmpty(value)){
            me.setRawValue(value); 
        }  

        me.applyEmptyText();
    },

    /**
     * sets the RawValue
     * @param value
     */
    setRawValue:function(value){
        this.inputEl.dom.value = value==null?"":value;
    },

    /**
     * get the Value
     * @return Ext.value
     */
    getValue:function(){
        return this.value;
    },

    /**
     * get the RawValue
     * @return Ext.value
     */
    getRawValue:function(){
        if(this.inputEl){
            return this.inputEl.dom.value;
        }
        return 0;

    },

    /**
     * set the raw value after store has been loaded
     *
     * @param store
     */
    onStoreHasLoaded: function(store) {
        var me = this,
                activeRecord;

        if(me.value) {
            activeRecord = store.getNodeById(me.value);

            if(!activeRecord) {
                return;
            }
            me.setRawValue(activeRecord.get(me.displayField));
        }
    },

    /**
     * prepares the submit value to be submitted
     *
     * @return Ext.value
     */
    getSubmitValue: function() {
        var me = this;
        if(!me.getRawValue()) {
            return "";
        }
        return this.value;
    },

    /**
     * Destroys the DragAndDropSelector panel
     *
     * @public
     * @return void
     */
    destroy: function() {
//        this.fromStore.destroy();
//        Ext.destroyMembers(this, 'fromField', 'toField');
        this.callParent();
    }
});
