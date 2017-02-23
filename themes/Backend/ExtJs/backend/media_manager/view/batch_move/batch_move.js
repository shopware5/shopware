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

//{namespace name=backend/media_manager/view/main}
//{block name="backend/media_manager/view/main/batchMove"}
Ext.define('Shopware.apps.MediaManager.view.batchMove.BatchMove', {
    extend: 'Enlight.app.SubWindow',
    alias: 'widget.batchMove.BatchMove',

    footerButton: false,
    modal: true,

    height: 135,
    width: 400,

    title: '{s name="move/media/title"}{/s}',
    indicatorSnippet: '{s name="move/indicator/snippet"}{/s}',

    initComponent: function() {
        var me = this;

        me.isCanceled = false;
        me.maxIndex = me.mediasToMove.length - 1;
        me.currentIndex = 0;

        me.items = me.createItems();
        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
    },

    /**
     * After render init's the move media batch process
     */
    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        me.startMoveMedia();
    },

    /**
     * @return { Ext.ProgressBar }
     */
    createItems: function() {
        var me = this;

        me.progressBar = Ext.create('Ext.ProgressBar', {
            margin: 20,
            text: Ext.String.format(me.indicatorSnippet, 0, '' + me.mediasToMove.length)
        });

        return me.progressBar;
    },

    /**
     * @return { Ext.toolbar.Toolbar }
     */
    createDockedItems: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            items: me.createToolbarItems()
        });
    },

    /**
     * @return { Array }
     */
    createToolbarItems: function() {
        var me = this;

        return [
            '->',
            me.createCancelButton()
        ]
    },

    /**
     * @return { Ext.button.Button }
     */
    createCancelButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: '{s name="move/media/cancel"}{/s}',
            cls: 'primary',
            handler: Ext.bind(me.onCancelClick, me)
        });
    },

    /**
     * on cancel click set the property isCanceled to true,
     * so we can break the move media process
     *
     * @param { Ext.button.Button } button
     */
    onCancelClick: function(button) {
        var me = this;

        me.isCanceled = true;

        me.updateProgressBar(true);
        button.setDisabled(true);
    },

    startMoveMedia: function() {
        var me = this,
            store = Ext.create('Shopware.apps.MediaManager.store.Media');

        store.add(me.mediasToMove[me.currentIndex]);

        store.sync({
            callback: Ext.bind(me.updateInternal, me)
        });
    },

    updateInternal: function() {
        var me = this;

        me.currentIndex++;
        me.updateProgressBar();
        me.afterUpdateProgressBar();
    },

    updateProgressBar: function(force) {
        var me = this,
            text = Ext.String.format(me.indicatorSnippet, me.currentIndex, '' + me.mediasToMove.length),
            value = (1 / me.mediasToMove.length) * (me.currentIndex),
            force = force || false;

        if (force || me.isCanceled) {
            me.progressBar.updateProgress(value, '{s name="move/media/cancel_message"}{/s}', true);
            return;
        }

        me.progressBar.updateProgress(value, text, true);
    },

    /**
     * Checks if progress is ready or canceled, else start a new process.
     */
    afterUpdateProgressBar: function() {
        var me = this;

        if (me.isCanceled || me.currentIndex > me.maxIndex) {
            me.closeWindow();
            return;
        }

        me.startMoveMedia();
    },

    closeWindow: function() {
        var me = this;

        if (me.progressBar.getActiveAnimation()) {
            Ext.defer(me.closeWindow, 200, me);
            return;
        }

        // Wait a little before destroy the window for a better use feeling
        Ext.defer(me.destroy, 500, me);
        me.updateSourceView();
    },

    /**
     * Updates the MediaManager window. Set is loading to false, reloads the store and fires a refresh event
     * to the albumTree to update the treeView.
     */
    updateSourceView: function() {
        var me = this;

        me.mediaGrid.setLoading(false);
        me.mediaView.setLoading(false);
        me.mediaGrid.getStore().load();

        me.sourceView.fireEvent('refresh', me.sourceView);
    }
});
//{/block}
