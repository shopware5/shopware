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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/designer"}
Ext.define('Shopware.apps.Emotion.view.detail.Designer', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.emotion-detail-designer',

    bodyPadding: '20 0',

    border: false,
    bodyStyle: {
        border: '0 none'
    },

    overflowX: 'auto',
    overflowY: 'auto',

    cls: Ext.baseCSSPrefix + 'emotion-designer-container',

    // Matching the container width in the frontend
    // for better relation between editing and live view.
    basicGridWidth: 1160,

    snippets: {
        preview: '{s name="toolbar/preview"}{/s}',
        desktop: '{s name="viewports/xl/name"}{/s}',
        tabletLandscape: '{s name="viewports/l/name"}{/s}',
        tabletPortrait: '{s name="viewports/m/name"}{/s}',
        mobileLandscape: '{s name="viewports/s/name"}{/s}',
        mobilePortrait: '{s name="viewports/xs/name"}{/s}',
        hiddenElTooltip: '{s name="viewports/hiddenElements/tooltip"}{/s}',
        copyViewportTooltip: '{s name="toolbar/copyViewportTooltip"}{/s}',
        masterViewportTooltip: '{s name="toolbar/masterViewportTooltip"}{/s}',
        disconnectLabel: '{s name="viewports/disconnect/label"}{/s}',
        connectAlertTitle: '{s name="viewports/connect_alert/title"}{/s}',
        connectAlertMsg: '{s name="viewports/connect_alert/msg"}{/s}',
        notActiveViewportTooltip: '{s name="viewports/inactive/tooltip"}{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('openSettingsWindow');

        me.activeHiddenElements = false;
        me.counterChange = [];

        me.toolbar = me.createToolbar();
        me.hiddenElements = me.createHiddenElementsContainer();
        me.grid = me.createGridView();
        me.preview = me.createPreview();

        me.dockedItems = [
            me.toolbar,
            me.hiddenElements
        ];

        me.items = [
            me.grid,
            me.preview
        ];

        me.on({
            'scope': me,
            'afterrender': me.onAfterRender
        });

        me.callParent(arguments);
    },

    onAfterRender: function() {
        var me = this;

        if (me.activePreview) {
            me.showPreview();
        }

        me.toolbar.getEl().on({
            'click': {
                delegate: '.x-designer-viewport-label',
                fn: me.onViewportClick,
                scope: me
            }
        });

        me.toolbar.getEl().on({
            'click': {
                delegate: '.x-viewport-hidden-elements-counter.is--master',
                fn: me.onHiddenCounterClick,
                scope: me
            }
        });

        me.toolbar.getEl().on({
            'click': {
                delegate: '.x-viewport-connect-trigger',
                fn: me.onConnectTriggerClick,
                scope: me
            }
        });

        me.toolbar.getEl().on({
            'click': {
                delegate: '.x-designer-disconnect-btn',
                fn: me.onDisconnectBtnClick,
                scope: me
            }
        });

        me.toolbar.getEl().on({
            'click': {
                delegate: '.x-designer-preview-btn',
                fn: me.onPreviewBtnClick,
                scope: me
            }
        });
    },

    onViewportClick: function(event) {
        var me = this,
            element = Ext.get(event.target),
            state = element.getAttribute('data-viewport') || 'xl',
            isStateConnection;

        if (me.activePreview) {
            var viewport = me.viewportStore.findRecord('alias', state);

            me.preview.changePreview(viewport);
        }

        isStateConnection = me.grid.stateConnections.indexOf(state);

        if (isStateConnection === -1) {
            me.grid.stateConnections = [ state ];
        }

        me.grid.state = state;
        me.grid.refresh();
    },

    onHiddenCounterClick: function() {
        var me = this;

        if (!me.activePreview) {
            me.activeHiddenElements = !me.hiddenElements.isVisible();
            me.hiddenElements.setVisible(me.activeHiddenElements);
            me.grid.refresh();
        }
    },

    onConnectTriggerClick: function(event) {
        var me = this,
            element = Ext.get(event.target),
            state = element.getAttribute('data-viewport'),
            isStateConnection;

        if (!state) {
            return false;
        }

        if (state === me.grid.state) {
            me.grid.stateConnections = [ state ];
            me.grid.refresh();
            return false;
        }

        isStateConnection = me.grid.stateConnections.indexOf(state);

        if (isStateConnection !== -1) {
            me.grid.stateConnections.splice(isStateConnection, 1);
            me.grid.refresh();
        } else {
            if (!me.grid.checkSameViewportSettings(state, me.grid.state)) {
                Ext.MessageBox.confirm(
                    me.snippets.connectAlertTitle,
                    me.snippets.connectAlertMsg,
                    function(response) {
                        if (response !== 'yes') {
                            return false;
                        }

                        me.grid.copyViewportElements(state, me.grid.state);
                        me.grid.stateConnections.push(state);
                        me.grid.refresh();
                    }
                );
            } else {
                me.grid.stateConnections.push(state);
                me.grid.refresh();
            }
        }
    },

    onDisconnectBtnClick: function() {
        var me = this;

        me.grid.stateConnections = [ me.grid.state ];
        me.grid.refresh();
    },

    onPreviewBtnClick: function() {
        var me = this,
            state = me.grid.state || 'xl',
            viewport;

        if (me.activePreview) {
            me.closePreview();
            return;
        }

        viewport = me.viewportStore.findRecord('alias', state);

        me.showPreview(viewport);
    },

    showPreview: function(previewViewport) {
        var me = this,
            viewport = previewViewport || me.viewportStore.findRecord('alias', 'xl');

        me.activePreview = true;
        me.fireEvent('preview', me, viewport, me.emotion);
        me.grid.refresh();
    },

    closePreview: function() {
        var me = this;

        me.activePreview = false;
        me.fireEvent('closePreview');
        me.grid.refresh();
    },

    createGridView: function() {
        var me = this,
            stateConnections = [ 'xl' ];

        /**
         * All viewports are initially connected when the emotion has no elements.
         */
        if (me.emotion.getElements().getCount() === 0) {
            stateConnections = [ 'xs', 's', 'm', 'l', 'xl' ];
        }

        return me.grid = Ext.create('Shopware.apps.Emotion.view.detail.Grid', {
            emotion: me.emotion,
            toolbar: me.toolbar,
            hiddenElements: me.hiddenElements,
            viewportStore: me.viewportStore,
            basicGridWidth: me.basicGridWidth,
            state: 'xl',
            stateConnections: stateConnections,
            designer: me
        });
    },

    createPreview: function() {
        var me = this;

        return me.preview = Ext.create('Shopware.apps.Emotion.view.detail.Preview', {
            designer: me,
            emotion: me.emotion,
            basicGridWidth: me.basicGridWidth
        });
    },

    createHiddenElementsContainer: function() {
        var me = this;

        return me.hiddenElements = Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'viewport-hidden-elements',
            dock: 'top',
            overflowX: 'auto',
            overflowY: 'hidden',
            hidden: !me.activeHiddenElements
        });
    },

    createToolbar: function() {
        var me = this;

        me.addEvents('preview', 'closePreview');

        return me.toolbar = Ext.create('Ext.view.View', {
            store: me.createViewportStore(),
            tpl: me.getToolbarTpl(),
            cls: 'x-designer-toolbar',
            itemSelector: '.x-designer-viewport',
            blockRefresh: true,
            dock: 'top'
        });
    },

    getToolbarTpl: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}',

                '<div class="x-designer-actions-toolbar">',
                    '<button data-qtip="{[this.getPreviewBtnTooltip()]}" class="{[this.getPreviewBtnCls()]}">',
                        '<span class="preview-btn-toggle"></span>' + me.snippets.preview + '',
                    '</button>',

                    '<button class="{[this.getDisconnectBtnCls()]}">',
                        '<span class="disconnect-btn-icon"></span>' + me.snippets.disconnectLabel,
                    '</button>',
                '</div>',

                '<div class="x-designer-viewports-toolbar">',
                    '<div class="x-designer-viewports">',
                        '<tpl for=".">',
                            '<div class="x-designer-viewport">',
                                '<div class="{[this.getViewportBtnCls(values.alias)]}">',
                                    '<div class="{[this.getLabelCls(values.deviceId)]}" data-viewport="{alias}" data-qtip="{[this.getLabelTooltip(values.deviceId)]}">{label}</div>',
                                    '<div class="{[this.getCounterCls(values.alias, values.hiddenCounter, values)]}" data-viewport="{alias}" data-qtip="{[this.getHiddenElTooltip()]}">',
                                        '<span class="counter--value">{hiddenCounter}</span>',
                                    '</div>',
                                    '<div class="{[this.getConnectCls(values.alias)]}" data-viewport="{alias}" data-qtip="{[this.getConnectTooltip(values.alias)]}"></div>',
                                '</div>',
                            '</div>',
                        '</tpl>',
                    '</div>',
                '</div>',

                '<div class="x-designer-viewport-lines">',
                    '<div class="x-designer-viewport-base-line">',
                        '<tpl for=".">',
                            '<div class="{[this.getLineCls(values.alias)]}" style="width: {[values.maxWidth - values.minWidth]}px; left: {minWidth}px;">',
                                '<div class="x-designer-viewport-line-inner"></div>',
                            '</div>',
                        '</tpl>',
                    '</div>',
                '</div>',
            '{/literal}',
            {
                getPreviewBtnTooltip: function() {
                    if (me.emotion.get('id')) {
                        return '';
                    }

                    return '{s name="preview/must_exists"}{/s}';
                },

                getPreviewBtnCls: function() {
                    var cls = 'x-designer-preview-btn';

                    if (me.activePreview) {
                        cls += ' is--active'
                    }

                    if (!me.emotion.get('id')) {
                        cls += ' is--disabled';
                    }

                    return cls;
                },

                getDisconnectBtnCls: function() {
                    var cls = 'x-designer-disconnect-btn';

                    if (me.activePreview || me.grid.stateConnections.length <= 1) {
                        cls += ' is--disabled';
                    }

                    return cls;
                },

                getViewportBtnCls: function(alias) {
                    var cls = 'x-designer-viewport-btn viewport-' + alias;

                    if (alias === me.grid.state) {
                        cls += ' is--active';
                    }

                    return cls;
                },

                getLabelCls: function(deviceId) {
                    var cls = 'x-designer-viewport-label',
                        device = me.emotion.get('device') || '0,1,2,3,4';

                    if (device.indexOf(deviceId) === -1) {
                        cls += ' is--not-active'
                    }

                    return cls;
                },

                getLabelTooltip: function (deviceId) {
                    var tooltip = '',
                        device = me.emotion.get('device') || '0,1,2,3,4';

                    if (device.indexOf(deviceId) === -1) {
                        tooltip = me.snippets.notActiveViewportTooltip
                    }

                    return tooltip;
                },

                getCounterCls: function(alias, counter, values) {
                    var cls = 'x-viewport-hidden-elements-counter counter-' + alias;

                    if (alias === me.grid.state) {
                        cls += ' is--master';

                        if (me.activeHiddenElements) {
                            cls += ' is--active';
                        }
                    }

                    if (me.counterChange.indexOf(alias) !== -1) {
                        cls += ' is--counter-change';
                    }

                    if (me.activePreview) {
                        cls += ' is--hidden';
                    }

                    return cls;
                },

                getConnectCls: function(alias) {
                    var cls = 'x-viewport-connect-trigger trigger-' + alias;

                    if (alias === me.grid.state) {
                        cls += ' is--master';
                    }

                    if (me.grid.stateConnections.indexOf(alias) !== -1 && me.grid.stateConnections.length > 1) {
                        cls += ' is--active';
                    }

                    if (me.activePreview) {
                        cls += ' is--hidden';
                    }

                    return cls;
                },

                getLineCls: function(alias) {
                    var cls = 'x-designer-viewport-line line-' + alias;

                    if (alias === me.grid.state) {
                        cls += ' is--active';

                    } else if(me.grid.stateConnections.indexOf(alias) !== -1) {
                        cls += ' is--connected';
                    }

                    return cls;
                },

                getHiddenElTooltip: function() {
                    return me.snippets.hiddenElTooltip;
                },

                getConnectTooltip: function(alias) {
                    var tipCopy = me.snippets.copyViewportTooltip,
                        tipMaster = me.snippets.masterViewportTooltip;

                    return (alias !== me.grid.state) ? tipCopy : tipMaster;
                }
            }
        );
    },

    createViewportStore: function () {
        var me = this;

        return me.viewportStore = Ext.create('Ext.data.Store', {
            fields: ['deviceId', 'alias', 'label', 'minWidth', 'maxWidth', 'hiddenCounter'],
            data: [
                { deviceId: '4', alias: 'xs',  label: me.snippets.mobilePortrait,  minWidth: 320,  maxWidth: 459,  hiddenCounter: 0 },
                { deviceId: '3', alias: 's',   label: me.snippets.mobileLandscape, minWidth: 460,  maxWidth: 707,  hiddenCounter: 0 },
                { deviceId: '2', alias: 'm',   label: me.snippets.tabletPortrait,  minWidth: 708,  maxWidth: 963,  hiddenCounter: 0 },
                { deviceId: '1', alias: 'l',   label: me.snippets.tabletLandscape, minWidth: 964,  maxWidth: 1159, hiddenCounter: 0 },
                { deviceId: '0', alias: 'xl',  label: me.snippets.desktop,         minWidth: 1160, maxWidth: 1190, hiddenCounter: 0 }
            ]
        });
    }
});
//{/block}
