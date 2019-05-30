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
 * Shopware UI - TinyMCE editor component
 *
 * This component provides the TinyMCE WYSIWYG editor
 * as a ExtJS 4 form field. Currently only the advanced
 * theme is supported.
 *
 * The component only provides the TinyMCE editor as a default
 * component. All other components which will be opened in
 * a new popup will remain unaffected. Please notice that this
 * component doesn't support any kind of validation.
 *
 * Inspired by daandeschepper.nl TinyMCE component:
 * http://daandeschepper.nl/tinymce-field-test
 *
 * @example
 * Ext.create('Ext.form.FormPanel', {
 *     title      : 'Sample TextArea',
 *     width      : 400,
 *     bodyPadding: 10,
 *     renderTo   : Ext.getBody(),
 *     items: [{
 *         xtype     : 'tinymcefield',
 *         name      : 'message',
 *         fieldLabel: 'Message',
 *         anchor    : '100%'
 *     }]
 * });
 *
 * @example
 * Ext.create('Shopware.form.TinyMCE', {
 *     fieldLabel: 'TinyMCE',
 *     anchor: '100%',
 *     name: 'tinymce-editor',
 *     height: 100
 * });
 */
Ext.define('Shopware.form.field.TinyMCE',
/** @lends Ext.form.field.TextArea# */
{
    /**
     * Extends the default textarea to provide the
     * TinyMCE form field
     * @string
     */
    extend: 'Ext.form.field.TextArea',

    /**
     * Defines alternate names for this class
     * @array
     */
    alternateClassName: [ 'Shopware.form.TinyMCE', 'Ext.form.field.TinyMCE' ],

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @array
     */
    alias: [ 'widget.tinymcefield', 'widget.tinymce' ],

    /**
     * List of classes that have to be loaded before instantiating this class
     * @array
     */
    requires: [ 'Ext.form.field.TextArea', 'Ext.XTemplate' ],

    /**
     * List of classes to load together with this class. These aren't neccessarily loaded before this class is instantiated
     * @array
     */
    uses: [ 'Shopware.MediaManager.MediaSelection' ],

    /**
     * Indicates if the TinyMCE editor is initialized
     *
     * @boolean
     */
    initialized: false,

    /**
     * List of static methods, properties and attributes for this class
     * @object
     */
    statics: {

        /**
         * Global configuration for the TinyMCE editor.
         *
         * @static
         * @object
         */
        settings: {
            cleanup : false,
            convert_urls : false,
            media_strict : false,
            relative_urls : true,
            language: "{$tinymceLang}",
            mode: "textareas",
            theme: "advanced",
            skin: "o2k7",
            invalid_elements:'script,applet',

            /** {if $user->extended_editor eq 1} */
            plugins: "media_selection,safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
            /** {else} */
            plugins: "media_selection,fullscreen",
            /** {/if} */

            theme_advanced_toolbar_location: "top",
            theme_advanced_resizing: true,
            theme_advanced_toolbar_align: "left",
            theme_advanced_statusbar_location: "bottom",
            extended_valid_elements : "font[size],iframe[frameborder|src|width|height|name|align|frameborder|allowfullscreen|id|class|style],script[src|type],object[width|height|classid|codebase|ID|value],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage],video[autoplay|class|controls|id|lang|loop|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|preload|poster|src|style|title|width|height],audio[autoplay|class|controls|id|lang|loop|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|preload|src|style|title]",
            document_base_url: '{"{url controller="index" fullPath}"}/'.replace('/backend', ''),

            // Content CSS - Styles the tiny mce editor. Please note the append timestamp. It's used to prevent caching the stylesheet
            contentCSS: '{link file="backend/_resources/styles/tiny_mce.css" fullPath}?_dc=' + new Date().getTime(),

            /** {if $user->extended_editor eq 1} */
            skin_variant: 'silver',
            theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor,|,media_selection"
            /** {else} */
            skin_variant: 'shopware',
            theme_advanced_buttons1: 'undo,redo,|,bold,italic,underline,|,fontsizeselect,forecolor,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,|,link,unlink,media_selection,|,code,fullscreen,',
            theme_advanced_buttons2: '',
            theme_advanced_buttons3: '',
            theme_advanced_buttons4: ''
            /** {/if} */
        },

        /**
         * Sets global TinyMCE editor settings
         *
         * @static
         * @public
         * @param [object] userSettings - Object of TinyMCE editor settings
         * @return void
         */
        setGlobalSettings: function(userSettings) {
            Ext.apply(this.settings, userSettings);
        }
    },

    /**
     * Truthy if the component has an `emptyText`, otherwise falsy.
     * @default false
     * @boolean
     */
    hasPlaceholder: false,

    /**
     * List of configuration options with their default values, for which automatically accessor methods are generated
     * @object
     */
    config: {

        /**
         * Default configuration of the TinyMCE editor.
         * @object
         */
        editor: { }
    },

    /**
     * Deactive the autoSize functionality of the
     * textarea due to the fact that this crashes
     * the TinyMCE editor instance
     *
     * @public
     * @return void
     */
    autoSize: Ext.emptyFn,

    /**
     * String with the error message for when no source files are included
     */
    noSourceErrorText: "The TinyMCE editor source files aren't included in the project",

    /**
     * We're using virtual paths to describe the path to images and the property defines
     * the API endpoint to request the images.
     */
    preloadImageUrl: '{url controller="MediaManager" action="getMediaUrls"}',

    /**
     * Initializes the component and sets it up to
     * match the requirements of the TinyMCE editor.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        // Register additional events
        me.registerEvents();
    },

    /**
     * Registers additional events which are associated
     * to the TinyMCE editor.
     *
     * @private
     * @return [boolean]
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(

            /**
             * Fires before the TinyMCE editor is initialized
             *
             * @event beforerendereditor
             * @param [object] scope
             * @param [string] ID of the render element
             * @param [object] TinyMCE configuration
             */
            'beforerendereditor',

            /**
             * Fires when the TinyMCE editor is initialized
             *
             * @event rendereditor
             * @param [object] scope
             * @param [object] generated TinyMCE instance
             * @param [string] ID of the render element
             * @param [object] TinyMCE configuration
             */
            'rendereditor',

            /**
             * Fires after the TinyMCE editor is initialized
             *
             * @event afterrendereditor
             * @param [object] scope
             * @param [object] generated TinyMCE instance
             * @param [string] ID of the render element
             * @param [object] TinyMCE configuration
             */
            'afterrendereditor'
        );

        return true;
    },

    /**
     * Renders the fieldSubTpl into the ownerCt.
     *
     * @private
     * @param [object] ct - Ext.form.field.TextArea
     * @param [integer] position - Render position in the ownerCt
     * @return void
     */
    onRender: function(ct, position) {
        var me = this, el;
        Ext.applyIf(me.subTplData, {
            cols: me.cols,
            rows: me.rows
        });

        me.callParent(arguments);

        // Hide the underlying textarea
        el = me.inputEl;
        el.dom.setAttribute('tabIndex', -1);
        el.addCls('x-hidden');

        // Init the tinymce editor
        me.initEditor();
        me.registerEditorEvents();
    },

    /**
     * Initialize the TinyMCE editor field based on extended
     * textarea.
     *
     * Raises an error if the TinyMCE sources aren't loaded.
     *
     * @private
     * @retrun void
     */
    initEditor: function() {
        var me = this, input = me.inputEl, height, placeholder = false;

        // Check if the TinyMCE editor files are included
        if(!window.tinyMCE) {
            Ext.Error.raise(me.noSourceErrorText);
        }

        // Merge user settings with our default settings
        me.config.editor = Ext.Object.merge(this.statics().settings, me.editor);

        // Set height if available
        if(me.height) {
            height = me.height - 12;
            me.config.editor.height = height;
        }

        // Support the readOnly property
        if(me.readOnly) {
            me.config.editor.readonly = true;
        }

        // Fire the "beforerendereditor" event
        me.fireEvent('beforerendereditor', me, input.id, me.config.editor);

        // Initialize the TinyMCE editor
        me.tinymce = new tinymce.Editor(input.id, me.config.editor);

        // Fire the "rendereditor" event
        me.fireEvent('rendereditor', me, me.tinymce, input.id, me.config.editor);

        // Bind on change event to refresh the content of the underlying textarea
        me.tinymce.onChange.add(function(ed, values) {
            values.content = me.replaceImagePathsWithSmartyPlugin(values.content);
            me.setRawValue(values.content);
        });

        // Fix for SW-2741: After resizing an image, no Change-Event is fired by the editor.
        // Thus the content is not set correctly and any changes might get lost.
        //
        // This fix will update the editor's content when the editor looses the focus
        // (e.g. in order to click the save button in the ExtJS window).
        // This solution still as some drawbacks as it image-resize-actions won't trigger a undo-step usually.
        me.tinymce.onInit.add(function(ed, evt) {
            me.initialized = true;

            me.setValue(ed.getContent());

            var dom = ed.dom,
                doc = ed.getDoc(),
                el = doc.content_editable ? ed.getBody() : (tinymce.isGecko ? doc : ed.getWin());

            document.addEventListener('insertMedia', function() {
                me.replacePlaceholderWithImage(ed.getContent());
            }, false);

            // Support for the `emptyText` property
            if((!me.value || !me.value.length) && me.emptyText && me.emptyText.length) {
                me.tinymce.setContent(me.emptyText);
                me.hasPlaceholder = true;

                tinymce.dom.Event.add(el, 'focus', function() {
                    var value = me.tinymce.getContent();
                    value = Ext.util.Format.stripTags(value);

                    if(value === me.emptyText) {
                        me.tinymce.setContent('');
                    }
                });
            }

            tinymce.dom.Event.add(el, 'blur', function() {
                var value = me.tinymce.getContent();
                value = me.replaceImagePathsWithSmartyPlugin(value);
                me.setRawValue(value);

                value = Ext.util.Format.stripTags(value);
                if(me.hasPlaceholder && !value.length || (value == me.emptyText)) {
                    me.tinymce.setContent(me.emptyText);
                }
            });

            me.fixImageSelection();

            me.changeSniffer = window.setInterval(function() {
                var value = me.tinymce.getContent();
                value = me.replaceImagePathsWithSmartyPlugin(value);
                me.setRawValue(value);
            }, 300);
        });

        // Render the TinyMCE editor
        me.tinymce.render();

        // Fire the "afterrendereditor" event
        me.fireEvent('afterrendereditor', me, me.tinymce, input.id, me.config.editor);
    },

    /**
     * Replaces original onClick listener with bugfixed version to prevent
     * on click console error in Webkit browsers.
     *
     */
    fixImageSelection: function() {
        var me = this;

        delete me.tinymce.onClick.listeners[2];
        me.tinymce.onClick.listeners = Ext.Array.clean(me.tinymce.onClick.listeners);

        me.tinymce.onClick.add(function(editor, e) {
            e = e.target;
            var selection = editor.selection;

            if (/^(IMG|HR)$/.test(e.nodeName)) {
                try {
                    selection.getSel().setBaseAndExtent(e, 0, e, 1); //Original behavior in 3.5.9; still works in Safari 10.1
                } catch (ex) {
                    selection.getSel().setBaseAndExtent(e, 0, e, 0); //Updated behavior for Chrome 58+ (and, I'm guessing, future versions of Safari)
                }
            }

            if (e.nodeName === 'A' && dom.hasClass(e, 'mceItemAnchor')) {
                selection.select(e);
            }

            editor.nodeChanged();
        });
    },

    _findImagesInDOMContent: function(content) {
        var filteredImages = [],
            images = content.getElementsByTagName('img');

        Ext.each(images, function(img) {
            if(img.classList.contains('tinymce-editor-image')) {
                var src = img.getAttribute('data-src'),
                    id = img.getAttribute('id');

                // update case
                if (!id) {
                    src = img.getAttribute('src');

                    if (src && src.substr(0,5) != "media") {
                        return;
                    }

                    id = 'tinymce-editor-image-' + Shopware.ModuleManager.uuidGenerator.generate();

                    img.setAttribute('id', id);
                    img.setAttribute('data-src', src);
                    img.classList.add(id);
                }

                filteredImages.push({ src: src, id: id, image: img });
            }
        });

        return filteredImages;
    },

    replaceImagePathsWithSmartyPlugin: function(rawContent) {
        var me = this,
            tpl = "{ldelim}media path='[0]'{rdelim}",
            content, images, html;

        if (!me.isValidContent(rawContent)) {
            return rawContent;
        }

        // Create a DOM using the content of the tinymce
        content = me.HTMLBlobToDomElements(rawContent);
        images = me._findImagesInDOMContent(content);

        Ext.each(images, function(img) {
            var element = content.getElementById(img.id),
                src = element.getAttribute('src'),
                dataSrc = element.getAttribute('data-src');

            // The source already using the Smarty media plugin, therefor we don't have to do anything
            if(src.charAt(0) === '{ldelim}') {
                return;
            }

            element.setAttribute('src', Ext.String.format(tpl, dataSrc));
        });

        html = me.DOMElementsToHTMLBlob(content);

        return html;
    },

    replaceSmartyPluginWithImagePaths: function(rawContent) {
        var me = this, content, images;

        if (!me.isValidContent(rawContent)) {
            return rawContent;
        }

        content = me.HTMLBlobToDomElements(rawContent);
        images = me._findImagesInDOMContent(content);

        Ext.each(images, function(img) {
            var element = content.getElementById(img.id);

            element.setAttribute('src', '{link file="TinyMce/plugins/media_selection/assets/placeholder-image.png"}');
        });

        rawContent = me.DOMElementsToHTMLBlob(content);

        return rawContent;
    },

    replacePlaceholderWithImage: function(rawContent, callback) {
        var me = this,
            imagesToLoad = [],
            content, params = '';

        if (!me.isValidContent(rawContent)) {
            if (Ext.isFunction(callback)) {
                callback(rawContent);
                return;
            } else {
                return rawContent;
            }
        }

        content = me.HTMLBlobToDomElements(rawContent);
        imagesToLoad = me._findImagesInDOMContent(content);

        Ext.each(imagesToLoad, function(img) {
            params = params + 'paths[]=' + img.src + '&';
        });
        params = params.substring(0, params.length - 1);

        if (params.length <= 0) {
            if (Ext.isFunction(callback)) {
                callback(rawContent);
                return;
            } else {
                return rawContent;
            }
        }

        Ext.Ajax.request({
            url: me.preloadImageUrl + '?' + params,
            success: function(response) {
                var html;
                response = JSON.parse(response.responseText);

                if(!response.success) {
                    return false;
                }

                Ext.each(response.data, function(item, index) {
                    var originalImage = imagesToLoad[index],
                        element = content.getElementById(originalImage.id);

                    element.setAttribute('src', item);
                });

                html = me.DOMElementsToHTMLBlob(content);

                if (Ext.isFunction(callback)) {
                    callback(html);
                } else {
                    me.tinymce.setContent(html);
                }
            }
        });
    },

    isValidContent: function(content) {
        return (Ext.isDefined(content) && content !== null && content.length && content.length > 0);
    },

    HTMLBlobToDomElements: function(html) {
        var dp = new DOMParser();
        return dp.parseFromString(html, 'text/html');
    },

    DOMElementsToHTMLBlob: function(elements) {
        return elements.body.innerHTML;
    },

    /**
     * Registers additional events to enhance the communication between
     * the TinyMCE editor and the ExtJS component
     *
     * @private
     * @return void
     */
    registerEditorEvents: function() {
        var me = this;

        me.on({
            'resize': {
                scope: me,
                fn: me.onEditorResize
            }
        })
    },

    /**
     * Event listener which will be fired when the ExtJS component
     * will be resized.
     *
     * Resizes the TinyMCE editor based on the passed with and height.
     *
     * Raises an error if the theme isn't "advanced"
     *
     * @private
     * @event resize
     * @param [object] view - Underlying Ext.form.field.TextArea
     * @param [integer] width - Width of the textarea
     * @param [integer] height - Height of the textarea
     * @return [boolean|void]
     */
    onEditorResize: function(view, width, height) {
        var me = this, editor = me.tinymce,
            edTable = Ext.get(editor.id + "_tbl"),
            edIframe = Ext.get(editor.id + "_ifr"),
            edToolbar = Ext.get(editor.id + "_xtbar");

        if(!edTable) {
            return false;
        }

        // Set minimal width and minimal height
        width = (width < 100) ? 100 : (width - 205);
        height = (height < 129) ? 129 : (height - 100);

        var toolbarWidth = width;
        if(edTable) {
            toolbarWidth = width - edTable.getFrameWidth( "lr" );
        }

        var toolbarHeight = 0;
        if(edToolbar) {
            toolbarHeight = edToolbar.getHeight();
            var toolbarTd = edToolbar.findParent( "td", 5, true );
            toolbarHeight += toolbarTd.getFrameWidth( "tb" );
            edToolbar.setWidth( toolbarWidth );
        }

        var edStatusbarTd = edTable.child( ".mceStatusbar" );
        var statusbarHeight = 0;
        if(edStatusbarTd) {
            statusbarHeight += edStatusbarTd.getHeight();
        }

        var iframeHeight = height - toolbarHeight - statusbarHeight;
        var iframeTd = edIframe.findParent( "td", 5, true );
        if(iframeTd)
            iframeHeight -= iframeTd.getFrameWidth( "tb" );

        // Resize iframe and container
        edTable.setSize( width, height );
        edIframe.setSize( toolbarWidth, iframeHeight );
    },

    /**
     * Returns the local instance of the TinyMCE editor.
     *
     * @public
     * @return [object] TinyMCE instance
     */
    getEditor: function() {
        return this.tinymce;
    },

    /**
     * Sets a data value into the field and runs the change detection.
     *
     * @public
     * @param [string] value - The new value
     * @return [object] this - Ext.form.field.TextArea
     */
    setValue: function(value, editorChange) {
        var me = this;

        if(!me.initialized) {
            value = me.replaceSmartyPluginWithImagePaths(value);

            me.replacePlaceholderWithImage(value, function (value) {
                value = value === null || value === undefined ? '' : value;
                me.setRawValue(me.valueToRaw(value));
                me.mixins.field.setValue.call(me, value);

                if (me.tinymce) {
                    try {
                        me.tinymce.setContent(value);
                    } catch (e) {
                        // Tinymce is still loading, the content will be set automatically when Tinymce is done
                    }
                }
            });

            return me;
        }

        if(!editorChange) {
            me.setEditorValue(value, me);

            // Support for the `emptyText` property
            if((!value || !value.length) && me.hasPlaceholder) {
                me.setEditorValue(me.emptyText, me);
            }
        }

        me.callParent(arguments);

        return me;
    },

    /**
     * Sets the field's raw value directly and bypasses the change detection.
     *
     * @public
     * @param [string] value - The new value
     * @return [false|object] this - Ext.form.field.TextArea
     */
    setRawValue: function(value) {
        var me = this;
        me.callParent(arguments);

        if(!me.initialized) {
            return false;
        }

        return me;
    },

    /**
     * Sets the editor's value and cleanup the editor's undo manager.
     *
     * @private
     * @param [string] value - The new value
     * @return [boolean] true otherwise false
     */
    setEditorValue: function(value, scope) {
        var me = scope;

        if(!me.initialized || !me.tinymce) {

            me.on('afterrendereditor', function() {
                me.setEditorValue(value, me);
            }, me, { single: true });

            return false;
        }

        if(me.tinymce.undoManager) {
            me.tinymce.undoManager.clear();
        }

        value = me.replaceSmartyPluginWithImagePaths(value);
        me.replacePlaceholderWithImage(value);

        me.tinymce.setContent(value === null || value === undefined ? '' : value);
        me.tinymce.startContent = me.tinymce.getContent({ format: 'raw' });

        me.replacePlaceholderWithImage(value);

        return true;
    },

    /**
     * Try to focus this component.
     *
     * @public
     * @param [boolean] selectText - If applicable, true to also select the text in this component
     * @param [boolean|integer] delay - Delay the focus this number of milliseconds (true for 10 milliseconds)
     * @return [object] this - Ext.form.field.TextArea
     */
    focus: function(selectText, delay) {
        var me = this;

        // Support the delay
        if(delay) {
            if (!me.focusTask) {
                me.focusTask = Ext.create('Ext.util.DelayedTask', me.focus);
            }
            me.focusTask.delay(Ext.isNumber(delay) ? delay : 10, null, me, [selectText, false]);
            return me;
        }

        // Focus the TinyMCE editor
        me.tinymce.focus();

        if(selectText) {

            // todo@stp - check if this method works in IE 9 aswell
            var edIframe = Ext.get(me.tinymce.id + "_ifr"),
                dom = edIframe.dom,
                doc = dom.contentDocument,
                win = dom.contentWindow,
                selection = win.getSelection(),
                range = doc.createRange();

            range.selectNodeContents(doc.body);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        return me;
    },

    /**
     * Destroys the component.
     *
     * @public
     * @return void
     */
    destroy: function() {
        var me = this;
        me.callParent(arguments);

        clearInterval(me.changeSniffer);
        Ext.destroyMembers(me, 'tinymce');
    },

    /**
     * Enable the component.
     *
     * @public
     * @param [boolean] slient - Passing true will supress the 'enable' event from being fired.
     * @return [object] this - Ext.form.field.TextArea
     */
    enable: function(slient) {
        var me = this;
        me.callParent(arguments);

        if(!me.tinymce || !me.initialized) {
            return me;
        }

        var bodyEl = me.tinymce.getBody();
        bodyEl = Ext.get(bodyEl);

        if(bodyEl.hasCls('mceNonEditable')) {
            bodyEl.removeCls('mceNonEditable');
            bodyEl.addCls('mceContentBody');
        }
        me.tinymce.getBody().setAttribute('contenteditable', true);

        return me;
    },

    /**
     * Disable the component.
     *
     * @public
     * @param [boolean] slient - Passing true will supress the 'disable' event from being fired.
     * @return [object] this - Ext.form.field.TextArea
     */
    disable: function(silent) {
        var me = this;
        me.callParent(arguments);

        if(!me.tinymce || !me.initialized) {
            return me;
        }

        var bodyEl = me.tinymce.getBody();
        bodyEl = Ext.get(bodyEl);

        if(bodyEl.hasCls('mceContentBody')) {
            bodyEl.removeCls('mceContentBody');
            bodyEl.addCls('mceNonEditable');
        }
        me.tinymce.getBody().setAttribute('contenteditable', false);

        return me;
    }
});
