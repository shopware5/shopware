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

/**
 * Shopware UI - Media Manager Media View
 *
 * This file contains the thumbnail generation window and its elements.
 * The main generation logic is contained in the thumbnail controller.
 *
 * @category    Shopware
 * @package     MediaManager
 * @copyright   Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/media_manager/view/main}

//{block name="backend/media_manager/view/main/thumbnails"}
Ext.define('Shopware.apps.MediaManager.view.thumbnail.Main', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.SubWindow',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.mediamanager-thumbnail-main',

    /**
     * Define window width
     * @integer
     */
    width: 360,

    /**
     * Define window height
     * @integer
     */
    height: 160,

    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton: false,

    /**
     * Set vbox layout and stretch align to display the toolbar on top and the button container
     * under the toolbar.
     * @object
     */
    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    /**
     * If the modal property is set to true, the user can't change the window focus to another window.
     * @boolean
     */
    modal: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Disable the close icon in the window header
     * @boolean
     */
    closable: false,

    /**
     * Disable window resize
     * @boolean
     */
    resizable: false,

    /**
     * Disables the maximize button in the window header
     * @boolean
     */
    maximizable: false,
    /**
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        batch: {
            label: '{s name=thumbnail/batch/label}Batch size{/s}',
            help: '{s name=thumbnail/batch/help}How many records should be processed per request? Default: 30{/s}',
            cancel: '{s name=thumbnail/batch/cancel}Cancel process{/s}',
            start: '{s name=thumbnail/batch/start}Start process{/s}',
            close: '{s name=thumbnail/batch/close}Close window{/s}',
            process: '{s name=thumbnail/batch/thumbnails}Creating thumbnails for [0]/[1] images{/s}'
        }
    },

    /**
     * The title shown in the window header
     */
    title: '{s name=thumbnail/batch/title}Create thumbnails{/s}',

    /**
     * The default generation batch size
     */
    batchSize: 20,

    /**
     * Constructor for the generation window
     * Registers events and adds all needed content items to the window
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();
        me.items = me.createItems();
        me.callParent(arguments);
    },

    /**
     * Helper function to create the window items.
     */
    createItems: function () {
        var me = this;

        me.thumbnailProgress = me.createProgressBar('thumbnails', 'Thumbnails ...');

        return [
            me.thumbnailProgress,
            me.createBatchSizeCombo(),
            me.createButtons()
        ];
    },

    /**
     * Registers events in the event bus for firing events when needed
     */
    registerEvents: function () {
        this.addEvents(
            'startProcess',
            'cancelProcess'
        );
    },

    /**
     * Creates and returns a new combo box with all available batch sizes for the generation process
     *
     * @returns [object]
     */
    createBatchSizeCombo: function () {
        var me = this;

        me.batchSizeCombo = Ext.create('Ext.form.ComboBox', {
            fieldLabel: me.snippets.batch.label,
            helpText: me.snippets.batch.help,
            name: 'batchSize',
            margin: '0 0 10 0',
            allowBlank: false,
            value: me.batchSize,
            editable: true,
            displayField: 'batchSize',
            store: Ext.create('Ext.data.Store', {
                fields: [
                    { name: 'batchSize', type: 'int' }
                ],
                data: [
                    { batchSize: '1' },
                    { batchSize: '5' },
                    { batchSize: '10' },
                    { batchSize: '20' },
                    { batchSize: '30' },
                    { batchSize: '50' },
                    { batchSize: '75' },
                    { batchSize: '100' }
                ]
            })
        });

        return me.batchSizeCombo;
    },

    /**
     * Returns a new progress bar for a detailed view of the generation progress status
     *
     * @param name
     * @param text
     * @returns [object]
     */
    createProgressBar: function (name, text) {
        return Ext.create('Ext.ProgressBar', {
            animate: true,
            name: name,
            text: text,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls: 'left-align'
        });
    },

    /**
     * Returns a new start button for the generation process
     *
     * @returns [object]
     */
    createStartButton: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.batch.start,
            cls: 'primary',
            action: 'start',
            handler: function () {
                me.fireEvent('startProcess', me, this);
            }
        });
    },

    /**
     * Returns a new cancel button for the generation process
     *
     * @returns [object]
     */
    createCancelButton: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.batch.cancel,
            cls: 'primary',
            action: 'cancel',
            disabled: false,
            hidden: true,
            handler: function () {
                me.fireEvent('cancelProcess', this);
            }
        });
    },

    /**
     * Returns a new close button for the generation process window
     *
     * @returns [object]
     */
    createCloseButton: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.batch.close,
            flex: 1,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function () {
                me.fireEvent('closeWindow');
                me.destroy();
            }
        });
    },

    /**
     * Returns a container with all generation buttons
     * The cancel button is hidden by default
     *
     * @returns [object]
     */
    createButtons: function () {
        var me = this;

        me.startButton = me.createStartButton();
        me.closeButton = me.createCloseButton();
        me.cancelButton = me.createCancelButton();

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.startButton,
                me.cancelButton,
                me.closeButton
            ]
        });
    }
});
//{/block}
