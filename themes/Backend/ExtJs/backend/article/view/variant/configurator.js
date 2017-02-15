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
 * @package    Article
 * @subpackage Variants
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article variants - Configurator
 * The configurator component contains all elements to generate variants automatically.
 * The user can define groups and options and generate over a cross joining logic multiple variants.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/configurator"}
Ext.define('Shopware.apps.Article.view.variant.Configurator', {
    /**
     * Define that the configurator is an extends the Ext.container.Container
     * @string
     */
    extend:'Ext.panel.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-variant-configurator',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-variant-configurator',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        toolbar: {
            saveSet: '{s name=variant/configurator/save_set}Save set{/s}',
            template: '{s name=variant/configurator/template}Define configurator template{/s}',
            loadSet: '{s name=variant/configurator/load_set}Load set{/s}',
            dependency: '{s name=variant/configurator/dependency}Set dependency{/s}',
            variation: '{s name=variant/configurator/variation}Define price variation{/s}',
            type: '{s name=variant/settings/type/label}Configurator type{/s}',
            empty: '{s name=variant/settings/combo_empty}Please choose...{/s}'
        },
        groups: {
            title: '{s name=variant/configurator/group/title}Manage attribute groups{/s}',
            create: '{s name=variant/configurator/group/create}Create{/s}',
            createAndActivate: '{s name=variant/configurator/group/create_and_activate}Create & Activate{/s}',
            groupName: {
                label: '{s name=variant/configurator/group/name_label}Create group{/s}',
                empty: '{s name=variant/configurator/group/name_empty}Color{/s}'
            },
            activeGroups: '{s name=variant/configurator/group/active_groups_header}Active groups{/s}',
            inactiveGroups: '{s name=variant/configurator/group/inactive_groups_header}Inactive groups{/s}',
            selected: '{s name=variant/configurator/group/group_header_selected}selected{/s}',
            remove: '{s name=variant/configurator/group/remove_column}Delete group{/s}',
            edit: '{s name=variant/configurator/group/edit_column}Edit group{/s}',
            name: '{s name=variant/configurator/group/name_column}Group name{/s}',
            description: '{s name=variant/configurator/group/description_column}Group description{/s}',
            active: '{s name=variant/configurator/group/active_column}Activated{/s}',
            options: {
                header: '{s name=variant/configurator/group/options_column_header}Option count{/s}'
            }
        },
        options: {
            title: '{s name=variant/configurator/option/title}Manage attribute options{/s}',
            titleLoaded: '{s name=variant/configurator/option/title}Manage attribute options of group:{/s}',
            optionName: {
                label: '{s name=variant/configurator/option/name_label}Create option{/s}',
                empty: '{s name=variant/configurator/option/name_empty}Yellow{/s}'
            },
            edit: '{s name=variant/configurator/option/edit_column}Edit option{/s}',
            remove: '{s name=variant/configurator/option/remove_column}Delete option{/s}',
            name: '{s name=variant/configurator/option/name_column}Option name{/s}',
            activeOptions: '{s name=variant/configurator/option/active_options_header}Active options{/s}',
            inactiveOptions: '{s name=variant/configurator/option/inactive_options_header}Inactive options{/s}'
        },
        types: {
            standard:'{s name=variant/configurator/types/standard}Standard{/s}',
            selection:'{s name=variant/configurator/types/selection}Selection{/s}',
            picture:'{s name=variant/configurator/types/picture}Picture{/s}'
        }
    },

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.typeData = [
            [0, me.snippets.types.standard],
            [1, me.snippets.types.selection],
            [2, me.snippets.types.picture]
        ];

        me.items = me.createItems();
        me.dockedItems = [ me.createToolbar() ];
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "save set" button
             * which displayed in the top toolbar of the whole variant tab.
             * @event
             */
            'displaySetSaveWindow',

            /**
             * Event will be fired when the user clicks the "load set" button
             * which displayed in the top toolbar of the whole variant tab.
             * @event
             */
            'displaySetLoadWindow',

            /**
             * Event will be fired when the user clicks the "set dependency" button
             * which displayed in the top toolbar of the whole variant tab.
             * @event
             */
            'defineDependency',

            /**
             * Event will be fired when the user clicks the "define price variations" button
             * which displayed in the top toolbar of the whole variant tab.
             * @event
             */
            'openPriceVariation',

            /**
             * Event will be fired when the user clicks the "define configurator template" button
             * which displayed in the top toolbar of the whole variant tab.
             * @event
             */
            'defineConfiguratorTemplate',


            /**
             * Event will be fired when the user clicks on the "create" button in the
             * group panel. If the user choose "create & activate" the "activate" parameter
             * will be set to true, otherwise the parameter is set to false.
             * @event
             * @param name - The value of the text field in the toolbar
             * @param activate - If the user clicks the button "create & activate" the parameter will be set to true
             */
            'createGroup',

            /**
             * Event will be fired when the user clicks the "create" button which
             * displayed in the top toolbar of the option panel.
             * @event
             * @param name - The value of the text field
             */
            'createOption',

            /**
             * Event will be fired when the user clicks the delete column in the group grid.
             * @event
             * @param record - The selected grid record
             */
            'deleteGroup',
            /**
             * Event will be fired when the user clicks the edit column in the group grid.
             */
            'editGroup',
            /**
             * Event will be fired when the user clicks the delete column in the option grid.
             * @event
             * @param record - The selected grid record
             */
            'deleteOption',
            /**
             * Event will be fired when the user clicks the edit column in the option grid.
             */
            'editOption',
            /**
             * Event will be fired when the user drag and drops a configurator group
             */
            'groupDropped',
            /**
             * Event will be fired when the user clicks on a group row
             */
            'groupClick',
            /**
             * Event will be fired when the user select a group row
             */
            'groupSelect',
            /**
             * Event will be fired when the user deselect a group row
             */
            'groupDeselect',
            /**
             * Event will be fired when the user drag and drops a configurator option
             */
            'optionDropped',
            /**
             * Event will be fired when the user clicks on a option row
             */
            'optionClick',
            /**
             * Event will be fired when the user select a option row
             */
            'optionSelect',
            /**
             * Event will be fired when the user deselect a option row
             */
            'optionDeselect',
            /**
             * Event will be fired when the user changes the configurator type combo box.
             */
            'typeChanged'
        );
    },

    /**
     * Creates the elements for this component.
     * @return array
     */
    createItems: function() {
        var me = this;
        return [
            me.createGroupPanel(),
            me.createOptionPanel()
        ];
    },

    /**
     * Creates the toolbar for the configurator tab.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        me.typeComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            queryMode: 'local',
            valueField: 'id',
            labelWidth: 150,
            editable: false,
            displayField: 'name',
            store: new Ext.data.SimpleStore({
                fields:['id', 'name'], data: me.typeData
            }),
            emptyText: me.snippets.toolbar.empty,
            fieldLabel: me.snippets.toolbar.type,
            listeners: {
                change: function(field, newValue, oldValue) {
                    me.fireEvent('typeChanged', field, newValue, oldValue);
                }
            }
        });
        if (me.articleConfiguratorSet) {
            me.typeComboBox.setValue(me.articleConfiguratorSet.get('type'));
        } else {
            me.typeComboBox.setValue(0);
        }

        //creates the "save set" button to save the current defined groups and options as a default set of groups and options.
        me.saveSetButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.saveSet,
            handler: function() {
                me.fireEvent('displaySetSaveWindow');
            }
        });

        //creates the "load set" button to load already defined standard groups and options.
        me.loadSetButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.loadSet,
            handler: function() {
                me.fireEvent('displaySetLoadWindow');
            }
        });

        //creates the "Set dependency" button to define dependency for the variant generation.
        me.dependencyButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.dependency,
            handler: function() {
                me.fireEvent('defineDependency', me.dependencyStore);
            }
        });

        //creates the "Define price variation" button to define price variation for options or group associations.
        me.priceVariationButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.variation,
            handler: function() {
                me.fireEvent('openPriceVariation');
            }
        });

        me.templateButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.template,
            handler: function() {
                me.fireEvent('defineConfiguratorTemplate', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [
                me.typeComboBox,
                { xtype:'tbspacer', width: 6 },
                { xtype: 'tbseparator' },
                { xtype:'tbspacer', width: 6 },
                me.saveSetButton,
                { xtype:'tbspacer', width: 12 },
                me.loadSetButton,
                { xtype:'tbspacer', width: 6 },
                { xtype: 'tbseparator' },
                { xtype:'tbspacer', width: 6 },
                me.dependencyButton,
                { xtype:'tbspacer', width: 12 },
                me.priceVariationButton,
                { xtype:'tbspacer', width: 12 },
                me.templateButton
            ]
        });
    },

    /**
     * Creates the panel for the group definition.
     */
    createGroupPanel: function() {
        var me = this;

        //creates the toolbar with the create button and the name field for the group panel
        me.groupToolbar = me.createGroupPanelToolbar();

        //creates the grid grouping feature for the group panel.
        me.groupPanelGroupingFeature = me.createGroupPanelGroupingFeature();

        me.groupGrid = Ext.create('Ext.grid.Panel', {
            title: me.snippets.groups.title,
            margin: 8,
            name: 'configurator-group-listing',
            store: me.configuratorGroupStore,
            flex: 1,
            selModel: me.getGroupPanelSelModel(),
            columns: me.createGroupGridColumns(),
            features: [ me.groupPanelGroupingFeature ],
            plugins: [
                {
                    ptype: 'gridtranslation',
                    translationType: 'configuratorgroup'
                }
            ],
            dockedItems: [ me.groupToolbar ],
            listeners: {
                cellclick: function(view, td, cellIndex, record) {
                    if (cellIndex === 0 && record.get('active') === true) {
                        me.fireEvent('groupDeselect', record, me);
                    }
                    if (cellIndex !== me.groupGrid.columns.length - 1) {
                        me.fireEvent('groupClick', me.groupGrid, record);
                    }
                }
            }
        });
        me.initGroupDragAndDrop();
        return me.groupGrid;
    },



    /**
     * Creates the drag and drop zone for the Ext.view.View to allow
     */
    initGroupDragAndDrop: function() {
        var me = this;
        me.groupGrid.view.on('afterrender', function(v) {
            me.groupGrid.dragZone = new Ext.dd.DragZone(v.getEl(), {
                getDragData: function(e) {
                    //Use the DataView's own itemSelector to
                    //test if the mousedown is within one of the DataView's nodes.
                    var sourceEl = e.getTarget(v.itemSelector, 10);

                    //If the mousedown is within a DataView node, clone the node to produce
                    //a ddel element for use by the drag proxy. Also add application data
                    //to the returned data object.
                    if (sourceEl) {
                        var d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        var result = {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: v.store,
                            draggedRecord: v.getRecord(sourceEl)
                        };
                        return result;
                    }
                },
                getRepairXY: function() {
                    return this.dragData.repairXY;
                }
            });

            me.groupGrid.dropZone = new Ext.dd.DropZone(me.groupGrid.getEl(), {
                //If the mouse is over a grid row, return that node. This is
                //provided as the "target" parameter in all "onNodeXXXX" node event handling functions
                getTargetFromEvent: function(e) {
                    return e.getTarget(me.groupGrid.view.itemSelector);
                },

                //On entry into a target node, highlight that node.
                onNodeEnter : function(target, dd, e, data){
                    var record = me.groupGrid.view.getRecord(target);
                    if (record !== data.draggedRecord) {
                        Ext.fly(target).addCls(me.dragOverCls);
                    }
                },

                //On exit from a target node, unhighlight that node.
                onNodeOut : function(target, dd, e, data){
                    Ext.fly(target).removeCls(me.dragOverCls);
                },

                //While over a target node, return the default drop allowed class which
                //places a "tick" icon into the drag proxy.
                onNodeOver : function(target, dd, e, data){
                    return (data.draggedRecord instanceof Ext.data.Model);
                },

                //On node drop we can interrogate the target to find the underlying
                //application object that is the real target of the dragged data.
                //In this case, it is a Record in the GridPanel's Store.
                //We can use the data set up by the DragZone's getDragData method to read
                //any data we decided to attach in the DragZone's getDragData method.
                onNodeDrop : function(target, dd, e, data){
                    var record = me.groupGrid.view.getRecord(target);
                    me.fireEvent('groupDropped', data.draggedRecord, record)
                }
            });

        });
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGroupPanelSelModel:function () {
        var me = this;
        return Ext.create('Ext.selection.CheckboxModel', {
            checkOnly: true,
            showHeaderCheckbox: false,
            listeners:{
                select:function (sm, record) {
                    var success = me.fireEvent('groupSelect', record, me);
                    if (success === false) {
                        sm.deselect(record, true);
                    }
                }
            }
        });
    },


    /**
     * Creates the grouping feature for the attribute group grid.
     * Groups the records by the active flag and display "active groups" and "inactive groups" as group header.
     */
    createGroupPanelGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                '<span>&nbsp;({ rows.length } ' + me.snippets.groups.selected + ')</span>',
                {
                    formatHeader: function(active) {
                        if (active === true || active === 'true') {
                            return me.snippets.groups.activeGroups;
                        } else {
                            return me.snippets.groups.inactiveGroups;
                        }
                    }
                }
            )
        });
    },

    /**
     * Creates the grid columns for the group grid.
     * @return array
     */
    createGroupGridColumns: function() {
        var me = this;

        return [{
            dataIndex: 'name',
            flex: 2,
            text: me.snippets.groups.name,
            translationEditor: {
                xtype: 'textfield',
                allowBlank: false,
                fieldLabel: me.snippets.groups.name,
                name: 'name'
            }
        } , {
            dataIndex: 'description',
            flex: 2,
            text: me.snippets.groups.description,
            translationEditor: {
                xtype: 'textarea',
                allowBlank: false,
                fieldLabel: me.snippets.groups.description,
                name: 'description'
            },
            hidden: true
        },{
            dataIndex: 'optionCount',
            flex: 1,
            text: me.snippets.groups.options.header,
            renderer: me.optionCountColumnRenderer
        } , {
            /**
             * Special column type which provides clickable icons in each row
             */
            xtype:'actioncolumn',
            width:90,
            items:[{
                iconCls:'sprite-minus-circle-frame',
                action:'deleteGroup',
                tooltip:me.snippets.groups.remove,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteGroup', record);
                }
            } , {
                iconCls:'sprite-pencil',
                action:'editGroup',
                tooltip:me.snippets.groups.edit,
                handler:function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editGroup', record);
                }
            }]
        }];
    },

    /**
     * Creates the toolbar for the group panel
     * @return
     */
    createGroupPanelToolbar: function() {
        var me = this;

        //creates the cycle button for the group panel which contains two options to create or create and activate a group.
        me.createGroupButton = Ext.create('Ext.button.Split', {
            showText: true,
            action : 'activate',
            text: me.snippets.groups.createAndActivate,
            name: 'create-group-button',
            handler: function() {
                me.fireEvent('createGroup', me.groupNameField.getValue(), (me.createGroupButton.action === 'activate'));
                me.groupNameField.reset();
            },
            menu: {
                xtype: 'menu',
                items: [
                    {
                        xtype: 'menuitem',
                        text: me.snippets.groups.create,
                        action: 'create',
                        handler: function() {
                            me.createGroupButton.setText(me.snippets.groups.create);
                            me.createGroupButton.action = 'create';
                        }
                    }, {
                        xtype: 'menuitem',
                        text: me.snippets.groups.createAndActivate,
                        action : 'activate',
                        handler: function() {
                            me.createGroupButton.setText(me.snippets.groups.createAndActivate);
                            me.createGroupButton.action = 'activate';
                        }
                    }
                ]
            }
        });

        //creates the text field to create a configurator group.
        me.groupNameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.groups.groupName.label,
            emptyText: me.snippets.groups.groupName.empty,
            labelWidth: 155,
            name: 'groupName',
            listeners: {
                specialkey: function(field, event) {
                    if (event.getKey() === event.ENTER) {
                        me.fireEvent('createGroup', field.getValue(), (me.createGroupButton.action === 'activate'));
                        field.reset();
                    }
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: [
                me.groupNameField,
                '->',
                me.createGroupButton
            ]
        });
    },


    /**OPTION PANEL DECLARATION*/

    /**
     * Creates the panel for the option definition.
     */
    createOptionPanel: function() {
        var me = this;

        //creates the toolbar with the create button and the name field for the option panel
        me.optionToolbar = me.createOptionPanelToolbar();

        //creates the grid grouping feature for the option panel.
        me.optionPanelGroupingFeature = me.createOptionPanelGroupingFeature();

        me.optionGrid = Ext.create('Ext.grid.Panel', {
            title: me.snippets.options.title,
            margin: 8,
            disabled: true,
            name: 'configurator-option-listing',
            flex: 1,
            selModel: me.getOptionPanelSelModel(),
            columns: me.createOptionGridColumns(),
            features: [ me.optionPanelGroupingFeature ],
            dockedItems: [ me.optionToolbar ],
            plugins: [
                {
                    ptype: 'gridtranslation',
                    translationType: 'configuratoroption'
                }
            ],
            listeners: {
                cellclick: function(view, td, cellIndex, record) {
                    if (cellIndex === 0 && record.get('active') === true) {
                        me.fireEvent('optionDeselect', record, me);
                    }
                }
            }
        });
        me.initOptionDragAndDrop();
        return me.optionGrid;
    },

    /**
     * Creates the drag and drop zone for the Ext.view.View to allow
     */
    initOptionDragAndDrop: function() {
        var me = this;
        me.optionGrid.view.on('afterrender', function(v) {
            me.optionGrid.dragZone = new Ext.dd.DragZone(v.getEl(), {
                getDragData: function(e) {
                    //Use the DataView's own itemSelector to
                    //test if the mousedown is within one of the DataView's nodes.
                    var sourceEl = e.getTarget(v.itemSelector, 10);

                    //If the mousedown is within a DataView node, clone the node to produce
                    //a ddel element for use by the drag proxy. Also add application data
                    //to the returned data object.
                    if (sourceEl) {
                        var d = sourceEl.cloneNode(true);
                        d.id = Ext.id();

                        var result = {
                            ddel: d,
                            sourceEl: sourceEl,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            sourceStore: v.store,
                            draggedRecord: v.getRecord(sourceEl)
                        };
                        return result;
                    }
                },
                getRepairXY: function() {
                    return this.dragData.repairXY;
                }
            });

            me.optionGrid.dropZone = new Ext.dd.DropZone(me.optionGrid.getEl(), {
                //If the mouse is over a grid row, return that node. This is
                //provided as the "target" parameter in all "onNodeXXXX" node event handling functions
                getTargetFromEvent: function(e) {
                    return e.getTarget(me.optionGrid.view.itemSelector);
                },

                //On entry into a target node, highlight that node.
                onNodeEnter : function(target, dd, e, data){
                    var record = me.optionGrid.view.getRecord(target);
                    if (record !== data.draggedRecord) {
                        Ext.fly(target).addCls(me.dragOverCls);
                    }
                },

                //On exit from a target node, unhighlight that node.
                onNodeOut : function(target, dd, e, data){
                    Ext.fly(target).removeCls(me.dragOverCls);
                },

                //While over a target node, return the default drop allowed class which
                //places a "tick" icon into the drag proxy.
                onNodeOver : function(target, dd, e, data){
                    return (data.draggedRecord instanceof Ext.data.Model);
                },

                //On node drop we can interrogate the target to find the underlying
                //application object that is the real target of the dragged data.
                //In this case, it is a Record in the GridPanel's Store.
                //We can use the data set up by the DragZone's getDragData method to read
                //any data we decided to attach in the DragZone's getDragData method.
                onNodeDrop : function(target, dd, e, data){
                    var record = me.optionGrid.view.getRecord(target);
                    me.fireEvent('optionDropped', data.draggedRecord, record)
                }
            });

        });
    },


    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getOptionPanelSelModel:function () {
        var me = this;
        return Ext.create('Ext.selection.CheckboxModel', {
            checkOnly: true,
            showHeaderCheckbox: false,
            listeners:{
                select:function (sm, record) {
                    me.fireEvent('optionSelect', record, me);
                }
            }
        });
    },

    /**
     * Creates the grouping feature for the attribute option grid.
     * Options the records by the active flag and display "active options" and "inactive options" as option header.
     */
    createOptionPanelGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                '<span>&nbsp;({ rows.length } ' + me.snippets.groups.selected + ')</span>',
                {
                    formatHeader: function(active) {
                        if (active === true || active === 'true') {
                            return me.snippets.options.activeOptions;
                        } else {
                            return me.snippets.options.inactiveOptions;
                        }
                    }
                }
            )
        });
    },

    /**
     * Creates the grid columns for the option grid.
     * @return array
     */
    createOptionGridColumns: function() {
        var me = this;

        return [{
            dataIndex: 'name',
            flex: 2,
            text: me.snippets.options.name,
            translationEditor: {
                xtype: 'textfield',
                allowBlank: false,
                fieldLabel: me.snippets.groups.name,
                name: 'name'
            }
        } , {
            /**
             * Special column type which provides clickable icons in each row
             */
            xtype:'actioncolumn',
            width: 70,
            items:[
                {
                    iconCls:'sprite-minus-circle-frame',
                    action:'deleteOption',
                    tooltip:me.snippets.options.remove,
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteOption', record);
                    }
                }, {
                    iconCls:'sprite-pencil',
                    action:'editOption',
                    tooltip: me.snippets.options.edit,
                    handler:function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('editOption', record);
                    }
                }
            ]
        }];
    },

    /**
     * Creates the toolbar for the option panel
     * @return
     */
    createOptionPanelToolbar: function() {
        var me = this;

        //creates the cycle button for the option panel which contains two options to create or create and activate a option.
        me.createOptionButton = Ext.create('Ext.button.Split', {
            showText: true,
            action : 'activate',
            text: me.snippets.groups.createAndActivate,
            name: 'create-option-button',
            handler: function() {
                me.fireEvent('createOption', me.optionNameField.getValue(), (me.createOptionButton.action === 'activate'));
                me.optionNameField.reset();
            },
            menu: {
                xtype: 'menu',
                items: [{
                    xtype: 'menuitem',
                    text: me.snippets.groups.create,
                    action: 'create',
                    handler: function() {
                        me.createOptionButton.setText(me.snippets.groups.create);
                        me.createOptionButton.action = 'create';
                    }
                }, {
                    xtype: 'menuitem',
                    text: me.snippets.groups.createAndActivate,
                    action : 'activate',
                    handler: function() {
                        me.createOptionButton.setText(me.snippets.groups.createAndActivate);
                        me.createOptionButton.action = 'activate';
                    }
                }]
            }
        });

        //creates the text field to create a configurator option.
        me.optionNameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.options.optionName.label,
            emptyText: me.snippets.options.optionName.empty,
            labelWidth: 155,
            name: 'optionName',
            listeners: {
                specialkey: function(field, event) {
                    if (event.getKey() === event.ENTER) {
                        me.fireEvent('createOption', field.getValue(), (me.createOptionButton.action === 'activate'));
                        field.reset();
                    }
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: [
                me.optionNameField,
                '->',
                me.createOptionButton
            ]
        });
    },

    /**
     * Renderer function of the option count column.
     */
    optionCountColumnRenderer: function(value, metaData, record) {
        var me = this,
            counter = 0,
            info = ' ' + '{s name=variant/configurator/group/options_column_info}Options assigned{/s}';

        if (!record) {
            return 0 + info;
        }
        var options = record.getConfiguratorOptions();

        if (options && options.getCount() > 0) {
            options.each(function(item) {
                 if (item.get('active')) {
                     counter++;
                 }
            });
            return counter + info;
        } else {
            return 0 + info;
        }
    }


});
//{/block}
