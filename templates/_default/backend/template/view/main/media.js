/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Template
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/template/view/main}
//{block name="backend/template/view/main/media"}
Ext.define('Shopware.apps.Template.view.main.Media', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.template-main-media',
    border: false,
    layout: 'border',
    previewMessage: null,
    enableBtn: null,
    previewBtn: null,

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        panelMoreInformation:  '{s name=panel_more_information}Further information{/s}',

        buttonPreview:    '{s name=button_preview}Preview{/s}',
        buttonEndPreview: '{s name=button_end_preview}End preview{/s}',
        buttonEnable:     '{s name=button_enable}Enable{/s}',

        badgeEnabled:   '{s name=badge_enabled}Enabled{/s}',
        badgePreviewed: '{s name=badge_previewed}Preview{/s}',

        infoAuthor:                '{s name=info_author}Author{/s}',
        infoName:                  '{s name=info_name}Name{/s}',
        infoDescription:           '{s name=info_description}Description{/s}',
        infoLicense:               '{s name=info_license}License{/s}',
        infoEsiCompatible:         '{s name=info_esi_compatible}ESI compatible{/s}',
        infoStyleAssistCompatible: '{s name=info_style_assist_compatible}StyleAssist compatible{/s}',
        infoEmotionsCompatible:    '{s name=info_emotions_compatible}EMOTIONS compatible{/s}',

        previewMessage: "{s name='preview_message'}<h1>Template Preview enabled</h1>You can see the selected template in the storefront.<br>Click to <a href='#' class='end'>end</a> Preview or permanently <a href='#' class='enable'>enable</a> this template.{/s}"
    },


    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * @event enableTemplate
             * @param [object] record
             */
            'enableTemplate',

            /**
             * @event enableTemplate
             * @param [object] record
             */
            'previewTemplate',

            /**
             * @event resetPreview
             */
            'resetPreview'
        );
    },

    /**
     * Initializes the component and sets the neccessary
     * toolbars and items.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.templateStore.load({
            callback: function(records) {
                var record = me.templateStore.findRecord('isEnabled', true);

                me.dataView.select(record);
                if (!record.get('isPreviewed')) {
                    me.previewMessage.show();
                }
            }
        });

        // Create the items of the container
        me.items = [{
            xtype: 'container',
            region: 'center',
            style: 'background: #fff',
            autoScroll: true,
            items: [
                me.createPreviewMessage(),
                me.createMediaView()
            ]
        }];

        me.items.push(me.createInfoPanel());

        me.callParent(arguments);
    },

    /**
     * Creates the message container which displayed on top of the container.
     *
     * @return [object] this.previewMessage - generated Ext.panel.Panel
     */
    createPreviewMessage: function() {
        var me = this;

        me.previewMessage        = Shopware.Notification.createBlockMessage(me.snippets.previewMessage, 'success');
        me.previewMessage.margin = 10;
        me.previewMessage.on('afterrender', function(event) {
            var el = me.getEl();

            el.down('a.end').on('click', function() {
                me.fireEvent('resetPreview');
            });

            el.down('a.enable').on('click', function() {
                var record = me.templateStore.findRecord('isPreviewed', true);
                me.fireEvent('enableTemplate', record);
            });
        });

         return me.previewMessage;
    },

    /**
     * Creates the template for the media view panel
     *
     * @return [object] generated Ext.XTemplate
     */
    createMediaViewTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',

            '<tpl if="isEnabled">',
                '<div class="thumb-wrap enabled" id="{basename}">',
            '<tpl elseif="isPreviewed">',
                '<div class="thumb-wrap previewed" id="{basename}">',
            '<tpl else>',
                '<div class="thumb-wrap" id="{basename}">',
            '</tpl>',

                '<tpl if="isEnabled">',
                    '<div class="hint enabled">' + me.snippets.badgeEnabled + '</div>',
                '<tpl elseif="isPreviewed">',
                    '<div class="hint preview">' + me.snippets.badgePreviewed + '</div>',
                '</tpl>',

                '<div class="thumb">',
                    '<div class="inner-thumb"><img src="{/literal}{link file="templates/"}{literal}{basename}/preview_thb.png" title="{name}" />',
                 '</div>',

                '</div>',
                    '<span class="x-editable">{[Ext.util.Format.ellipsis(values.basename, 20)]}</span>',
                '</div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    },

    /**
     * Creates the media listing based on an Ext.view.View (know as DataView)
     * and binds the "Template"-store to it
     *
     * @return [object] this.dataView - created Ext.view.View
     */
    createMediaView: function() {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            itemSelector: '.thumb-wrap',
            store: me.templateStore,
            cls: Ext.baseCSSPrefix + 'more-info',
            tpl: me.createMediaViewTemplate()
        });

        // Set event listeners for the selection model
        me.dataView.getSelectionModel().on({
            'select': {
                fn: me.onSelectMedia,
                scope: me
            }
        });

        return me.dataView;
    },

    /**
     * Creates the XTemplate for the information panel
     *
     * Note that the template has different member methods
     * which are only callable in the actual template.
     *
     * @return [object] generated Ext.XTemplate
     */
    createInfoPanelTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            '<div class="media-info-pnl">',

            '<div class="thumb">',
            '<div class="inner-thumb"><img src="{/literal}{link file="templates/"}{literal}{basename}/preview_thb.png" title="{name}" /></div>',
            '</div>',

            '<div class="base-info">',
            '<p>',
            '<strong>' + me.snippets.infoName + ':</strong>',
            '<span>{name}</span>',
            '</p>',

            '<tpl if="description">',
            '<p>',
            '<strong>' + me.snippets.infoDescription + ':</strong>',
            '<span>{description}</span>',
            '</p>',
            '</tpl>',

            '<tpl if="author">',
            '<p>',
            '<strong>' + me.snippets.infoAuthor + ':</strong>',
            '<span>{author}</span>',
            '</p>',
            '</tpl>',

            '<tpl if="license">',
            '<p>',
            '<strong>' + me.snippets.infoLicense + ':</strong>',
            '<span>{license}</span>',
            '</p>',
            '</tpl>',

            '<p>',
                '<strong>' + me.snippets.infoEsiCompatible + ':',
                    '<tpl if="isEsiCompatible">',
                        '<span class="sprite-tick-small"  style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '<tpl else>',
                       '<span class="sprite-cross-small" style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '</tpl>',
                '</strong>',
            '</p>',

            '<p>',
                '<strong>' + me.snippets.infoStyleAssistCompatible + ':',
                    '<tpl if="isStyleAssistCompatible">',
                        '<span class="sprite-tick-small"  style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '<tpl else>',
                       '<span class="sprite-cross-small" style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '</tpl>',
                '</strong>',
            '</p>',

            '<p>',
                '<strong>' + me.snippets.infoEmotionsCompatible + ':',
                    '<tpl if="isEmotionsCompatible">',
                        '<span class="sprite-tick-small"  style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '<tpl else>',
                       '<span class="sprite-cross-small" style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                    '</tpl>',
                '</strong>',
            '</p>',

            '</div>',
            '</tpl>{/literal}'
        );
    },

    /**
     * Creates a new panel which displays additional information
     * about the selected media.
     *
     * @return [object] this.infoPanel - generated Ext.panel.Panel
     */
    createInfoPanel: function() {
        var me = this;

        me.infoView = Ext.create('Ext.view.View', {
            cls: 'outer-media-info-pnl',
            tpl: me.createInfoPanelTemplate(),
            region: 'center',
            height: '100%'
        });

        me.infoPanel = Ext.create('Ext.panel.Panel', {
            title: me.snippets.panelMoreInformation,
            layout: 'border',
            cls: Ext.baseCSSPrefix + 'more-info',
            style: 'background: #fff',
            collapsible: true,
            region: 'east',
            autoScroll: true,
            width: 205,
            items: [ me.infoView ],
            bbar: me.createBottomToolbar()
        });

        return me.infoPanel;
    },

    /**
     * Enables/Disables the Preview and Enable buttons
     *
     * @return void
     */
    setupButtonState: function() {
        var me     = this;
        var record = me.dataView.getSelectionModel().getLastSelected();

        if (record.get('isEnabled') && record.get('isPreviewed')) {
            me.enableBtn.setDisabled(true);
            me.previewBtn.setDisabled(true);
        } else {
            me.enableBtn.setDisabled(record.get('isEnabled'));

            if (record.get('isPreviewed')) {
                me.previewBtn.setText(me.snippets.buttonEndPreview);
                me.previewBtn.setDisabled(false);
            } else {
                me.previewBtn.setText(me.snippets.buttonPreview);
                me.previewBtn.setDisabled(false);
            }
        }
    },

    /**
     * Event listener method which fires when the user
     * selects a template in the media view.
     *
     * Updates the information panel on the right hand
     *
     * @event select
     * @param [object] rowModel - Associated Ext.selection.RowModel from the Ext.view.View
     * @return void
     */
    onSelectMedia: function(rowModel) {
        var me     = this,
            record = rowModel.getLastSelected();

        me.setupButtonState();

        if (me.infoView) {
            me.infoView.update(record.data);
        }
    },

    /**
     * Creates the bottom toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createBottomToolbar: function() {
        var me = this;

        me.previewBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.buttonPreview,
            iconCls: 'sprite-eye',
            disabled: true,
            handler: function() {
                var record = me.dataView.getSelectionModel().getLastSelected();
                me.fireEvent('previewTemplate', record);
            }
        });

        me.enableBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.buttonEnable,
            iconCls: 'sprite-tick',
            disabled: true,
            handler: function() {
                var record = me.dataView.getSelectionModel().getLastSelected();
                me.fireEvent('enableTemplate', record);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [ me.previewBtn, '->', me.enableBtn]
        });
    }
});
//{/block}
