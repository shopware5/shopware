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
 * @package    MediaManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/media_manager/view/main}

/**
 * Shopware UI - Media Manager Album Settings
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/media_manager/view/album/setting"}
Ext.define('Shopware.apps.MediaManager.view.album.Setting', {
    extend: 'Enlight.app.SubWindow',
    title: '{s name="albumSettingsTitle"}Album settings{/s}',
    alias: 'widget.mediamanager-album-setting',
    border: false,
    width: 600,
    height: 600,
    layout: 'fit',
    autoShow: true,

    /**
     * Holder property which holds of the album settings.
     *
     * @null
     */
    settings: null,

	snippets:{
		settings:{
			cancel: '{s name="settings/cancel"}Cancel{/s}',
			save: '{s name="settings/save"}Save settings{/s}',
			albumName: '{s name="settings/albumName"}Album name{/s}',
			myAlbum: '{s name="settings/myAlbum"}My album{/s}',
			thumbnails: '{s name="settings/thumbails"}Thumbnails{/s}',
			createThumbnails: '{s name="settings/createThumbnails"}Create thumbnails for this album{/s}',
			createThumb: '{s name="settings/createThumb"}Create thumbnail{/s}',
			thumbSize: '{s name="settings/thumbSize"}Thumbnail size{/s}',
			albumIcon: '{s name="settings/albumIcon"}Album icon{/s}',
			chooseThumb: '{s name="settings/chooseThumb"}Thumbnail configuration{/s}',
			duplicateThumb: '{s name="settings/duplicateThumb"}Duplicate thumbnail{/s}',
			deleteThumb: '{s name="settings/deleteThumb"}Delete thumbnail{/s}',
            highDpiThumbs: '{s name="settings/highDpiThumbs"}High dpi thumbnails{/s}',
            highDpiThumbsHelper: '{s name="settings/highDpiThumbsHelper"}Also generate high dpi versions of thumbnails{/s}',
            thumbQuality: '{s name="settings/thumbQuality"}Thumbnail quality{/s}',
            thumbQualitySupport: '{s name="settings/thumbQualitySupport"}Value between 1 and 100. Higher means more quality but bigger files{/s}',
            highDpiQuality: '{s name="settings/highDpiQuality"}High dpi thumbnail quality{/s}'
		}
	},
    /**
     * Initializes the component and sets the toolbars
     * and the neccessary event listener
     *
     * @return void
     */
    initComponent: function() {
        var me = this,
            id = null;

        // Set the album as the window title
        if(me.settings) {
            me.title = me.title + ' - "' + me.settings.get('text') + '"';
            id = ~~(1* me.settings.get('id'));
        }

        me.items = [ me.createFormPanel() ]

        // Create buttons
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: me.snippets.settings.cancel,
                cls: 'secondary',
                handler: function() {
                    me.close();
                }
            }, {
				text: me.snippets.settings.save,
                cls: 'primary',
                action: 'mediamanager-album-setting-save'
            }]
        }];

        me.formPanel.getForm().loadRecord(me.settings);

        me.callParent(arguments);
    },

    /**
     * Creates the main form panel for the component which
     * features all neccessary form elements
     *
     * @return [object] me.formPnl - generated Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        // Name of the album
        me.albumNameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.settings.albumName,
            emptyText: me.snippets.settings.myAlbum,
            anchor: '100%',
            name: 'text'
        });

        // Create thumbnails for this album
        me.createThumbsField = Ext.create('Ext.form.field.Checkbox', {
            name: 'createThumbnails',
            fieldLabel: me.snippets.settings.thumbnails,
            inputValue: 1,
            listeners: {
                scope: me,
                change: function(value) {
                    if(value.checked) {
                        me.thumbnailSelectionField.show();
                    } else {
                        me.thumbnailSelectionField.hide();
                    }
                }
            },
            uncheckedValue: 0,
            boxLabel: me.snippets.settings.createThumbnails
        });

        // Item selection for this album
        me.iconSelectionField = me.createIconSelection();

        // Thumbnail generation
        me.thumbnailSelectionField = me.createThumbnailSelection();

        // Form panel which holds off all options
        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,

            defaults: {
                labelStyle: 'font-weight: 700; text-align: right;'
            },
            items: [
                me.albumNameField,
                me.createThumbsField,
                me.iconSelectionField,
                me.thumbnailSelectionField
            ]
        });
        return me.formPanel;
    },

    /**
     * Creates a icon selection for the album which will be displayed
     * in the Ext.tree.Panel.
     *
     * @return [object] Ext.form.RadioGroup
     */
    createIconSelection: function() {
        var me = this;
        var icons = [
            { boxLabel: '<span class="sprite-inbox-document-folder"></span>', name: 'iconCls', inputValue: 'sprite-inbox-document-folder' },
            { boxLabel: '<span class="sprite-inbox-document-text"></span>', name: 'iconCls', inputValue: 'sprite-inbox-document-text' },
            { boxLabel: '<span class="sprite-inbox-document-music"></span>', name: 'iconCls', inputValue: 'sprite-inbox-document-music' },
            { boxLabel: '<span class="sprite-inbox-film"></span>', name: 'iconCls', inputValue: 'sprite-inbox-film' },
            { boxLabel: '<span class="sprite-inbox-document-image"></span>', name: 'iconCls', inputValue: 'sprite-inbox-document-image' },
            { boxLabel: '<span class="sprite-images-stack"></span>', name: 'iconCls', inputValue: 'sprite-images-stack' },
            { boxLabel: '<span class="sprite-pictures"></span>', name: 'iconCls', inputValue: 'sprite-pictures' },
            { boxLabel: '<span class="sprite-films"></span>', name: 'iconCls', inputValue: 'sprite-films' },
            { boxLabel: '<span class="sprite-music-beam"></span>', name: 'iconCls', inputValue: 'sprite-music-beam' },
            { boxLabel: '<span class="sprite-blue-document-pdf-text"></span>', name: 'iconCls', inputValue: 'sprite-blue-document-pdf-text' },
            { boxLabel: '<span class="sprite-box"></span>', name: 'iconCls', inputValue: 'sprite-box' },
            { boxLabel: '<span class="sprite-target"></span>', name: 'iconCls', inputValue: 'sprite-target' },
            { boxLabel: '<span class="sprite-globe-green"></span>', name: 'iconCls', inputValue: 'sprite-globe-green' },
            { boxLabel: '<span class="sprite-inbox"></span>', name: 'iconCls', inputValue: 'sprite-inbox' },
            { boxLabel: '<span class="sprite-leaf"></span>', name: 'iconCls', inputValue: 'sprite-leaf' },
            { boxLabel: '<span class="sprite-store"></span>', name: 'iconCls', inputValue: 'sprite-store' },
            { boxLabel: '<span class="sprite-hard-hat"></span>', name: 'iconCls', inputValue: 'sprite-hard-hat' },
            { boxLabel: '<span class="sprite-sd-memory-card"></span>', name: 'iconCls', inputValue: 'sprite-sd-memory-card' }
        ];

        // Iterate each icon to set it active
        Ext.each(icons, function(item) {
            if(item.inputValue == me.settings.get('iconCls')) {
                item.checked = true;
            }
        });

        return Ext.create('Ext.form.RadioGroup', {
            fieldLabel: me.snippets.settings.albumIcon,
            cls: 'icon-selection',
            name: 'iconCls',
            anchor: '100%',
            columns: 6,
            vertical: true,
            items: icons
        });
    },

    /**
     * Create a new fieldset which holds off the
     * thumbnail generation and selection.
     *
     * @return [object] Ext.form.FieldSet
     */
    createThumbnailSelection: function() {
        var me = this;

        me.thumbnailField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.settings.thumbSize,
            name: 'thumbnail-size',
            emptyText: '120x120'
        });

        me.thumbnailSubmit = Ext.create('Ext.button.Button', {
            text: me.snippets.settings.createThumb,
            margin: '0 0 0 6',
            scale: 'small',
            action: 'mediamanager-album-setting-add-thumbnail',
            listeners: {
                scope: me,
                click: me.onAddThumbnail
            }
        });

        me.thumbnailGenerate = Ext.create('Ext.button.Button', {
            text: '{s name=settings/generateThumbBtn}Generate thumbnails{/s}',
            margin: '0 0 0 6',
            scale: 'small',
            action: 'mediamanager-album-setting-generate-thumbnail',
            hidden: Ext.isEmpty(me.settings.get('thumbnailSize')),
            listeners: {
                scope: me,
                click: function(){
                    me.fireEvent('generateThumbnails', me);
                }
            }
        });

        me.thumbnailStore = Ext.create('Ext.data.Store',{
            fields: [ 'id', 'index', 'value' ],
            data: me.settings.data.thumbnailSize
        });

        me.thumbnailView = Ext.create('Ext.view.View', {
            itemSelector: '.thumb-wrap-tiny',
            tpl: me.createThumbnailTemplate(),
            store: me.thumbnailStore,
            listeners: {
                scope: me,
                itemcontextmenu: me.onThumbnailContextMenu
            },
            plugins: [
                Ext.create('Ext.ux.DataView.LabelEditor', { dataIndex: 'value' })
            ]
        });

        me.highDpiThumbsField = Ext.create('Ext.form.field.Checkbox', {
            name: 'thumbnailHighDpi',
            anchor: '100%',
            fieldLabel: me.snippets.settings.highDpiThumbs,
            inputValue: 1,
            uncheckedValue: 0,
            boxLabel: me.snippets.settings.highDpiThumbsHelper,
            listeners: {
                scope: me,
                change: function(value) {
                    if(value.checked) {
                        me.highDpiQuality.show();
                    } else {
                        me.highDpiQuality.hide();
                    }
                }
            }
        });

        me.thumbQuality = Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.settings.thumbQuality,
            name: 'thumbnailQuality',
            supportText: me.snippets.settings.thumbQualitySupport,
            anchor: '100%',
            minValue: 1,
            maxValue: 100
        });

        me.highDpiQuality = Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.settings.highDpiQuality,
            name: 'thumbnailHighDpiQuality',
            supportText: me.snippets.settings.thumbQualitySupport,
            anchor: '100%',
            hidden: (me.settings.get('thumbnailHighDpi')) ? false : true,
            minValue: 1,
            maxValue: 100
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.settings.chooseThumb,
            padding: 12,
            hidden: (me.settings.get('createThumbnails')) ? false : true,
            defaults: {
                labelStyle: 'font-weight: 700; text-align: right;'
            },
            items: [{
                xtype: 'container',
                layout: 'hbox',
                padding: '0 0 8',
                items: [
                    me.thumbnailField,
                    me.thumbnailSubmit,
                    me.thumbnailGenerate,
                ]
            },
                {
                    xtype: 'container',
                    layout: 'anchor',
                    items: [
                        me.thumbQuality,
                        me.highDpiThumbsField,
                        me.highDpiQuality
                    ]
                },
                me.thumbnailView
            ]
        });
    },

    /**
     * Creates a new XTemplate for the thumbnail size listing
     *
     * @return [object] Ext.XTemplate
     */
    createThumbnailTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="thumb-wrap-tiny">',
                '<div class="thumb"><span class="number">{index}</span></div>',
                '<span class="x-editable">{value}</span></div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    },

    /**
     * Event listener method which fires when the user clicks
     * the "add thumbnail"-button.
     *
     * Adds a new thumbnail to the thumbnail store
     *
     * @event click
     * @return void
     */
    onAddThumbnail: function() {
        var me = this,
            size = me.thumbnailField.getValue(),
            store = me.thumbnailStore;

        if (size.length > 0) {
            store.add({
                index: store.count(),
                value: size
            });
            me.thumbnailGenerate.show();
        }
        me.thumbnailField.setValue('');
    },

    /**
     * Event listener method which will be fired when the user
     * right-click the a thumbnail.
     *
     * Opens a context menu.
     *
     * @event itemcontextmenu
     * @param [object] view - View which fires the event Ext.view.View
     * @param [object] record - clicked item Ext.data.Model
     * @param [object] item - HTML DOM Element of the clicked item
     * @param [integer] index - Index of the clicked item in the associated store
     * @param [object] event - fired event Ext.EventObj
     * @return void
     */
    onThumbnailContextMenu: function(view, record, item, index, event) {
        var me = this;
        event.preventDefault(true);

        var menu = Ext.create('Ext.menu.Menu', {
            items: [{
                text: me.snippets.settings.duplicateThumb,
                iconCls: 'sprite-picture--arrow',
                handler: function() {
                    me.onDuplicateThumbnail(record);
                }
            }, {
                text: me.snippets.settings.deleteThumb,
                iconCls: 'sprite-picture--minus',
                handler: function() {
                    me.onDeleteThumbnail(record);
                }
            }]
        });

        menu.showAt(event.getPageX(), event.getPageY());
    },

    /**
     * Event listener method which fires when the user
     * clicks on the "duplicate thumbnail"-button in the
     * thumbnail context menu.
     *
     * Duplicates a thumbnail.
     *
     * @event click
     * @param [object] record - Associated Ext.data.Model
     * @return void.
     */
    onDuplicateThumbnail: function(record) {
        var me = this,
            store = me.thumbnailStore;

        store.add({
            index: store.count(),
            value: record.get('value')
        });
    },

    /**
     * Event listener method which fires when the user
     * clicks on the "delete thumbnail"-button in the
     * thumbnail context menu.
     *
     * Deletes a thumbnail from the store.
     *
     * @event click
     * @param [object] record - Associated Ext.data.Model
     * @return void.
     */
    onDeleteThumbnail: function(record) {
        var me = this,
            store = me.thumbnailStore,
            counter = 0;

        if(store && store.getCount() > 0){
            store.remove(record);

            // Iterate through each entry to change the index
            Ext.each(store.data.items, function(item) {
                item.set('index', counter);
                counter++;
            });
        }

        if(counter === 0){
            me.thumbnailGenerate.hide();
        }
    }
});
//{/block}
