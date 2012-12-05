/** ************************************************************
	Ext.ux.TinyMCE v0.8.5
	ExtJS form field containing TinyMCE v3.

	Author: Andrew Mayorov et al.
	http://blogs.byte-force.com/xor

	Copyright (c)2008-2010 BYTE-force
	www.byte-force.com

	License: LGPLv2.1 or later
*/

(function() {

	Ext.namespace("Ext.ux");

	var tmceInitialized = false;

	// Lazy references to classes. To be filled in the initTinyMCE method.
	var WindowManager;
	var ControlManager;

	// Create a new Windows Group for the dialogs
	/*var windowGroup = new Ext.WindowGroup();
	windowGroup.zseed = 12000;*/


	/** ----------------------------------------------------------
	Ext.ux.TinyMCE
	*/
	Ext.ux.TinyMCE = Ext.extend( Ext.form.Field, {

		// TinyMCE Settings specified for this instance of the editor.
		tinymceSettings: {},

		// Validation properties
		allowBlank: true,
		invalidText: "The value in this field is invalid",
		invalidClass: "invalid-content-body",
		minLengthText : 'The minimum length for this field is {0}',
		maxLengthText : 'The maximum length for this field is {0}',
		blankText : 'This field is required',

		// HTML markup for this field
		hideMode: 'offsets',
		defaultAutoCreate: {
			tag: "textarea",
			style: "width:1px;height:1px;",
			autocomplete: "off"
		},

		/** ----------------------------------------------------------
		*/
		constructor: function(cfg) {

			var config = {
				tinymceSettings: {
					theme : "advanced",
					plugins: "pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
					theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|",
					theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : false,
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],iframe[src|name|width|height|align|frameborder|marginwidth|marginheight|scrolling],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]",
					accessibility_focus : false
				}
			};

			Ext.apply(config, cfg);

			// Add events
			this.addEvents({
				"editorcreated": true
			});

			Ext.ux.TinyMCE.superclass.constructor.call(this, config);
		},

		/** ----------------------------------------------------------
		*/
		initComponent: function() {
			this.tinymceSettings = this.tinymceSettings || {};
			Ext.ux.TinyMCE.initTinyMCE({ language: this.tinymceSettings.language });
		},

		/** ----------------------------------------------------------
		*/
		initEvents: function() {
			this.originalValue = this.getValue();
		},

		/** ----------------------------------------------------------
		*/
		onRender: function(ct, position) {
			Ext.ux.TinyMCE.superclass.onRender.call(this, ct, position);

            // Fix size if it was specified in config
			if (Ext.type(this.width) == "number") {
				this.tinymceSettings.width = this.width;
			}
			if (Ext.type(this.height) == "number") {
				this.tinymceSettings.height = this.height;
			}

			//this.el.dom.style.border = '0 none';
			this.el.dom.setAttribute('tabIndex', -1);
			this.el.addClass('x-hidden');

			// Wrap textarea into DIV
			this.textareaEl = this.el;
			var wrapElStyle = { overflow: "hidden" };
			if( Ext.isIE ) { // fix IE 1px bogus margin
				wrapElStyle["margin-top"] = "-1px";
				wrapElStyle["margin-bottom"] = "-1px";
			}
			this.wrapEl = this.el.wrap({ style: wrapElStyle });
			this.actionMode = "wrapEl"; // Set action element to the new wrapper
			this.positionEl = this.wrapEl;

			var id = this.getId();

			// Create TinyMCE editor.
			this.ed = new tinymce.Editor(id, this.tinymceSettings);

			// Validate value onKeyPress
			var validateContentTask = new Ext.util.DelayedTask( this.validate, this );
			this.ed.onKeyPress.add(function(ed, controlManager) {
                if (controlManager.keyCode == 9) {
                    controlManager.preventDefault();
                }

				validateContentTask.delay( 250 );
			} .createDelegate(this));

			// Set up editor events' handlers
			this.ed.onBeforeRenderUI.add(function(ed, controlManager) {
				// Replace control manager
				ed.controlManager = new ControlManager(this, ed);
			} .createDelegate(this));

			this.ed.onPostRender.add(function(ed, controlManager) {
				var s = ed.settings;

				// Modify markup.
				var tbar = Ext.get(Ext.DomQuery.selectNode("#" + this.ed.id + "_tbl td.mceToolbar"));
				if( tbar != null ) {
					// If toolbar is present
					var tbars = tbar.select("> table.mceToolbar");
					Ext.DomHelper
						.append( tbar,
							{ tag: "div", id: this.ed.id + "_xtbar", style: { overflow: "hidden"} }
							, true )
						.appendChild(tbars);
				}

				// Change window manager
				ed.windowManager = new WindowManager({
					editor: this.ed,
					manager: this.manager
				});
				// Patch css-style for validation body like ExtJS
				Ext.get(ed.getContentAreaContainer()).addClass('patch-content-body');

				// Event of focused body
				Ext.Element.fly(s.content_editable ? ed.getBody() : ed.getWin())
					.on("focus", this.onFocus, this);

				// Event of blur body
				Ext.Element.fly(s.content_editable ? ed.getBody() : ed.getWin())
					.on("blur", this.onBlur, this,
						this.inEditor && Ext.isWindows && Ext.isGecko ? { buffer: 10} : null
					);

			} .createDelegate(this));

			// Set event handler on editor init.
			//this.ed.onInit.add(function() {
			//} .createDelegate(this));

			// Wire "change" event
			this.ed.onChange.add(function(ed, l) {
				this.fireEvent("change", ed, l);
			} .createDelegate(this));

			// Render the editor
			this.ed.render();
			tinyMCE.add(this.ed);

			// Fix editor size when control will be visible
			(function fixEditorSize() {

				// If element is not visible yet, wait.
				if( !this.isVisible() ) {
					arguments.callee.defer( 50, this );
					return;
				}

				var size = this.getSize();
				this.withEd( function() {
					this._setEditorSize( size.width, size.height );

					// Indicate that editor is created
					this.fireEvent("editorcreated");
				});
			}).call( this );
		},

		/** ----------------------------------------------------------
		*/
		getResizeEl: function() {
			return this.wrapEl;
		},

		/** ----------------------------------------------------------
		* Returns the name attribute of the field if available
		* @return {String} name The field name
		*/
		getName: function() {
			return this.rendered && this.textareaEl.dom.name
				? this.textareaEl.dom.name : (this.name || '');
		},

		/** ----------------------------------------------------------
		*/
		initValue: function() {

			if (!this.rendered)
				Ext.ux.TinyMCE.superclass.initValue.call(this);
			else {
				if (this.value !== undefined) {
					this.setValue(this.value);
				}
				else {
					var v = this.textareaEl.value;
					if ( v )
						this.setValue( v );
				}
			}
		},

		/** ----------------------------------------------------------
		*/
		beforeDestroy: function() {
			if( this.ed ) tinyMCE.remove( this.ed );
			if( this.wrapEl ) Ext.destroy( this.wrapEl );
			Ext.ux.TinyMCE.superclass.beforeDestroy.call( this );
		},

		/** ----------------------------------------------------------
		*/
		getRawValue : function(){

			if( !this.rendered || !this.ed.initialized )
				return Ext.value( this.value, '' );

			var v = this.ed.getContent();
			if(v === this.emptyText){
				v = '';
			}
			return v;
		},

		/** ----------------------------------------------------------
		*/
		getValue: function() {

			if( !this.rendered || !this.ed.initialized )
				return Ext.value( this.value, '' );

			var v = this.ed.getContent();
			if( v === this.emptyText || v === undefined ){
				v = '';
			}
			return v;
		},

		/** ----------------------------------------------------------
		*/
		setRawValue: function(v) {
			this.value = v;
			if (this.rendered)
				this.withEd(function() {
					this.ed.undoManager.clear();
					this.ed.setContent(v === null || v === undefined ? '' : v);
					this.ed.startContent = this.ed.getContent({ format: 'raw' });
				});
		},

		/** ----------------------------------------------------------
		*/
		setValue: function(v) {
			this.value = v;
			if (this.rendered)
				this.withEd(function() {
					this.ed.undoManager.clear();
					this.ed.setContent(v === null || v === undefined ? '' : v);
					this.ed.startContent = this.ed.getContent({ format: 'raw' });
					this.validate();
					//this.ed.resizeToContent();
				});
		},

		/** ----------------------------------------------------------
		*/
		isDirty: function() {
			if (this.disabled || !this.rendered) {
				return false;
			}
			return this.ed && this.ed.initialized && this.ed.isDirty();
		},

		/** ----------------------------------------------------------
		*/
		syncValue: function() {
			if (this.rendered && this.ed.initialized)
				this.ed.save();
		},

		/** ----------------------------------------------------------
		*/
		getEd: function() {
			return this.ed;
		},

		/** ----------------------------------------------------------
		*/
		disable: function() {
			this.withEd(function() {
				var bodyEl = this.ed.getBody();
				bodyEl = Ext.get(bodyEl);

				if (bodyEl.hasClass('mceContentBody')) {
					bodyEl.removeClass('mceContentBody');
					bodyEl.addClass('mceNonEditable');
				}
			});
		},

		/** ----------------------------------------------------------
		*/
		enable: function() {
			this.withEd(function() {
				var bodyEl = this.ed.getBody();
				bodyEl = Ext.get(bodyEl);

				if (bodyEl.hasClass('mceNonEditable')) {
					bodyEl.removeClass('mceNonEditable');
					bodyEl.addClass('mceContentBody');
				}
			});
		},

		/** ----------------------------------------------------------
		*/
		onResize: function(aw, ah) {
			if( Ext.type( aw ) != "number" ){
				aw = this.getWidth();
			}
			if( Ext.type(ah) != "number" ){
				ah = this.getHeight();
			}
			if (aw == 0 || ah == 0)
				return;

			if( this.rendered && this.isVisible() ) {
				this.withEd(function() { this._setEditorSize( aw, ah ); });
			}
		},

		/** ----------------------------------------------------------
			Sets control size to the given width and height
		*/
		_setEditorSize: function( width, height ) {

			// We currently support only advanced theme resize
			if( !this.ed.theme.AdvancedTheme ) return;

			// Minimal width and height for advanced theme
			if( width < 100 ) width = 100;
			if( height < 129 ) height = 129;

			// Set toolbar div width
			var edTable = Ext.get(this.ed.id + "_tbl"),
				edIframe = Ext.get(this.ed.id + "_ifr"),
				edToolbar = Ext.get(this.ed.id + "_xtbar");

			var toolbarWidth = width;
			if( edTable )
				toolbarWidth = width - edTable.getFrameWidth( "lr" );

			var toolbarHeight = 0;
			if( edToolbar ) {
				toolbarHeight = edToolbar.getHeight();
				var toolbarTd = edToolbar.findParent( "td", 5, true );
				toolbarHeight += toolbarTd.getFrameWidth( "tb" );
				edToolbar.setWidth( toolbarWidth );
			}

			var edStatusbarTd = edTable.child( ".mceStatusbar" );
			var statusbarHeight = 0;
			if( edStatusbarTd ) {
				statusbarHeight += edStatusbarTd.getHeight();
			}

			var iframeHeight = height - toolbarHeight - statusbarHeight;
			var iframeTd = edIframe.findParent( "td", 5, true );
			if( iframeTd )
				iframeHeight -= iframeTd.getFrameWidth( "tb" );

			// Resize iframe and container
			edTable.setSize( width, height );
			edIframe.setSize( toolbarWidth, iframeHeight );
		},

		/** ----------------------------------------------------------
		*/
		focus: function(selectText, delay) {
			if (delay) {
				this.focus.defer(typeof delay == 'number' ? delay : 10, this, [selectText, false]);
				return;
			}

			this.withEd(function() {
				this.ed.focus();
				/*if (selectText === true) {
				// TODO: Select editor's content
				}*/
			});

			return this;
		},

		/** ----------------------------------------------------------
		*/
		processValue : function( value ){
			return Ext.util.Format.stripTags( value );
		},

		/** ----------------------------------------------------------
		*/
		validateValue: function( value ) {
			if(Ext.isFunction(this.validator)){
				var msg = this.validator(value);
				if(msg !== true){
					this.markInvalid(msg);
					return false;
				}
			}
			if(value.length < 1 || value === this.emptyText){ // if it's blank
				 if(this.allowBlank){
					 this.clearInvalid();
					 return true;
				 }else{
					 this.markInvalid(this.blankText);
					 return false;
				 }
			}
			if(value.length < this.minLength){
				this.markInvalid(String.format(this.minLengthText, this.minLength));
				return false;
			}
			if(value.length > this.maxLength){
				this.markInvalid(String.format(this.maxLengthText, this.maxLength));
				return false;
			}
			if(this.vtype){
				var vt = Ext.form.VTypes;
				if(!vt[this.vtype](value, this)){
					this.markInvalid(this.vtypeText || vt[this.vtype +'Text']);
					return false;
				}
			}
			if(this.regex && !this.regex.test(value)){
				this.markInvalid(this.regexText);
				return false;
			}
			return true;
		},

		/** ----------------------------------------------------------
		If ed (local editor instance) is already initilized, calls
		specified function directly. Otherwise - adds it to ed.onInit event.
		*/
		withEd: function(func) {

			// If editor is not created yet, reschedule this call.
			if (!this.ed) this.on(
				"editorcreated",
				function() { this.withEd(func); },
				this);

			// Else if editor is created and initialized
			else if (this.ed.initialized) func.call(this);

			// Else if editor is created but not initialized yet.
			else this.ed.onInit.add(function() { func.defer(10, this); } .createDelegate(this));
		}
	});

	// Add static members
	Ext.apply(Ext.ux.TinyMCE, {

		/**
		Static field with all the plugins that should be loaded by TinyMCE.
		Should be set before first component would be created.
		@static
		*/
		tinymcePlugins: "pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		/** ----------------------------------------------------------
			Inits TinyMCE and other necessary dependencies.
		*/
		initTinyMCE: function(settings) {
			if (!tmceInitialized) {

				// Create lazy classes
				/** ----------------------------------------------------------
				WindowManager
				*/
				WindowManager = Ext.extend( tinymce.WindowManager, {

					/** ----------------------------------------------------------
						Config parameters:
						editor - reference to TinyMCE intstance.
						mangager - WindowGroup to use for the popup window. Could be empty.
					*/
					constructor: function( cfg ) {
						WindowManager.superclass.constructor.call(this, cfg.editor);

						// Set window group
						this.manager = cfg.manager;
					},

					/** ----------------------------------------------------------
					*/
					alert: function(txt, cb, s) {
						Ext.MessageBox.alert("", txt, function() {
							if (!Ext.isEmpty(cb)) {
								cb.call(this);
							}
						}, s);
					},

					/** ----------------------------------------------------------
					*/
					confirm: function(txt, cb, s) {
						Ext.MessageBox.confirm("", txt, function(btn) {
							if (!Ext.isEmpty(cb)) {
								cb.call(this, btn == "yes");
							}
						}, s);
					},

					/** ----------------------------------------------------------
					*/
					open: function(s, p) {

						s = s || {};
						p = p || {};

						if (!s.type)
							this.bookmark = this.editor.selection.getBookmark('simple');

						s.width = parseInt(s.width || 320);
						s.height = parseInt(s.height || 240) + (tinymce.isIE ? 8 : 0);
						s.min_width = parseInt(s.min_width || 150);
						s.min_height = parseInt(s.min_height || 100);
						s.max_width = parseInt(s.max_width || 2000);
						s.max_height = parseInt(s.max_height || 2000);
						s.movable = true;
						s.resizable = true;
						p.mce_width = s.width;
						p.mce_height = s.height;
						p.mce_inline = true;

						this.features = s;
						this.params = p;

						var win = new Ext.Window(
						{
							title: s.name,
							width: s.width,
							height: s.height,
							minWidth: s.min_width,
							minHeight: s.min_height,
							resizable: true,
							maximizable: s.maximizable,
							minimizable: s.minimizable,
							modal: true,
							stateful: false,
							constrain: true,
							manager: this.manager,
							layout: "fit",
							items: [
								new Ext.BoxComponent({
									autoEl: {
										tag: 'iframe',
										src: s.url || s.file
									},
									style : 'border-width: 0px;'
								})
							]
						});

						p.mce_window_id = win.getId();

						win.show(null,
							function() {
								if (s.left && s.top)
									win.setPagePosition(s.left, s.top);
								var pos = win.getPosition();
								s.left = pos[0];
								s.top = pos[1];
								this.onOpen.dispatch(this, s, p);
							},
							this
						);

						return win;
					},

					/** ----------------------------------------------------------
					*/
					close: function(win) {

						// Probably not inline
						if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
							WindowManager.superclass.close.call(this, win);
							return;
						}

						var w = Ext.getCmp(win.tinyMCEPopup.id);
						if (w) {
							this.onClose.dispatch(this);
							w.close();
						}
					},

					/** ----------------------------------------------------------
					*/
					setTitle: function(win, ti) {

						// Probably not inline
						if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
							WindowManager.superclass.setTitle.call(this, win, ti);
							return;
						}

						var w = Ext.getCmp(win.tinyMCEPopup.id);
						if (w) w.setTitle(ti);
					},

					/** ----------------------------------------------------------
					*/
					resizeBy: function(dw, dh, id) {

						var w = Ext.getCmp(id);
						if (w) {
							var size = w.getSize();
							w.setSize(size.width + dw, size.height + dh);
						}
					},

					/** ----------------------------------------------------------
					*/
					focus: function(id) {
						var w = Ext.getCmp(id);
						if (w) w.setActive(true);
					}

				});

				/** ----------------------------------------------------------
				ControlManager
				*/
				ControlManager = Ext.extend( tinymce.ControlManager, {

					// Reference to ExtJS control Ext.ux.TinyMCE.
					control: null,

					/** ----------------------------------------------------------
					*/
					constructor: function(control, ed, s) {
						this.control = control;
						ControlManager.superclass.constructor.call(this, ed, s);
					},

					/** ----------------------------------------------------------
					*/
					createDropMenu: function(id, s) {
						// Call base method
						var res = ControlManager.superclass.createDropMenu.call(this, id, s);

						// Modify returned result
						var orig = res.showMenu;
						res.showMenu = function(x, y, px) {
							orig.call(this, x, y, px);
							Ext.fly('menu_' + this.id).setStyle("z-index", 200001);
						};

						return res;
					},

					/** ----------------------------------------------------------
					*/
					createColorSplitButton: function(id, s) {
						// Call base method
						var res = ControlManager.superclass.createColorSplitButton.call(this, id, s);

						// Modify returned result
						var orig = res.showMenu;
						res.showMenu = function(x, y, px) {
							orig.call(this, x, y, px);
							Ext.fly(this.id + '_menu').setStyle("z-index", 200001);
						};

						return res;
					}
				});

				// Init TinyMCE
				var s = {
					mode: "none",
					plugins: Ext.ux.TinyMCE.tinymcePlugins,
					theme: "advanced"
				};
				Ext.apply(s, settings);

				if (!tinymce.dom.Event.domLoaded)
					tinymce.dom.Event._pageInit();

				tinyMCE.init(s);
				tmceInitialized = true;
			}
		}
	});

	Ext.ComponentMgr.registerType("tinymce", Ext.ux.TinyMCE);

})();
