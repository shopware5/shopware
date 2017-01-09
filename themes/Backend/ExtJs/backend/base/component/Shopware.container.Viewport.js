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
 * Shopware UI - Viewport
 *
 * This components creates a special viewport which
 * adds additional features and methods to the default viewport.
 *
 * The viewport supports now a unlimited number of desktops,
 * provides scrolling or jumping to a specifc desktop.
 *
 * All desktops could be dynamically created and destroyed, so the
 * stack of this component is lightweight and the API is easy to use.
 *
 * ToDo - add stateEvents
 */

//{namespace name=base/login/view/main}
Ext.define('Shopware.container.Viewport',
/** @lends Ext.container.Container# */
{
    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.container.Viewport',

    /**
     * Short alias name for class names.
     * @string
     */
    alias: 'widget.sw4viewport',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [
        'Ext.EventManager',
        'Ext.util.MixedCollection'
    ],

    /**
     * Defines an alternate name for this class.
     * @string
     */
    alternateClassName: 'Shopware.Viewport',

    /**
     * Indicates that this container is a Viewport.
     * @boolean
     */
    isViewport: true,

    /**
     * Name of the aria role.
     * @string
     */
    ariaRole: 'application',

    /**
     * CSS class for the SW4 Viewport.
     * @string
     */
    cssBaseCls: Ext.baseCSSPrefix + 'viewport',

    /**
     * Property which holds all created desktops
     * @object
     */
    desktops: Ext.create('Ext.util.MixedCollection'),

    /**
     * Property which holds the active desktop index. Note that the index starts from 0.
     * @default null
     * @integer
     */
    activeDesktop: null,

    /**
     * Should the Viewport scrollable with the mouse wheel. Note that
     * this could causes errors in Safari and Chrome (all Webkit based browsers).
     * @boolean
     */
    scrollable: true,

    /**
     * Scroll animation duration
     * @integer
     */
    scrollDuration: 500,

    /**
     * Scroll animation easing. See all available easing
     * types at http://docs.sencha.com/ext-js/4-0/#!/api/Ext.fx.Easing
     * @string
     */
    scrollEasing: 'ease',

    /**
     * Component type of the desktops.
     * @string
     */
    desktopComponentName: 'Ext.container.Container',

    /**
     * Names of the default desktops.
     * @integer
     */
    defaultDesktopNames: [
        'Dashboard'
    ],

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     * @boolean
     */
    stateful: true,

    /**
     * The unique id for this object to use for state management purposes.
     * @boolean
     */
    stateId: 'sw4-viewport',

    afterRender: function() {
        var me = this;

        var appCls = Ext.ClassManager.get('Shopware.app.Application');
        appCls.baseComponentIsReady(me);

        me.callParent(arguments);
    },

    /**
     * Initializes the special SW 4 Viewport component which
     * supports multiple desktops and additional events compared
     * to the default one.
     *
     * @public
     * @return void
     */
    initComponent : function() {
        var me = this,
            html = Ext.fly(document.body.parentNode),
            el;

        // Register new events to provide an easier handling of the desktops.
        me.registerEvents();
        me.callParent(arguments);

        el = Ext.getBody();
        el.setHeight = Ext.emptyFn;
        el.dom.scroll = 'no';
        el.dom.scrollWidth = Ext.Element.getViewportWidth();
        html.dom.scroll = 'no';
        html.dom.scrollWidth = Ext.Element.getViewportWidth();
        html.scrollLeft = 0;

        // Prevent ExtJS errors on containers
        me.allowDomMove = false;
        Ext.EventManager.onWindowResize(me.fireResize, me);
        me.renderTo = el;

        // Stops the render queue
        Ext.suspendLayouts();

        html.addCls(me.cssBaseCls);
        html.setStyle('position', 'relative');
        html.setStyle('left', '0px');
        html.setStyle('overflow', 'hidden');

        el.setStyle('left', '0px');
        el.setStyle('overflow', 'hidden');

        Ext.resumeLayouts(true);

        me.el = el;

        // The viewport is now setup so we can create the default desktop.
        me.createDefaultDesktops();
        me.resizeViewport();

        me.createHiddenLayer();
    },

    /**
     * Creates an hidden layer which catches the events,
     * so the performance should be better then
     * moving windows.
     *
     * @public
     */
    createHiddenLayer: function() {
        var me = this;

        me.hiddenLayer = new Ext.dom.Element(document.createElement('div'));
        me.hiddenLayer.set({
            'class': Ext.baseCSSPrefix + 'hidden-layer',
            style: {
                position: 'fixed',
                top: 0,
                left: 0,
                width: Ext.Element.getViewportWidth() + 'px',
                height: Ext.Element.getViewportHeight() + 'px'
            }
        });
    },

    /**
     * Helper method which returns the hidden layer
     *
     * @public
     * @return [object] Ext.dom.Element
     */
    getHiddenLayer: function() {
        var me = this;
        if(!me.hiddenLayer) {
            me.createHiddenLayer();
        }

        return me.hiddenLayer;
    },

    /**
     * Adds additional events to the viewport which are helpful
     * to handle the desktop switching.
     *
     * @private
     * @return void
     */
    registerEvents: function() {
        this.addEvents(

            /**
             * Will be fired when a new desktop is added.
             *
             * @event createdesktop
             * @param [object] this - Shopware.container.Viewport
             * @param [object] desktop - created desktop
             */
            'createdesktop',

            /**
             * Will be fired when the active desktop is changed.
             *
             * @event changedesktop
             * @param [object] this - Shopware.container.Viewport
             * @param [integer] index - index of the new desktop
             * @param [object] newDesktop - the new active desktop object
             * @param [integer] oldDesktopIndex - index of the old desktop
             * @param [object] oldDesktop - the old desktop object
             */
            'changedesktop',

            /**
             * Will be fired when a desktop is removed.
             *
             * @event removedesktop
             * @param [object] this - Shopware.container.Viewport
             * @param [object] removedDesktop - object of the removed desktop
             */
            'removedesktop',

            /**
             * Will be fired when the Viewport will be resized.
             *
             * @event resizeviewport
             * @param [object] this - Shopware.container.Viewport
             * @param [integer] width - new width of the Viewport
             * @param [integer] height - new height of the Viewport
             */
            'resizeviewport',

            /**
             * Will be fired before the Viewport is scrolled.
             *
             * @event beforescroll
             * @param [object] this - Shopware.container.Viewport
             * @param [object] animation - Ext.util.Anim
             * @param [integer] new active desktop
             */
            'beforescroll',

            /**
             * Will be fired after the Viewport is scrolled.
             *
             * @event afterscroll
             * @param [object] this - Shopware.container.Viewport
             * @param [object] animation - Ext.util.Anim
             * @param [integer] new active desktop
             */
            'afterscroll'
        );
    },

    /**
     * Resizes the whole Viewport and all containing desktops.
     *
     * @private
     * @param [integer] w - new width of the viewport
     * @param [integer] h - new height of the viewport
     */
    fireResize : function(w, h) {
        var me = this;
        // setSize is the single entry point to layouts
        me.el.setSize(w * (me.getDesktopCount() || 1), h);
        me.setSize(w * (me.getDesktopCount() || 1), h);

        Ext.each(this.desktops.items, function(desktop) {
            // Create the spacing of the main & footer toolbar using the "-80"
            desktop.setSize(w, h - 80);
        });

        Ext.defer(me._rearrangeVisibleWindows, 5, this);
    },

    /**
     * Rearranges the position of the windows and handles
     * the resizing of the windows when they're in full screen
     * mode (= maximized).
     *
     * @private
     */
    _rearrangeVisibleWindows: function() {
        var activeWindows = Shopware.app.Application.getActiveWindows();
        Ext.each(activeWindows, function(win) {
            if(win.hidden) {
                return;
            }

            var position = win.getPosition(),
                size = win.getSize();

            win.center();
            win.setPosition(position[0], (win.maximized) ? 0 : 15, false);

            if(win.maximized) {
                size.height -= 50;
                win.setSize(size);
            }
        });
    },

    /**
     * Resizes the Viewport to match the containing number of desktops.
     *
     * @private
     * @return [array] Array containing the new Viewport size.
     */
    resizeViewport: function() {
        var me = this,
            size = me.getViewportSize(),
            width = size[0],
            height = size[1];

        me.el.setSize(width, height);

        me.fireEvent('resizeviewport', me, width, height);

        return size;
    },

    /**
     * Returns the actual size of the Viewport.
     *
     * @private
     * @return [array] Array containing the Viewport size.
     */
    getViewportSize: function() {
        var me = this,
            width = Ext.Element.getViewportWidth() * (me.getDesktopCount() || 1),
            height = Ext.Element.getViewportHeight();

        return [ width, height ];
    },

    /**
     * Creates the default desktops.
     *
     * @private
     * @return void
     */
    createDefaultDesktops: function() {
        var me = this;

        me.activeDesktop = 0;

        Ext.suspendLayouts();

        me.createDesktop(me.defaultDesktopNames[me.activeDesktop]);

        Ext.resumeLayouts(true);
    },

    /**
     * Returns the founded desktop based on the passed index.
     *
     * @public
     * @param [integer] index - Position of the desktop
     * @return [object|null] founded desktop
     */
    getDesktop: function(index) {
        return this.desktops.getAt(index);
    },

    /**
     * Returns the position of the passed desktop.
     *
     * @public
     * @param [object] desktop
     * @return [integer] index of the desktop or -1
     */
    getDesktopPosition: function(desktop) {
        return this.desktops.indexOf(desktop);
    },

    /**
     * Returns the number of active desktops.
     *
     * @public
     * @return [integer] count of the active desktops
     */
    getDesktopCount: function() {
        return this.desktops.getCount();
    },

    /**
     * Creates a new desktop based on an Ext.container.Container
     *
     * @public
     * @param [string] title - title of the desktop
     * @return [object] created desktop - Ext.container.Container
     */
    createDesktop: function(title) {
        var me = this,
            desktop = Ext.create(me.desktopComponentName, {
            renderTo: me.getEl(),
            region: 'center',
            x: 0,
            y: 40,
            width: Ext.Element.getViewportWidth(),
            height: Ext.Element.getViewportHeight() - 80,
            layout: 'fit',
            title: title,
            floating: true,
            style: 'z-index: 10',
            cls: 'desktop-pnl'
        });

        me.desktops.add(desktop);

        me.fireEvent('createdesktop', me, desktop);

        me.resizeViewport();

        return desktop;
    },

    /**
     * Removes a desktop from the Viewport.
     *
     * @public
     * @param [integer|object] desktop - index of the desktop to remove or the desktop object
     * @return [object] removed desktop - object of the removed desktop
     */
    removeDesktop: function(desktop) {
        var me = this, removedDesktop;
        if(Ext.isNumeric(desktop)) {
            removedDesktop = me.getDesktop(desktop);
            me.desktops.removeAt(desktop);
        } else {
            removedDesktop = desktop;
            me.desktops.remove(desktop);
        }
        me.fireEvent('removedesktop', this, removedDesktop);
        me.resizeViewport();

        return desktop;
    },

    /**
     * Sets the active desktop.
     *
     * @public
     * @param [integer] index - index of the active desktop
     * @return [object] newDesktop -  the active desktop component
     */
    setActiveDesktop: function(index) {
        var me = this,
            newDesktop = me.getDesktop(index),
            oldDesktopIndex = me.activeDesktop,
            oldDesktop = me.getDesktop(me.activeDesktop);

        me.activeDesktop = index;
        me.fireEvent('changedesktop', me, index, newDesktop, oldDesktopIndex, oldDesktop);

        // todo - jump to active desktop

        return newDesktop;
    },

    /**
     * Returns the active desktop.
     *
     * @public
     * @return [object] the active desktop.
     */
    getActiveDesktop: function() {
        return this.desktops.getAt(this.getActiveDesktopPosition());
    },

    /**
     * Returns the index of the active desktop.
     *
     * @public
     * @return [integer] active desktop position.
     */
    getActiveDesktopPosition: function() {
        return this.activeDesktop;
    },

    /**
     * Scrolls the Viewport to the left or to the right based
     * on the based direction.
     *
     * @param [string] direction - "left" or "right" available
     * @return void
     */
    scroll: function(direction) {
        var me = this,
            html = Ext.fly(document.body.parentNode),
            width = Ext.Element.getViewportWidth(),
            pos = me.getActiveDesktopPosition();

        if(direction === 'left' && pos - 1 > -1) {
            pos -= 1;
        } else if(direction === 'right' && me.getDesktopCount() > pos + 1) {
            pos += 1;
        } else {
            return false;
        }

        // Now animate the "html"-tag
        html.animate({
            duration: me.scrollDuration,
            easing: me.scrollEasing,
            listeners: {
                beforeanimate: function() {
                    Ext.suspendLayouts();
                    me.fireEvent('beforescroll', me, this, pos);
                },
                afteranimate: function() {
                    Ext.resumeLayouts(true);
                    me.activeDesktop = pos;
                    me.fireEvent('afterscroll', me, this, pos);
                }
            },
            to: { left: -(width * pos) }
        });

        return true;
    },

    jumpTo: function(index, noAnim) {
        var me = this,
            html = Ext.fly(document.body.parentNode),
            width = Ext.Element.getViewportWidth(),
            maxPos = me.getDesktopCount();

        if(noAnim) {
            html.setStyle('left', -(width * index));
            me.activeDesktop = index;
            me.fireEvent('afterscroll', me, this, index);
            return true;
        }

        if(index < 0 && index < maxPos) {
            return false;
        }

        // Retrieve all active Windows
        var activeWindows = Shopware.app.Application.getActiveWindows();

        html.animate({
            duration: me.scrollDuration,
            easing: me.scrollEasing,
            listeners: {
                beforeanimate: function() {
                    Ext.suspendLayouts();
                    me.fireEvent('beforescroll', me, this, index);

                    Ext.each(activeWindows, function(window) {
                        window.el.shadow.hide();
                    });

                },
                afteranimate: function() {
                    Ext.resumeLayouts(true);
                    me.activeDesktop = index;
                    me.fireEvent('afterscroll', me, this, index);

                    Ext.each(activeWindows, function(window) {
                        window.el.shadow.show(window.el);
                    });
                }
            },
            to: { left: -(width * index) }
        });

        return true;
    },

    /**
     * Proxy method which provides an easy interface to scroll to the left.
     *
     * @public
     * @return [boolean]
     */
    scrollLeft: function() {
        return this.scroll('left');
    },

    /**
     * Proxy method which provides an easy interface to scroll to the right.
     *
     * @public
     * @return [boolean]
     */
    scrollRight: function() {
        return this.scroll('right');
    }
});
