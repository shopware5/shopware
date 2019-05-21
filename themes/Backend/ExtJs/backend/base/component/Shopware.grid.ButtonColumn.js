//Ext.require("Ext.button.Button");

Ext.define('Ext.ux.ButtonColumnMenuItem', {
    extend: 'Ext.menu.Item',

    setState: function (state) {
        this.state = state;
    },

    onClick: function (e) {
        var me = this;

        if (!me.href) {
            e.stopEvent();
        }

        if (me.disabled) {
            return;
        }

        if (me.hideOnClick) {
            me.deferHideParentMenusTimer = Ext.defer(me.deferHideParentMenus, me.clickHideDelay, me);
        }
        /*This is the only difference, we pass state as 2nd argument*/
        Ext.callback(me.handler, me.scope || me, [me, me.state, e]);
        me.fireEvent('click', me, e);

        if (!me.hideOnClick) {
            me.focus();
        }
    }
});


Ext.define('Ext.ux.ButtonColumn', {
    extend: 'Ext.grid.column.Column',
    alias: ['widget.buttoncolumn'],
    requires:['Ext.button.Button'],

    /* @cfg { String }  buttonText
     * If defined, will be button text ,otherwise underlying store value will be used
     */

    /**
     * @cfg { String } iconCls
     * A CSS class to apply to the button. To determine the class dynamically, configure the Column with
     * a `{ @link #getClass }` function.
     */



    /**
     * @cfg { Function } handler
     * A function called when the button is clicked.
     * @cfg { Ext.view.Table } handler.view The owning TableView.
     * @cfg { Number } handler.rowIndex The row index clicked on.
     * @cfg { Number } handler.colIndex The column index clicked on.
     * @cfg { Object } handler.item The clicked item (or this Column if multiple { @link #items} were not configured).
     * @cfg { Event } handler.e The click event.
     */

    /**
     * @cfg { Object } scope
     * The scope (**this** reference) in which the `{ @link #handler}` and `{ @link #getClass}` fuctions are executed.
     * Defaults to this Column.
     */


    /**
     * @cfg { Function } isDisabledFn
     * is an 'interceptor' method which can be used to disable button.
     * @cfg { Object} isDisabledFn.value The data value for the current cell
     * @cfg { Object} isDisabledFn.metaData A collection of metadata about the current cell;
     * @cfg { Ext.data.Model} isDisabledFn.record The record for the current row
     * @cfg { Number} isDisabledFn.rowIndex The index of the current row
     * @cfg { Number} isDisabledFn.colIndex The index of the current column
     * @cfg { Ext.data.Store} isDisabledFn.store The data store
     * @cfg { Ext.view.View} isDisabledFn.view The current view
     * @cfg { Boolean}isDisabledFn.return The disabled flag.
     */

    /**
     * @cfg { Object[] } items
     * An Array which may contain multiple menuItem actions definitions
     **/

    /**
     * @cfg { Function } setupMenu
     * is a 'hook' method which called to generate drop down menu for the record. The items config will be ignored
     * @cfg { Object} setupMenu.record The record for the current row
     * @cfg { Object} setupMenu.recordIndex The index of the current row
     * @cfg Ext.menu.Item[]/Ext.Action[]/Object[] setupMenu.return array of menuItems config options.
     */


    /*
     * @cfg { Boolean} [stopSelection=true]
     * Prevent grid _row_ selection upon mousedown.
     */

    header: '&#160;',

    /**
     * @cfg { String} menuAlign
     * The position to align the menu to (see { @link Ext.Element#alignTo} for more details).
     */
    menuAlign: 'tl-bl?',

    extMinor:Ext.getVersion().getMinor(),

    sortable: false,

    /**
     * @cfg { String} [baseCls='x-btn']
     * The base CSS class to add to all buttons.
     */
    baseCls: Ext.baseCSSPrefix + 'btn',

    /**
     * @cfg { String} arrowAlign
     * The side of the Button box to render the arrow if the button has an associated { @link #cfg-menu}. Two
     * values are allowed:
     *
     * - 'right'
     * - 'bottom'
     */
    arrowAlign: 'right',

    /**
     * @cfg { String} arrowCls
     * The className used for the inner arrow element if the button has a menu.
     */
    arrowCls: 'split',


    /**
     * @cfg { String} textAlign
     * The text alignment for this button (center, left, right).
     */
    textAlign: 'center',

    btnRe: new RegExp(Ext.baseCSSPrefix + 'btn'),

    triggerRe: new RegExp(Ext.baseCSSPrefix + 'btn-split'),

    constructor: function (config) {
        var me = this,
            cfg = Ext.apply({}, config),
            items = cfg.items;

        // This is a Container. Delete the items config.
        delete cfg.items;
        me.callParent([cfg]);
        //init menu
        if (items || me.setupMenu) {
            this.menu = Ext.create('Ext.menu.Menu');
            me.split = true;
            if (items) {
                var i, l = items.length
                for (i = 0; i < l; i++) {
                    this.menu.add(new Ext.ux.ButtonColumnMenuItem(items[i]));
                }
            }
        }
        //init template
        me.initBtnTpl();
        me.renderer = function (v, meta, record) {
            var data = {};
            data.tooltip = me.tooltip ? Ext.String.format('data-qtip="{literal}{0}{/literal}"', me.tooltip) : '';
            data.iconCls = Ext.isFunction(me.getClass) ? me.getClass.apply(me, arguments) : (me.iconCls || 'x-hide-display');
            //allocate place for icon on button
            data.iconClsBtn = data.iconCls === 'x-hide-display' ? me.getBtnGroupCls('noicon').join(' ') : me.getBtnGroupCls('icon-text-left').join(' ');
            data.disabledCls = me.isDisabledFn && me.isDisabledFn.apply(me,
                arguments) ? me.disabledCls + ' ' + me.getBtnGroupCls('disabled').join(' ')/*(Ext.isIE7 ? me.disabledCls : me.disabledCls + ' ' + me.getBtnCls('disabled').join(' '))*/ : '';
            v = Ext.isFunction(cfg.renderer) ? cfg.renderer.apply(this, arguments) : v;
            data.text = Ext.isEmpty(v) ? me.buttonText || '&#160;': v;
            // Apply the renderData to the template args
            Ext.applyIf(data, me.getTemplateArgs());
            return me.btnTpl.apply(data);
        };
    },


    getTemplateArgs: function () {
        var me = this;
        return {
            id:Ext.id(),
            href:false,
            type:'button',
            /*Need empty values to avoid XTemplate undefined error
             */
            glyph:'',
            iconUrl:'',
            baseCls: me.baseCls,
            splitCls: me.getSplitCls(),
            btnCls: me.extMinor === 1 ? me.getBtnCls() :''
        };
    },




    //private
    initBtnTpl: function () {
        var me = this,
            mainDivStr = '<div class="x-btn x-btn-default-small {literal}{iconClsBtn} {disabledCls}{/literal}"{literal} {tooltip}{/literal}>{literal}{0}{/literal}</div>',
            btnFrameTpl = '<TABLE  class="x-table-plain" cellPadding=0><TBODY><TR>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: 0px -6px"' : '') + ' class="x-frame-tl x-btn-tl x-btn-default-small-tl" role=presentation></TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="BACKGROUND-POSITION: 0px 0px; HEIGHT: 3px"' : '')  + ' class="x-frame-tc x-btn-tc x-btn-default-small-tc" role=presentation></TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: right -9px"' : '')  + ' class="x-frame-tr x-btn-tr x-btn-default-small-tr" role=presentation></TD>' +
                '</TR><TR>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: 0px 0px"' : '')  + ' class="x-frame-ml x-btn-ml x-btn-default-small-ml" role=presentation></TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="BACKGROUND-POSITION: 0px 0px"' : '')  + ' class="x-frame-mc x-btn-mc x-btn-default-small-mc" role=presentation>' +
                '{literal}{0}{/literal}' +
                '</TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: right 0px"' : '')  + ' class="x-frame-mr x-btn-mr x-btn-default-small-mr" role=presentation></TD>' +
                '</TR><TR>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: 0px -12px"' : '')  + ' class="x-frame-bl x-btn-bl x-btn-default-small-bl" role=presentation></TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="BACKGROUND-POSITION: 0px -3px; HEIGHT: 3px"' : '')  + ' class="x-frame-bc x-btn-bc x-btn-default-small-bc" role=presentation></TD>' +
                '<TD'+ (me.extMinor != 2 ? ' style="PADDING-LEFT: 3px; BACKGROUND-POSITION: right -15px"' : '')  + ' class="x-frame-br x-btn-br x-btn-default-small-br" role=presentation></TD>' +
                '</TR></TBODY></TABLE>'
        if (Ext.supports.CSS3BorderRadius) {
            me.btnTpl = Ext.create('Ext.XTemplate', Ext.String.format(mainDivStr, me.btnTpl))
        } else {
            me.btnTpl = Ext.create('Ext.XTemplate', Ext.String.format(Ext.String.format(mainDivStr, btnFrameTpl), me.btnTpl));
        }
    },

    //private
    getBtnGroupCls: function (suffix) {
        var cls = ['', 'btn-', 'btn-default-', 'btn-default-small-'],
            i, l;
        for (i = 0, l = cls.length; i < l; i++) {
            cls[i] = Ext.baseCSSPrefix + cls[i] + suffix;
        }
        return cls;
    },


    showMenu: function (el) {
        var me = this;
        if (me.rendered && me.menu) {
            if (me.menu.isVisible()) {
                me.menu.hide();
            }
            me.menu.showBy(el, me.menuAlign);
        }
        return me;
    },


    destroy: function () {
        delete this.items;
        delete this.renderer;
        Ext.destroy(this.menu);
        return this.callParent(arguments);
    },

    /**
     * @private
     * Process and refire events routed from the GridView's processEvent method.
     * Also fires any configured click handlers. By default, cancels the mousedown event to prevent selection.
     * Returns the event handler's status to allow canceling of GridView's bubbling process.
     */
    processEvent: function (type, view, cell, recordIndex, cellIndex, e) {
        var me = this,
            target = e.getTarget();
        btnMatch = target.className.match(me.btnRe) || target.localName == 'button' || target.nodeName == 'BUTTON',
            triggerMatch = target.className.match(me.triggerRe);
        /* mouseover && mouseout doesn't work in 4.2 -just 'mouseout' get fired then we enter cell, no events fired if move mouse inside grid cell
         * I have to reset { @link Ext.view.View} mouseOverItem attribute - this is only way to make events fired correctly*/
        if(me.extMinor === 2 && !view.mouseOverOutBuffer){
            view.mouseOverItem = undefined;
        }
        //Ext.log("EVENT TYPE: " + e.type);
        if (btnMatch) {
            var btnEl = Ext.fly(cell).down('div.x-btn');
            if (btnEl.hasCls(me.disabledCls)) {
                return me.stopSelection !== true;
            }
            if (type == 'click') {
                btnEl.removeCls(me.getBtnGroupCls('over'));
                if (triggerMatch) {
                    var record = view.getStore().getAt(recordIndex),
                        menuItems,
                        menu = me.menu;
                    if (me.setupMenu) {
                        menuItems = me.setupMenu.call(me.setupMenuScope || me, record, recordIndex);
                        menu.removeAll(true);
                        var i, l = menuItems.length;
                        for (i = 0; i < l; i++) {
                            menu.add(menuItems[i]);
                        }
                    } else {
                        menuItems = menu.items;
                        menuItems.each(function (item) {
                            item.setState({
                                view: view,
                                record: record,
                                rowIndex: recordIndex
                            });
                        }, me);
                    }
                    me.showMenu(btnEl);
                } else {
                    if (me.handler) {
                        me.handler.call(me.scope || me, view, recordIndex, cellIndex, e);
                    }
                }
                /* mouseover && mouseout doesn't work good in 4.2 with  mouseover buffering */
            } else if (type == 'mouseover' && (me.extMinor !== 2 || (me.extMinor === 2 && !view.mouseOverOutBuffer))) {
                if(!me.menu || !me.menu.isVisible()){
                    btnEl.addCls(me.getBtnGroupCls('over'));
                }
            } else if (type == 'mouseout' && (me.extMinor !== 2 || (me.extMinor === 2 && !view.mouseOverOutBuffer))) {
                btnEl.removeCls(me.getBtnGroupCls('over'));
            }
            else if (type == 'mousedown') {
                btnEl.addCls(me.getBtnGroupCls('pressed'));
                return me.stopSelection !== true;
            } else if (type == 'mouseup') {
                btnEl.removeCls(me.getBtnGroupCls('pressed'));
            }
        }
        return me.callParent(arguments);
    },

    cascade: function (fn, scope) {
        fn.call(scope || this, this);
    },

    // private
    getRefItems: function (deep) {
        var menu = this.menu,
            items;
        if (menu) {
            items = menu.getRefItems(deep);
            items.unshift(menu);
        }
        return items || [];
    }
}, function() {
    var buttonPrototype = Ext.button.Button.prototype;
    //borrow buttons tpl
    this.prototype.btnTpl = Ext.isArray(buttonPrototype.renderTpl) ? buttonPrototype.renderTpl.join('') : buttonPrototype.renderTpl;
    //borrow buttons methods
    this.prototype.getSplitCls = buttonPrototype.getSplitCls;
    this.prototype.getBtnCls= buttonPrototype.getBtnCls;
});
