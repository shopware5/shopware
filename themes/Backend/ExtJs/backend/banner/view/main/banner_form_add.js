/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Banner
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name="backend/banner/view/main"}*/

/**
 * Shopware UI - Banner View Main Add
 *
 * View component which features a form panel to add
 * a new banner.
 */
//{block name="backend/banner/view/main/banner_form_add"}
Ext.define('Shopware.apps.Banner.view.main.BannerFormAdd', {
    extend: 'Enlight.app.Window',
    alias: 'widget.BannerFormAdd',
    cls: 'addWindow',
    autoShow: true,
    border: 0,
    title: '{s name="form_add/title"}Create a new banner for category: [0]{/s}',
    height: 510,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    /**
     * Initializes the component by setting the form panel and the buttons
     *
     * @return void
     */
    initComponent: function() {
        var me      = this;
        me.title = Ext.String.format(this.title, this.category.get('text'));
        me.items    = me.createFormPanel();
        me.dockedItems = [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: me.createActionButtons()
        }];
        me.callParent(arguments);
        me.record.set('categoryId', me.category.get('id'));

        me.formPanel.loadRecord(me.record);
        me.linkTarget.setValue('_blank');
    },

    /**
     * Creates the main form panel for this component.
     *
     * @return [object] generated Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this,
            descField, linkField, validFrom, validUntil, dropZone;

        // Description field
        descField = Ext.create('Ext.form.field.Text', {
            name: 'description',
            anchor: '100%',
            allowBlank: false,
            labelWidth: 155,
            fieldLabel: '{s name="form_add/description"}Description{/s}',
            supportText: '{s name="form_add/description_support"}Description of the banner e.g. Jackets-Winter-Special2013{/s}'
        });

        // Link field
        linkField = Ext.create('Ext.form.field.Text', {
            name: 'link',
            anchor: '100%',
            labelWidth: 155,
            fieldLabel: '{s name="form_add/link"}Link{/s}',
            supportText: '{s name="form_add/link_support"}Link which will be called up if the banner has been clicked.{/s}'
        });

        var store = Ext.create('Ext.data.Store', {
            fields: ['id', 'value', 'display'],
            data: [
                { value: '_blank', display: '{s name="form_add/link_target/external"}External{/s}' },
                { value: '_parent', display: '{s name="form_add/link_target/internal"}Shopware{/s}' }
            ]
        });

        me.linkTarget = Ext.create('Ext.form.field.ComboBox', {
            name: 'linkTarget',
            fieldLabel: '{s name="form_add/link_target/field"}Link target{/s}',
            store: store,
            valueField: 'value',
            labelWidth: 155,
            displayField: 'display',
            editable: false
        });

        // Get timing containers
        validFrom   = me.createValidFromContainer();
        validUntil  = me.createValidUntilContainer();

        // Media selection field
        dropZone = Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel: '{s name="form_add/banner"}Banner{/s}',
            labelWidth: 155,
            name: 'media-manager-selection',
            supportText: '{s name="form_add/banner_support"}Banner image selection via the Media Manager. The selection is limited to one media.{/s}',
            multiSelect: false,
            anchor: '100%',
            allowBlank: false,
            validTypes: me.getAllowedExtensions(),
            validTypeErrorFunction: me.getExtensionErrorCallback()
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_emarketing_banners_attributes',
            disabled: false
        });

        // Actual form panel
        me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            layout: 'anchor',
            bodyPadding: 10,
            flex: 1,
            autoScroll: true,
            defaults: { anchor: '100%' },
            items: [ descField, linkField, me.linkTarget, validFrom, validUntil, dropZone, me.attributeForm ]
        });
        me.formPanel.add(me.createHiddenFields());

        return me.formPanel;
    },
    /**
     * Returns the method which should be called if some select a file with a wrong extension.
     *
     * @return string
     */
    getExtensionErrorCallback: function() {
        return 'onExtensionError';
    },

    onExtensionError: function() {
        Shopware.Msg.createGrowlMessage('', '{s name="extension_error"}Incorrect MIME Type{/s}', '{s name="main_title"}{/s}');
    },

    /**
     * Method to set the allowed file extension for the media manager
     * @return []
     */
    getAllowedExtensions: function() {
        return [ 'gif', 'png', 'jpeg', 'jpg', 'svg' ]
    },

    /**
     * Creates the neccessary hidden fields which transports
     * all needed informations
     *
     * @return [array] generated hidden form elements
     */
    createHiddenFields: function() {
        var me = this;

        return [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'hidden',
            name: 'image'
        }, {
            xtype: 'hidden',
            name: 'categoryId',
            value: me.categoryId
        }];
    },

    /**
     * Creates a container which includes the "valid from" field
     *
     * @return [object] generated Ext.container.Container
     */
    createValidFromContainer: function() {
        var me = this;

        me.validFromField = Ext.create('Ext.form.field.Date', {
            submitFormat: 'd.m.Y',
            fieldLabel: '{s name="form_add/from_label"}Active from{/s}',
            name: 'validFromDate',
            supportText: '{s name="form_add/from_support"}Format: dd.mm.jjjj{/s}',
            columnWidth: .6,
            labelWidth: 155,
            minValue: new Date(),
            value: new Date(),
            allowBlank: true,
            listeners: {
                change: function(field, newValue) {
                    me.validToField.setMinValue(newValue);
                }
            }
        }
        );

        return Ext.create('Ext.container.Container', {
            layout: 'column',
            anchor: '100%',
            items: [
                , me.validFromField,
                {
                    margin: '0 0 0 10',
                    submitFormat: 'H:i',
                    xtype: 'timefield',
                    name: 'validFromTime',
                    supportText: '{s name="form_add/from_time_support"}Format: hh:mm{/s}',
                    columnWidth: .4,
                    minDate: new Date()
                }]
        })
    },

    /**
     * Creates a container which includes the "valid until" field
     *
     * @return [object] generated Ext.container.Container
     */
    createValidUntilContainer: function() {
        var me = this;

        me.validToField = Ext.create('Ext.form.field.Date', {
            submitFormat: 'd.m.Y',
            fieldLabel: '{s name="form_add/to_date_label"}Active till{/s}',
            name: 'validToDate',
            labelWidth: 155,
            supportText: '{s name="form_add/to_date_support"}Format jjjj.mm.tt{/s}',
            columnWidth: .60,
            allowBlank: true,
            listeners: {
                change: function(field, newValue) {
                    me.validFromField.setMaxValue(newValue);
                }
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: 'column',
            anchor: '100%',
            items: [
                me.validToField,
                {
                    margin: '0 0 0 10',
                    xtype: 'timefield',
                    name: 'validToTime',
                    submitFormat: 'H:i',
                    supportText: '{s name="form_add/to_time_support"}Format: hh:mm{/s}',
                    columnWidth: .40
                }]
        })
    },

    /**
     * Creates the action buttons for the component.
     *
     * @return [array] - Array of Ext.button.Button's
     */
    createActionButtons: function() {
        var me = this;

        return ['->', {
            text: '{s name="form_add/cancel"}Cancel{/s}',
            cls: 'secondary',
            scope: me,
            handler: me.destroy
        }, {
            text: '{s name="form_add/save"}Save{/s}',
            action: 'addBannerSave',
            cls: 'primary'
        }];
    }
});
//{/block}
