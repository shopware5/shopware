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
 */

//{block name="extjs/overrides/rowEditor"}
Ext.override(Ext.grid.RowEditor, {
    style: {
        background: '#eaf1fb'
    },

    getFloatingButtons: function() {
       var me = this,
           cssPrefix = Ext.baseCSSPrefix,
           btnsCss = cssPrefix + 'grid-row-editor-buttons',
           plugin = me.editingPlugin,
           btns;

       if (!me.floatingButtons) {
           btns = me.floatingButtons = new Ext.Container({
               renderTpl: [
                   '{literal}<div class="{baseCls}-ml"></div>',
                   '<div class="{baseCls}-mr"></div>',
                   '<div class="{baseCls}-bl"></div>',
                   '<div class="{baseCls}-br"></div>',
                   '<div class="{baseCls}-bc"></div>',
                   '{%this.renderContainer(out,values)%}{/literal}'
               ],
               width: 200,
               renderTo: me.el,
               baseCls: btnsCss,
               layout: {
                   type: 'hbox',
                   align: 'middle'
               },
               defaults: {
                   flex: 1,
                   margins: '0 1 0 1'
               },
               items: [{
                   itemId: 'update',
                   xtype: 'button',
                   cls: 'primary small',
                   handler: plugin.completeEdit,
                   scope: plugin,
                   text: me.saveBtnText,
                   disabled: !me.isValid,
                   minWidth: Ext.panel.Panel.prototype.minButtonWidth
               }, {
                   xtype: 'button',
                   handler: plugin.cancelEdit,
                   scope: plugin,
                   cls: 'secondary small',
                   text: me.cancelBtnText,
                   minWidth: Ext.panel.Panel.prototype.minButtonWidth
               }]
           });

           // Prevent from bubbling click events to the grid view
           me.mon(btns.el, {
               // BrowserBug: Opera 11.01
               //   causes the view to scroll when a button is focused from mousedown
               mousedown: Ext.emptyFn,
               click: Ext.emptyFn,
               stopEvent: true
           });
       }
       return me.floatingButtons;
   },

    /**
     * This is a version of the default Ext.grid.RowEditor, but with support for the CheckBoxSelectionModel.
     * Usually, when having a CheckBoxSelectionModel with the configuration `clicksToEdit: 1` and the RowEditing plugin
     * active at the same time, you wouldn't be able to select more than one entry at once anymore.
     *
     * This override changes the `startEdit` method and makes sure the other entries are not de-selected again.
     * This is done using a new configuration 'keepExisting' on the editor plugin itself. For compatibility reasons,
     * this is `false` by default.
     *
     * @param { Ext.data.Model } record
     */
    startEdit: function(record) {
        var me = this,
            grid = me.editingPlugin.grid,
            store = grid.store,
            context = me.context = Ext.apply(me.editingPlugin.context, {
                view: grid.getView(),
                store: store
            }),
            keepExisting = me.editingPlugin.keepExisting || false;

        // make sure our row is selected before editing
        context.grid.getSelectionModel().select(record, keepExisting);

        // Reload the record data
        me.loadRecord(record);

        if (!me.isVisible()) {
            me.show();
            me.focusContextCell();
        } else {
            me.reposition({
                callback: this.focusContextCell
            });
        }
    }
});
//{/block}
