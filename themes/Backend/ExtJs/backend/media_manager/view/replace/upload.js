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
 */

//{namespace name=backend/media_manager/view/replace}
//{block name="backend/media_manager/view/replace/row"}
Ext.define('Shopware.apps.MediaManager.view.replace.Upload', {
    extend: 'Ext.container.Container',
    uploadUrl: '{url action=singleReplace controller=MediaManager}',
    layout: 'anchor',
    margin: '10 10 0 10',
    height: 120,

    style: {
        overflow: 'hidden'
    },

    config: {
        maxFileUpload: 1,
        dropZoneText: '{s name="mediaManager/replaceWindow/dropZone/dragAndDrop"}{/s}',
        fileSelectText: '{s name="mediaManager/replaceWindow/dropZone/selectMedia"}{/s}',
        fileUploadErrorMessageTitle: '{s name="mediaManager/replaceWindow/window/errorTitle"}{/s}',
        fileUploadErrorMessage: '{s name="mediaManager/replaceWindow/window/uploadErrorOccurred"}{/s}',
        fileUploadWrongTypeErrorMessage: '{s name="mediaManager/replaceWindow/window/errorWrongMediaType"}{/s}',
        fileUploadExtensionError: '{s name="mediaManager/replaceWindow/window/errorExtensionBlacklisted"}{/s}'
    },

    /**
     * init`s the upload component
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.registerEvents();

        me.callParent(arguments);
    },

    /**
     *  creates the required items and returns them as array
     *
     * @return { Array }
     */
    createItems: function() {
        var me = this;

        return [
            me.createDropZoneContainer(),
            me.createFileUploadField()
        ];
    },

    /**
     * registers required events
     */
    registerEvents: function() {
        var me = this;

        me.dropZone.on('render', Ext.bind(me.initializeDropZone, me));
    },

    /**
     * creates the dropZone container
     *
     * @return { Ext.container.Container }
     */
    createDropZoneContainer: function() {
        var me = this;

        me.dropZone = Ext.create('Ext.container.Container', {
            anchor: '100%',
            border: 1,
            height: 60,
            cls: 'x-container-dropzone',
            tpl: me.createDropZoneContainerTemplate(),
            data: me.config
        });

        return me.dropZone;
    },

    /**
     * creates the dropZone template
     *
     * @return { Ext.XTemplate }
     */
    createDropZoneContainerTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<div class="inner-dropzone">',
            '   <span class="text">',
            '       {dropZoneText}',
            '   </span>',
            '</div>{/literal}'
        );
    },

    /**
     * creates the file input field
     *
     * @return { Shopware.apps.MediaManager.view.replace.FileSelect }
     */
    createFileUploadField: function() {
        var me = this;

        me.fileUploadField = Ext.create('Shopware.apps.MediaManager.view.replace.FileSelect', {
            buttonText: me.config.fileSelectText,
            maxFileUpload: me.config.maxFileUpload,
            listeners: {
                change: Ext.bind(me.onFileUploadFieldChange, me)
            }
        });

        return me.fileUploadField;
    },

    /**
     * init´s the dropZone drag and drop events
     *
     * @param { Ext.Component } component
     */
    initializeDropZone: function(component) {
        var me = this,
            element = component.getEl().dom;

        element.addEventListener('dragenter', Ext.bind(me.onDragEnter, me, [component], true), false);
        element.addEventListener('dragover', Ext.bind(me.onDragOver, me, [component], true), false);
        element.addEventListener('dragleave', Ext.bind(me.onDragLeave, me, [component], true), false);
        element.addEventListener('drop', Ext.bind(me.onDrop, me, [component], true), false);
    },

    /**
     * Event handler of the dropZone dragEnter event
     *
     * @param { Event } event
     * @param { Ext.Component } component
     */
    onDragEnter: function(event, component) {
        var me = this;

        component.getEl().addCls('dropzone-over');

        event.preventDefault();
        event.stopPropagation();

        me.fireEvent('upload-dragEnter', me, component, event);
    },

    /**
     * Event handler of the dropZone dragOver event
     *
     * @param { Event } event
     * @param { Ext.Component } component
     */
    onDragOver: function(event, component) {
        var me = this;

        event.preventDefault();
        event.stopPropagation();

        me.fireEvent('upload-Over', me, component, event);
    },

    /**
     * Event handler of the dropZone dragLeave event
     *
     * @param { Event } event
     * @param { Ext.Component } component
     */
    onDragLeave: function(event, component) {
        var me = this;

        component.getEl().removeCls('dropzone-over');

        event.preventDefault();
        event.stopPropagation();

        me.fireEvent('upload-dragLeave', me, component, event);
    },

    /**
     * Event handler of the dropZone drop event
     *
     * @param { Event } event
     * @param { Ext.Component } component
     */
    onDrop: function(event, component) {
        var me = this;

        event.preventDefault();
        event.stopPropagation();

        if (!event.dataTransfer || !event.dataTransfer.files || event.dataTransfer.files.length == 0) {
            return;
        }

        me.value = event.dataTransfer.files;

        if (!me.validateCount()) {
            me.fireEvent('upload-maximumReached', me);
            return;
        }

        me.fireEvent('upload-changed', me, component, event, me.value);
    },

    /**
     * Event handler for the File element. Sets the own property value and fires the change event
     *
     * @param { Ext.form.field.File } fileInput
     */
    onFileUploadFieldChange: function(fileInput) {
        var me = this;

        me.value = fileInput.fileInputEl.dom.files;

        if (!me.validateCount()) {
            me.fireEvent('upload-maximumReached', me);
            return;
        }

        me.fireEvent('upload-changed', me, fileInput, {}, me.value)
    },

    /**
     * Uploads the file in the current index
     */
    startUpload: function() {
        var me = this;

        if (!me.hasOwnProperty('value') || me.value.length < 1) {
            me.fireEvent('upload-noValue', me);
            return;
        }

        if (!me.hasOwnProperty('index') || me.index == null) {
            me.index = 0;
        }

        me.fireEvent('upload-uploadStart', me, me.value[me.index], me.index);
        me.uploadFile(me.uploadUrl, me.value[me.index], me.mediaId, me.continueUpload, me);
    },

    /**
     * Checks the current index. If index reached fire the uploadReady event, else starts the next file upload
     */
    continueUpload: function() {
        var me = this;

        me.index++;

        if (me.index >= me.value.length) {
            me.index = null;
            me.fireEvent('upload-uploadReady', me);
            return;
        }

        me.startUpload();
    },

    /**
     * creates a form and send it to the server to upload the given file
     *
     * @param { string } url
     * @param { File } file
     * @param { string|number } replaceMediaId
     * @param { function } callback
     * @param { object } scope
     */
    uploadFile: function(url, file, replaceMediaId, callback, scope) {
        var me = this,
            form = new FormData(),
            scope = scope || me,
            request;

        if (!Ext.isFunction(callback)) {
            callback = Ext.emptyFn
        }

        request = me.createRequest(callback, scope);

        form.append('file', file, file.name);
        form.append('mediaId', replaceMediaId);

        request.open('POST', url, true);
        request.setRequestHeader('X-CSRF-Token', Ext.CSRFService.getToken());
        request.send(form);
    },

    /**
     * create a new XMLHttpRequest object
     *
     * @param { function } callback
     * @param { object } scope
     * @return { XMLHttpRequest }
     */
    createRequest: function(callback, scope) {
        var me = this,
            request = new XMLHttpRequest(),
            responseText;

        request.onload = function() {
            if (request.status === 200) {
                try {
                    responseText = Ext.JSON.decode(request.response);
                } catch (exception) {
                    // me.showMessage(me.config.fileUploadErrorMessageTitle, request.response);
                    me.showMessage(me.config.fileUploadErrorMessageTitle, me.config.fileUploadErrorMessage);
                    me.fireEvent('upload-error', me, me.value[me.index], me.index);
                    return;
                }

                if (!responseText.success) {
                    if (responseText.exception) {
                        switch (responseText.exception['_class']) {
                            case 'Shopware\\Bundle\\MediaBundle\\Exception\\WrongMediaTypeForReplaceException':
                                me.showMessage(
                                    me.config.fileUploadErrorMessageTitle,
                                    Ext.String.format(me.config.fileUploadWrongTypeErrorMessage, responseText.exception.requiredType)
                                );
                                break;
                            case 'Shopware\\Bundle\\MediaBundle\\Exception\\MediaFileExtensionIsBlacklistedException':
                            case 'Shopware\\Bundle\\MediaBundle\\Exception\\MediaFileExtensionNotAllowedException':
                                me.showMessage(
                                    me.config.fileUploadErrorMessageTitle,
                                    Ext.String.format(me.config.fileUploadExtensionError, responseText.exception.extension)
                                );
                                break;
                        }
                    } else {
                        me.showMessage(me.config.fileUploadErrorMessageTitle, responseText.message);
                    }

                    me.fireEvent('upload-error', me, me.value[me.index], me.index);
                    return;
                }

                me.fireEvent('upload-fileUploaded', me, me.value[me.index], me.index);
                Ext.callback(callback, scope);

            } else {
                me.index = null;
                me.showMessage(me.config.fileUploadErrorMessageTitle, me.config.fileUploadErrorMessage);
                me.fireEvent('upload-error', me, me.value[me.index], me.index);
            }
        };

        return request;
    },

    /**
     * Returns the current value of the upload component
     *
     * @return { FileList }
     */
    getValue: function() {
        var me = this;

        if (!me.hasOwnProperty('value')) {
            return new FileList();
        }

        return me.value;
    },

    /**
     * validates the value count
     *
     * @return { boolean }
     */
    validateCount: function() {
        var me = this;

        return me.value.length <= me.config.maxFileUpload;
    },

    /**
     * shows a growlMessage
     *
     * @param { string } title
     * @param { string } message
     */
    showMessage: function(title, message) {
        Shopware.Notification.createGrowlMessage(title, message);
    }
});
//{/block}
