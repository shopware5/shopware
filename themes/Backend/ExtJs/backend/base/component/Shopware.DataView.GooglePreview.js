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
 * Shopware UI - Window
 *
 * This class provides a google preview data view.
 * You can add fields to the component which values will be automatically be refactored.
 * So you will see a preview of googles search entry of this page.
 *
 * @example
 {
     xtype:'googlepreview',
     fieldSetTitle: 'Preview',
     viewData: me.detailRecord,
     titleField: me.titleField,
     fallBackTitleField: me.mainTitleField,
     descriptionField: me.metaDescription,
     supportText: 'This preview displayed can differ from the version shown in the search engine.',
     refreshButtonText: 'Generate Preview'
 }
 */
Ext.define('Shopware.DataView.GooglePreview',
{
    /**
     * Based on Ext.panel.Panel
     */
    extend:'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @array
     */
    alias: [ 'widget.googlepreview'],

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'dataview-google-preview',


    /**
     * the fieldSet in which the preview will be shown
     */
    fieldSet: null,

    /**
     * the fieldSet title
     */
    fieldSetTitle: '',

    /**
     * the field to display the value in the preview
     */
    titleField: null,

    /**
     * the field to display the value in the preview if the titleField is empty
     */
    fallBackTitleField: null,

    /**
     * support text under the preview
     */
    supportText:'',

    /**
     * The button text
     */
    refreshButtonText: 'Refresh',

    /**
     * standard layout
     */
    layout:{
        type:'vbox'
    },

    /**
     * Init the component
     */
    initComponent : function() {
        var me = this;
        me.fieldSet = Ext.create('Ext.form.FieldSet', {
            layout:'anchor',
            padding:10,
            width: '100%',
            title: me.fieldSetTitle,
            items: [ me.createPreviewView() ]
        });

        me.refreshButton = Ext.create('Ext.Button', {
            text: me.refreshButtonText,
            cls: 'primary',
            scope:me,
            handler:function () {
                me.refreshView();
            }
        });

        me.supportTextContainer = Ext.create('Ext.container.Container', {
            style: 'font-style: italic; color: #999; font-size: x-small; margin: 0 0 8px 0;',
            width: '100%',
            html: me.supportText
        });

        // Create the view
        me.items = [
            me.fieldSet,
            me.supportTextContainer,
            me.refreshButton
        ];

        me.callParent(arguments);
    },


    createPreviewView: function() {
        var me = this;

        me.previewView = Ext.create('Ext.view.View', {
            itemSelector: '.preview',
            name: 'google-preview-view',
            tpl: me.createPreviewTemplate()
        });


        return me.previewView;
    },

    /**
     * Creates the template for preview fieldset
     *
     * @return [object] generated Ext.XTemplate
     */
    createPreviewTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '{literal}' +
            '<tpl for=".">',
                '<div class="preview">',
                    '<strong class="title">{title}</strong>',
                    '<span class="url">{url}</span>',
                    '<div class="desc">',
                        '<span class="date">{date}</span>',
                        '{metaDescription}',
                    '</div>',
                '</div>',
            '</tpl>',
            '{/literal}'
        );
    },

    /**
     * Returns the required data for the preview
     * @return { Object }
     */
    getPreviewData: function() {
        var me = this,
            title = me.titleField.getValue() ? me.titleField.getValue() : me.fallBackTitleField.getValue(),
            url = title,
            metaDescription = me.descriptionField.getValue(),
            date = '';

            if(title != '') {
                title = title.substr(0,50)+'...';
                date = new Date().toLocaleDateString();
                date = date+" - ";
            }
            if(url != '') {
                url = "www.example.com/"+url;
                url = url.substr(0,35)+'...';
                url = url.toLowerCase();
                url = url.replace(/\s/g, '-');
            }
            if(metaDescription != '') {
                metaDescription = metaDescription.substr(0,70)+'...';
            }

        return {
            title: title,
            url: url,
            date: date,
            metaDescription: metaDescription
        };
    },

    /**
     * Refreshes the view
     * Will be called if someone clicks the refresh button
     *
     * @public
     * @return void
     */
    refreshView: function() {
        var me = this;
        me.previewView.update(me.getPreviewData());
    },

    /**
     * Destroys the DragAndDropSelector panel
     *
     * @public
     * @return void
     */
    destroy: function() {
        Ext.destroyMembers(this);
        this.callParent();
    }
});
