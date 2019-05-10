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
  * Ext.util.FileUpload
  *
  * This components provides an HTML5 file upload with
  * a preview image and a progress bar.
  */
 // {namespace name=backend/component/file_upload}
Ext.define('Ext.util.FileUpload', {
    extend: 'Ext.container.Container',
    alternateClassName: [ 'Ext.FileUpload', 'Shopware.app.FileUpload' ],
    alias: 'widget.html5fileupload',
    padding: 20,

    /**
     * Request URL where the AJAX
     * request will be sended.
     *
     * @example requestURL: '{url controller="index" action="test"}'
     *
     * @string
     */
    requestURL: null,

    /**
     * Checks for the file reader api
     *
     * @boolean
     */
    supportsFileAPI: !!window.FileReader,

    /**
     * Renders a file input
     *
     * @boolean
     */
    showInput: true,

    /**
     * Checks the files amount
     *
     * @boolean
     */
    checkAmount: true,

    /**
     * Max file upload. Will be ignored if
     * checkAmount is set to "false"
     * Default: 3
     *
     * @integer
     */
    maxAmount: 3,

    /**
     * Callback function which will be called when the
     * user reaches the max amount limit.
     *
     * @function
     */
    maxAmountErrorFunction: Ext.emptyFn,

    /**
     * Checks the file types.
     *
     * @boolean
     */
    checkType: true,

    /**
     * Supported file extensions. Will be ignored if
     * checkType is set to "false"
     *
     * @array
     */
    validTypes: [
        'gif', 'png', 'tiff',
        'jpeg', 'jpg', 'jpe',
        'rar', 'zip', 'tar'
    ],

    /**
     * Callback function which will be called when the
     * user uploads an unsupported file type
     *
     * @function
     */
    validTypeErrorFunction: Ext.emptyFn,

    /**
     * Checks file size
     *
     * @boolean
     */
    checkSize: false,

    /**
     * Max file size (in byte). Will be ignored if
     * checkSize is set to "false"
     * Default: 1048576 bytes (= 1 megabyte)
     *
     * @integer
     */
    maxSize: 2097152,

    /**
     * Callback function which will be called when the
     * user uploads an file which exceed the max file size
     *
     * @function
     */
    maxSizeErrorFunction: Ext.emptyFn,

    afterUploadFunction: Ext.emptyFn,

    /**
     * Configuration object for the file input
     *
     * @object
     */
    fileInputConfig: {
        name: 'images[]',
        fieldLabel: '{s name=file_upload/choose_image}Choose image(s){/s}',
        buttonText: '{s name=file_upload/select_image}Select image(s){/s}',
        labelStyle: 'font-weight: 700',
        labelWidth: 125,
        allowBlank: true,
        width: 450,
        buttonConfig: {
	        cls: 'secondary small',
	        iconCls: 'sprite-inbox-image'
        }
    },

    inputConfig: {},

    /**
     * Default class name for the drop zone
     *
     * @string
     */
    dropZoneCls: '-dropzone',

    /**
     * Class name for the drop zone which will
     * be set when the user hovers over the
     * zone.
     *
     * @string
     */
    dropZoneOverCls: 'dropzone-over',

    /**
     * Class name for the drop zone which will
     * be set when the user drops an element
     * on the drop zone.
     *
     * @string
     */
    dropZoneDropCls: 'dropzone-drop',

    /**
     * Default text for the drop zone
     *
     * @string
     */
    dropZoneText: '{s name=file_upload/drag_info}or drag and drop files here{/s}',

    /**
     * Class name for the rendered drop item
     *
     * @string
     */
    dropItemCls: 'dropzone-item',

    /**
     * Class name for the rendered preview image container:
     */
    dropItemImageCls: 'dropzone-item-image',

    /**
     * Class name for the rendered drop item info box
     *
     * @string
     */
    dropItemInfoCls: 'dropzone-item-info',

    /**
     * Template for the information item
     *
     * @array
     */
    dropItemTpl: [
        '{literal}<div class="{infoCls}">',
        '<p><strong>Name:</strong> {name:ellipsis(30)}</p>',
        '<p><strong>Gr&ouml;&szlig;e:</strong> {size}kB</p>',
        '</div>{/literal}'
    ],

    /**
     * Clas name for the progress bar
     *
     * @string
     */
    progressBarCls: 'dropzone-item-progress-bar',

    /**
     * Template for the progress bar
     *
     * @array
     */
    progressBarTpl: [
        '{literal}<span style="width: {percent}%"></span>{/literal}'
    ],

    /**
     * Handles the hint text status
     *
     * @private
     * @boolean
     */
    initial: true,

    /**
     * Key for the uploaded file
     */
    fileField: 'fileId',

    /**
     * If set to true, the file upload component creates an preview image while uploading the files.
     * Default: false
     */
    enablePreviewImage: true,

    /**
     * CSS Class which indicates that a preview will be rendered.
     *
     * @string
     */
    previewEnabledCls: 'preview-image-displayed',

    /**
     * Indicates if the need to render a notice into the drop zone for
     * legacy browsers.
     *
     * @default false
     * @boolean
     */
    showLegacyBrowserNotice: false,

    /**
     * Truthy to hide the whole input field. Falsy to show it.
     */
    hideOnLegacy: false,

    /**
     * Snippet for this component.
     *
     * @object
     */
    snippets: {
        uploadReady: '{s name=file_upload/upload_ready_info}files uploaded{/s}',
        filesFrom: '{s name=file_upload/progress_bar_text}from{/s}',
        messageText: '{s name=file_upload/upload_ready_message}[0] files uploaded{/s}',
        messageTitle: '{s name=file_upload/upload_ready_title}Media manager{/s}',
        legacyMessage: '{s name=file_upload/legacy_message}Your browser doesn\'t support the necessary feature to support drag &drop uploads.{/s}',
        maxUploadSizeTitle: '{s name=file_upload/max_upload_size_title}The file exceeds the file size limit{/s}',
        maxUploadSizeText: '{s name=file_upload/max_upload_size_text}The selected file exceeds the configured maximum file size for uploads. Please select another file to upload.{/s}',
        extensionNotAllowedTitle: '{s name=file_upload/extension_not_allowed_title}File extension not allowed{/s}',
        extensionNotAllowedText: '{s name=file_upload/extension_not_allowed_title}The selected file extension is not allowed. Please select another file to upload.{/s}',
        blackListTitle: '{s name=file_upload/black_list_title}Blacklist{/s}',
        blackListMessage: "{s name=file_upload/black_list_message}File extension [0] isn't allowed{/s}"

    },
    /**
     * Initializes the component, binds the necessary event listeners and
     * creates the drop zone
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.items = me.items || [];

        // Check if the request url is set
        if (!me.requestURL) {
            Ext.Error.raise(me.$className + ' needs the property "requestURL"');
        }

        // Checking if the browser supports the File and FileReader API
        if (me.supportsFileAPI) {
            // Create the drop zone
            me.dropZone = me.createDropZone();
            me.items.push(me.dropZone);
        } else {
            // Force the browser to display the input field
            me.showInput = true;
            // me.showLegacyBrowserNotice = true;
            me.enablePreviewImage = false;

            if (me.hideOnLegacy) {
                me.showInput = false;
                me.showLegacyBrowserNotice = false;
                me.hidden = true;
            }
        }

        // Create the file input field if necessary
        if (me.showInput) {
        	if (me.showLegacyBrowserNotice) {
	            me.fileInputConfig.supportText = me.snippets.legacyMessage;
        }

            me.fileInput = me.createFileInputField();
            me.items.push(me.fileInput);
        }

        /**
         * Add generic function to display an error if the
         * file excesses the configured file size limit.
         */
        if (me.maxSizeErrorFunction == Ext.emptyFn) {
            me.maxSizeErrorFunction = me.maxSizeErrorCallback;
        }

        // event will be fired when one file uploaded.
        me.addEvents('fileUploaded', 'uploadReady', 'uploadFailed');
        me.callParent(arguments);
    },

    /**
     * Creates the drop zone and all used events
     *
     * @return [object]
     */
    createDropZone: function () {
        var me = this, dropZone, text;

        text = Ext.create('Ext.Component', {
//            renderTpl: me.dropZoneTpl,
            renderTpl: me.createDropZoneTemplate(),
            renderData: {
                text: me.dropZoneText
            }
        });

        dropZone = Ext.create('Ext.container.Container', {
            focusable: false,
            cls: me.baseCls + me.dropZoneCls,
            items: [ text ]
        });

        me.on('afterrender', me.createDropZoneEvents, me);

        return dropZone;
    },

    /**
     * Callback method if the file is bigger than the max upload size.
     *
     * @private
     * @return vpid
     */
    maxSizeErrorCallback: function() {
        var me = this;

        // Throw alert box
        Ext.Msg.alert(me.snippets.maxUploadSizeTitle, me.snippets.maxUploadSizeText);

        // Create the drop zone
        me.dropZone.removeAll();

        // create default drop zone
        var text = Ext.create('Ext.Component', {
            renderTpl: me.createDropZoneTemplate(),
            tpl: me.createDropZoneTemplate(),
            renderData: {
                text: me.dropZoneText
            }
        });
        me.dropZone.add(text);
        me.fireEvent('uploadReady');
    },

    /**
     * Creates the template for the drop zone.
     *
     * @public
     * @return [object] Ext.XTemplate
     */
    createDropZoneTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<div class="inner-dropzone">',
                '<span class="text">',
                    '<tpl if="actualQuantity">',
                        '{actualQuantity} ' + me.snippets.filesFrom + ' {totalQuantity}&nbsp;',
                    '</tpl>',
                    '{text}',
                '</span>',
            '</div>{/literal}'
        );
    },

    /**
     * Creates the necessary events for the drop zone
     *
     * @return void
     */
    createDropZoneEvents: function () {
        var me = this, el = me.dropZone.getEl().dom;

        el.addEventListener('dragenter', function (event) {
            me.dropZone.getEl().addCls(me.dropZoneOverCls);
            event.preventDefault();
            event.stopPropagation();
        }, false);

        el.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.stopPropagation();
        }, false);

        el.addEventListener('dragleave', function (event) {
            var target = event.target;

            if (target && target === el) {
                me.dropZone.getEl().removeCls(me.dropZoneOverCls);
            }
            event.preventDefault();
            event.stopPropagation();
        }, false);

        el.addEventListener('drop', function (event) {
            var dropEl = me.dropZone.getEl(), files;

            event.preventDefault();
            event.stopPropagation();

            // Handle the element classes
            if (dropEl.hasCls(me.dropZoneOverCls)) {
                dropEl.removeCls(me.dropZoneOverCls);
            }
            dropEl.addCls(me.dropZoneDropCls);

            if (event.dataTransfer && event.dataTransfer.files) {
                files = event.dataTransfer.files;
            }
            me.iterateFiles(files);
        }, false);
    },

    /**
     * Creates an multiple file input for the upload component
     *
     * @return [object] file
     */
    createFileInputField: function () {
        var me = this, file, el, ret;

        var config = Ext.apply(me.inputConfig, me.fileInputConfig);
        config.name = me.fileField;
        file = me.inputFileCmp = Ext.create('Ext.form.field.File', config);
        ret = file;

        // Add "multiple" attribute to the file input field
        file.on('afterrender', function () {
            el = file.fileInputEl.dom;

            if (!me.showLegacyBrowserNotice) {
                el.setAttribute('multiple', 'multiple');
                el.setAttribute('size', '5');
            }
        }, me);

        if (Ext.isIE || Ext.isSafari) {
	        me.form = Ext.create('Ext.form.Panel', {
		        unstyled: true,
		        layout: 'anchor',
		        url: me.requestURL,
		        items: [ file ]
	        });

	        ret = me.form;
        }

        file.on('change', function(field) {
            var fileField = field.getEl().down('input[type=file]').dom;

            if (Ext.isIE || Ext.isSafari) {
	            me.form.getForm().submit({
	            	method: 'POST',
                success: function() {
                    	me.fireEvent('uploadReady', null);
                }
	            });
	            return false;
            }

            Ext.each(fileField.files, function(file) {
                var timeout = window.setTimeout(function() {
                    me.uploadFile(file, 0, null, fileField.files.length);
                    clearTimeout(timeout);
                    timeout = null;
                }, 10);
            });
        }, me);

        return ret;
    },

    /**
     * Iterates through the file object
     *
     * @param [object] files
     */
    iterateFiles: function (files) {
        var me = this;

        if (typeof (files) === 'undefined') {
            return false;
        }

        this.currentFiles = files;

        // Check file amount
        if (me.checkAmount) {
            if (files.length > me.maxAmount) {
                if (me.maxAmountErrorFunction) {
                    me.maxAmountErrorFunction.call();
                }

                return false;
            }
        }
        if (me.enablePreviewImage) {
            for (var i = 0, l = files.length; i < l; i++) {
                me.createPreviewImage(files[i]);
            }
        } else {
            me.createPreview(files);
        }
    },

    reuploadFiles: function() {
        if (!Ext.isDefined(this.currentFiles)) {
            return;
        }

        this.iterateFiles(this.currentFiles);
    },

    /**
     * Creates the display when uploading files without preview image.
     * Creates a label in which the number of already uploaded files
     * will be displayed and how many have yet to be uploaded.
     * Below the number one progressbar is displayed, which is updated whenever a file is uploaded.
     *
     * @param files
     */
    createPreview: function(files) {
        var me = this;

        // safari does not have a dropzone
        if (me.dropZone !== Ext.undefined) {
            // Remove all other elements in the drop zone
            me.dropZone.removeAll();

            var text = Ext.create('Ext.Component', {
                renderTpl: me.createDropZoneTemplate(),
                tpl: me.createDropZoneTemplate(),
                renderData: {
                    actualQuantity: '0',
                    totalQuantity: files.length,
                    text: me.snippets.uploadReady
                }
            });
            text.addClass('small-padding');

            // Create the progress bar
            var progressBar = me.createProgressBar();
            progressBar.update({ percent: 0 });
            progressBar.value = 0;

            // Create information holder panel
            var infoPnl = Ext.create('Ext.panel.Panel', {
                ui: 'plain',
                cls: me.dropItemCls,
                items: [ progressBar ]
            });

            // add components to the drop zone
            me.dropZone.add(text);

            me.dropZone.add(infoPnl);
        }

        Ext.each(files, function(file) {
            var timeout = window.setTimeout(function() {
                me.uploadFile(file, progressBar, text, files.length);
                clearTimeout(timeout);
                timeout = null;
            }, 10);
        });
    },

    /**
     * Creates the progressbar
     *
     * @return { Ext.Component }
     */
    createProgressBar: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            cls: me.progressBarCls,
            tpl: me.progressBarTpl
        });
    },

    /**
     * Creates a preview image using the FileReader API
     *
     * @param [object] file
     * @return void
     */
    createPreviewImage: function (file) {
        var reader = new FileReader(), img, me = this;

        reader.onload = (function() {
            return function(event) {
                var format, progressBar, kbValue, info, infoPnl;

                // Check file type and size
                if (me.checkType) {
                    format = file.type;
                    format = format.replace(/(.*\/)/i, '');

                    if (!me.in_array(format, me.validTypes)) {
                        if (me.validTypeErrorFunction) {
                            me.validTypeErrorFunction.call();
                        }
                        return false;
                    }
                }

                // Check file size
                if (me.checkSize && me.maxSize < file.size) {
                    if (me.maxSizeErrorFunction) {
                        me.maxSizeErrorFunction.call(me, file.size);
                    }
                    return false;
                }

                // Remove all other elements in the drop zone
                if (me.initial) {
                    me.dropZone.removeAll();
                    me.initial = false;
                }

                // Create the progress bar
                progressBar = me.createProgressBar();
                progressBar.update({ percent: 0 });
                progressBar.addClass(me.previewEnabledCls);

                // Divide by 1000 - See http://de.wikipedia.org/wiki/Byte#Vergleich
                kbValue = ~~(file.size / 1000);

                // Create information panel
                info = Ext.create('Ext.Component', {
                    renderTpl: me.dropItemTpl,
                    columnWidth: 0.7,
                    renderData: {
                        infoCls: me.dropItemInfoCls,
                        name: file.name,
                        size: kbValue
                    },
                    items: [ progressBar ]
                });

                // Check if it's an image
                if ((/image/i).test(file.type)) {
                    // Create the preview image
                    img = Ext.create('Ext.container.Container', {
                        cls: me.dropItemImageCls,
                        layout: 'column',
                        items: [{
                            xtype: 'image',
                            columnWidth: 0.3,
                            src: event.target.result
                        }, info ]
                    });
                } else {
                    img = Ext.create('Ext.container.Container', {
                        cls: me.dropItemImageCls,
                        layout: 'column',
                        items: [{
                            xtype: 'container',
                            cls: 'ico-package',
                            columnWidth: 0.3
                        }, info ]
                    });
                }
                // Create information holder panel
                infoPnl = Ext.create('Ext.panel.Panel', {
                    ui: 'plain',
                    cls: me.dropItemCls,
                    items: [ img, progressBar ]
                });
                me.dropZone.add(infoPnl);

                // Create preview for the other supported file types

                me.uploadFile(file, progressBar, infoPnl);
            };
        }(img));

        reader.readAsDataURL(file);
    },

    /**
     * Uploads the passed media to the server
     *
     * @param [object] file - Content of the file to be uploaded
     * @param [object] progressBar - Ext.Component to display the upload progress
     * @param [object] infoText - Ext.panel.Panel which contains the info text '0 von 10 ...'
     * @param [int] count - Number of files to be uploaded.
     */
    uploadFile: function (file, progressBar, infoText, count) {
        var xhr = new XMLHttpRequest(),
            me = this;

        progressBar = progressBar || 0;
        infoText = infoText || null;
        xhr.open('post', me.requestURL, false);

        // Upload complete
        xhr.addEventListener('load', function(e) {
            var target = e.target,
                response = Ext.decode(target.responseText);

            if (response.success == false && response.blacklist == true) {
                Shopware.Notification.createGrowlMessage(me.snippets.blackListTitle, Ext.String.format(me.snippets.blackListMessage, response.extension));
            }

             // Check file size
            if (me.checkSize && me.maxSize < file.size) {
                if (me.maxSizeErrorFunction) {
                    me.maxSizeErrorFunction.call(me, file.size);
                }
                return false;
            }

            // increase progress bar value
            if (me.enablePreviewImage) {
                progressBar.update({ percent: 100 });
            } else {
                if (Ext.isNumeric(progressBar)) {
                    progressBar++;
                } else {
                    progressBar.value++;
                    progressBar.update({ percent: (progressBar.value / count) * 100 });
                }

                if (infoText) {
                    infoText.tpl = me.createDropZoneTemplate();
                    infoText.renderTpl = me.createDropZoneTemplate();

                    try {
                        // update info text panel
                        infoText.update({
                            actualQuantity: progressBar.value,
                            totalQuantity: count,
                            text: me.snippets.uploadReady
                        });
                    } catch (e) {
                        // todo@dr: throw exception
                    }
                }
            }

            if (target.readyState === 4 && target.status === 200) {
                try {
                    me.fireEvent('fileUploaded', target, response);
                } catch (e) {
                    // todo@dr: throw exception
                }
            }

            // last item? remove progress bar, display initial drop zone area and create a growl message
            if (infoText && progressBar.value === count) {
                // safari does not have a dropzone
                if (me.dropZone !== Ext.undefined) {
                    // Create the drop zone
                    me.dropZone.removeAll();

                    // create default drop zone
                    var text = Ext.create('Ext.Component', {
                        renderTpl: me.createDropZoneTemplate(),
                        tpl: me.createDropZoneTemplate(),
                        renderData: {
                            text: me.dropZoneText
                        }
                    });
                    me.dropZone.add(text);
                }
                me.fireEvent('uploadReady', target);
                if (response.success) {
                    // show info how much files uploaded
                    Shopware.Msg.createGrowlMessage(me.snippets.messageTitle, Ext.String.format(me.snippets.messageText, count), 'Media-Manager');

                // check if error message send
                } else if (response.error) {
                    Shopware.Msg.createGrowlMessage(
                        me.snippets.messageTitle,
                        response.error
                    );
                } else {
                    // Throw alert box
                    if (response.exception && response.exception._class) {
                        switch(response.exception._class) {
                            case 'Shopware\\Bundle\\MediaBundle\\Exception\\MediaFileExtensionNotAllowedException':
                            case 'Shopware\\Bundle\\MediaBundle\\Exception\\MediaFileExtensionIsBlacklistedException':
                                Ext.Msg.alert(
                                    me.snippets.extensionNotAllowedTitle,
                                    Ext.String.format(me.snippets.extensionNotAllowedText, response.exception.extension)
                                );
                                break;
                            default:
                                Ext.Msg.alert(me.snippets.maxUploadSizeTitle, me.snippets.maxUploadSizeText);
                        }
                    }

                    me.fireEvent('uploadFailed', response);
                }
            } else {
                me.fireEvent('uploadReady', target);
            }
        }, false);

        // enable CSRF
        xhr.setRequestHeader('X-CSRF-Token', Ext.CSRFService.getToken());

        // send xml http request
        var formData = new FormData();
        formData.append(this.fileField, file);
        xhr.send(formData);
    },

    /**
     * Checks if a value exists in an array
     *
     * @example in_array('van', ['Kevin', 'van', 'Zonneveld']); - returns true
     *          in_array('vlado', { 0: 'Kevin', vlado: 'van', 1: 'Zonneveld' }); - returns false
     *          in_array(1, ['1', '2', '3']); - returns true
     *          in_array(1, ['1', '2', '3'], false); - returns true
     *          in_array(1, ['1', '2', '3'], true); - returns false
     *
     * @param [mixed] needle
     * @param [array] haystack
     * @param [boolean] argStrict
     * @return [boolean]
     */
    in_array: function(needle, haystack, argStrict) {
        var key = '',
            strict = !!argStrict;

        if (strict) {
            for (key in haystack) {
                if (haystack[key] === needle) {
                    return true;
                }
            }
        } else {
            for (key in haystack) {
                if (haystack[key] == needle) {
                    return true;
                }
            }
        }
        return false;
    }
});
