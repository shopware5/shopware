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
 * Shopware UI - Media Manager - Media Selection
 *
 * This components provides an easy to use way to select
 * media in the media manager selection and passes the
 * selected media to the module which includes this component.
 *
 * Alternate class names for this class are:
 * - Shopware.form.field.MediaSelection
 * - Shopware.MediaSelection
 *
 * @example Ext.create('Shopware.MediaManager.MediaSelection');
 */
Ext.define('Shopware.MediaManager.MediaSelection',
/** @lends Ext.form.field.Trigger */
{
    extend: 'Ext.form.field.Trigger',
    alternateClassName: [ 'Shopware.form.field.MediaSelection', 'Shopware.MediaSelection' ],
    alias: [ 'widget.mediafield', 'widget.mediaselectionfield' ],
    uses: [ 'Ext.button.Button', 'Ext.Component', 'Ext.layout.component.field.Field' ],
    componentLayout: 'triggerfield',

    /**
     * A standard Ext.button.Button config object.
     * @object
     */
    buttonConfig: null,

    /**
     * The button text to display on the media selection button.
     * @string
     */
    buttonText: '{s name=backend/base/component/media_selection/button_text}Select own files{/s}',

    /**
     * Icon class which will be rendered on the "open media manager" button
     * @string
     */
    buttonIconCls: 'sprite-inbox-image',

    /**
     * True to display the file upload field as a button with no visible text field. If true, all
     * inherited Text members will still be available.
     * @boolean
     */
    buttonOnly: false,

    /**
     * The number of pixels of space reserved between the button and the text field. Note that this only
     * applies if buttonOnly = false
     * @integer
     */
    buttonMargin: 3,

    /**
     * Unlike with other form fields, the readOnly config defaults to true in Media selection field.
     * @boolean
     */
    readOnly: true,

    /**
     * True to allow selection of more than one item at a time, false to allow selection of only a single item at a time or no selection at all
     * @boolean
     */
    multiSelect: true,

    /**
     * The album id property is used to filter the album store of the media manager.
     * @integer
     */
    albumId: null,

    /**
     * Return type
     *
     * If you want to change the return value set the property "valueField" when creating this component.
     *
     * @see Shopware.apps.MediaManager.model.Media for fields
     * @string
     */
    returnValue: 'path',

    /**
     * Property to set the "returnValue".
     * If this property is set to "virtualPath" the property "returnValue" is set to "virtualPath"
     */
    valueField: null,

    /**
     * Initializes the component
     *
     * @private
     * @return void
     */
    onRender: function() {
        var me = this,
            inputEl, buttonWrap, mediaButton;

        if (me.valueField) {
            me.returnValue = me.valueField;
        }

        me.callParent(arguments);

        me.registerEvents();

        if (me.disabled) {
            me.disableItems();
        }

        inputEl = me.inputEl;
        if(me.buttonOnly) {
            inputEl.setDisplayed(false);
        }

        buttonWrap = Ext.get(me.id + '-browseButtonWrap');
        mediaButton = buttonWrap.down('.' + Ext.baseCSSPrefix + 'form-mediamanager-btn');
        buttonWrap.setStyle('width', mediaButton.getWidth() + ((!me.buttonOnly) ? 3 : 0) + 'px');

        // Set the event listener to open up the media selection.
        buttonWrap.on('click', me.onOpenMediaManager, me);
    },

    /**
     * Registers new events on the passed view.
     *
     * @return [boolean]
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(

            /**
             * Fires after the media manager button was rendered.
             *
             * @event renderMediaManagerButton
             * @param [object] me - Shopware.MediaManager.DropZone
             * @param [object] btn - generated Ext.button.Button
             */
            'renderMediaManagerButton',

            /**
             * Fires before the media manager was called.
             *
             * @event beforeOpenMediaManager
             * @param [object] me - Shopware.MediaManager.DropZone
             */
            'beforeOpenMediaManager',

            /**
             * Fires after the media manager was called.
             *
             * @event beforeOpenMediaManager
             * @param [object] me - Shopware.MediaManager.DropZone
             */
            'afterOpenMediaManager',

            /**
             * Fires after the user selects one or media in the media manager
             * and presses the "apply selection"-button in the media manager.
             *
             * @event selectMedia
             * @param [object] me - Shopware.MediaManager.DropZone
             * @param [array] selected - Array of the selected Ext.data.Model's
             * @param [object] selModel - Associated Ext.selection.Model
             */
            'selectMedia'
        );

        return true;
    },

    /**
     * Returns the selected records from the media manager or false
     * if the selection wasn't performed.
     *
     * @return [array|false] Array of the selected records e.g. Ext.data.Model's
     */
    getRecords: function() {
        if(!this.selectedRecords) {
            return false;
        }

        return this.selectedRecords;
    },

    /**
     * Returns the quantity of the selected records.
     *
     * @return [integer]
     */
    getRecordsCount: function() {
        if(!this.selectedRecords) {
            return 0;
        }

        return this.selectedRecords.length;
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "open media manager" button.
     * Fires the events "beforeOpenMediaManager" and "afterOpenMediaManager".
     *
     * Opens the media manger with the neccessary parameters.
     *
     * @return void
     */
    onOpenMediaManager: function() {
        var me = this;

        me.fireEvent('beforeOpenMediaManager', me);
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.MediaManager',
            layout: 'small',
            eventScope: me,
            params: {
                albumId: me.albumId
            },
            mediaSelectionCallback: me.onGetSelection,
            selectionMode: me.multiSelect,
            validTypes: me.validTypes || []
        });
        me.fireEvent('afterOpenMediaManager', me);
    },

    /**
     * Event listener method which will fired when the user
     * clicks in the media manager on the "apply selection" button.
     * Fires the "selectMedia" event
     *
     * Determines the selected media and returns the associated
     * Ext.data.Model's.
     *
     * @param [object] btn - pressed Ext.button.Button
     */
    onGetSelection: function(btn) {
        var me = this,
            win = btn.up('window'),
            dataPnl = win.down('.mediamanager-media-view'),
            selModel, selected;

        if(dataPnl.selectedLayout === 'grid') {
            dataPnl = dataPnl.dataView;
        } else {
            dataPnl = dataPnl.cardContainer.getLayout().getActiveItem();
        }

        selModel = dataPnl.getSelectionModel();
        selected = selModel.getSelection();

        me.selectedRecords = selected;
        me.fireEvent('selectMedia', me, me.selectedRecords, selModel);

        if(me.selectedRecords.length > 1) {

            // Multi selection
            var paths = [];
            Ext.each(me.selectedRecords, function(record) {
                paths.push(record.get(me.returnValue));
            });

            paths = paths.toString();
            me.inputEl.dom.value = paths;
        } else {

            // Single selection
            selected = me.selectedRecords[0];
            me.inputEl.dom.value = selected.get(me.returnValue);
        }

//        me.window.setLoading(false);
        win.close();
    },

    /**
     * Event listener method which destroyes the component
     *
     * @return void
     */
    onDestroy: function(){
        Ext.destroyMembers(this, 'button');
        this.callParent();
    },

    /**
     * Event listener method which enables the button.
     *
     * @return void
     */
    onEnable: function(){
        var me = this;
        me.callParent();
        //me.button.enable();
    },

    /**
     * Event listener method which disables the button.
     *
     * @return void
     */
    onDisable: function(){
        this.callParent();
        this.disableItems();
    },

//    /**
//     * Overridden to do nothing
//     *
//     * @private
//     */
//    setValue: Ext.emptyFn,

    /**
     * Resets the input field
     *
     * @return void
     */
    reset : function(){
        var me = this;

        if (me.rendered) {
            me.inputEl.dom.value = '';
        }
        me.callParent();
    },

    /**
     * Disables the button.
     *
     * @return void
     */
    disableItems: function(){
        var button = this.button;

        if (button) {
            button.disable();
        }
    },

    /**
     * Gets the markup to be inserted into the subTplMarkup.
     *
     * @return [string] result - DOM markup
     */
    getTriggerMarkup: function() {
        var me = this,
            result,
            btn = Ext.widget('button', Ext.apply({
                preventDefault: false,
                cls: Ext.baseCSSPrefix + 'form-mediamanager-btn small secondary',
                style: (me.buttonOnly) ? '' : 'margin-left:' + me.buttonMargin + 'px',
                text: me.buttonText,
                iconCls: me.buttonIconCls
            }, me.buttonConfig)),
            btnCfg = btn.getRenderTree();

        me.fireEvent('renderMediaManagerButton', me, btn);

        result = '<td id="' + me.id + '-browseButtonWrap">' + Ext.DomHelper.markup(btnCfg) + '</td>';

        btn.destroy();
        return result;
    }
});
