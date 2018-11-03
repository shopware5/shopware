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
 * The list controller handles the split view mode
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/split_view"}
Ext.define('Shopware.apps.ArticleList.controller.SplitView', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    // Is the split mode active?
    splitViewMode: false,

    /**
     * Here the dimension / position of the listing window *before* split mode
     * can be saved
     */
    defaultState: {
        x: 0,
        y: 0,
        width: 0,
        height: 0
    },

    refs: [
        { ref:'sidebar', selector:'multi-edit-sidebar' },
    ],

    /**
     * Ref to the main window, set up on init, when we are sure the main window exists.
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'multi-edit-main-grid': {
                triggerSplitView: me.onTriggerSplitView,
                productchange: me.onProductChange
            }
        });

        Shopware.app.Application.addEvents(
            'moduleConnector:splitView',
            'moduleConnector:splitViewClose'
        );

        Shopware.app.Application.on('moduleConnector:splitViewClose', me.onCloseSplitView, me);

        me.mainWindow = me.getController('Main').mainWindow;

        me.callParent(arguments);
    },

    /**
     * Called when the split view should be triggered.
     * Will minimize the sidebar, resize the list window and
     * open the article detail window
     *
     * @param btn
     * @param record
     * @returns boolean
     */
    onTriggerSplitView: function(btn, record) {
        var me = this,
            mainWindow = me.getController('Main').mainWindow,
            tmpPosition = mainWindow.getPosition(),
            position = {
                x: tmpPosition[0],
                y: tmpPosition[1] - 40
            };

        if(!record) {
            return;
        }

        // Is a split view already been up and running...
        if(me.splitViewMode) {
            Ext.MessageBox.alert('{s name=splitview_title}Split-View{/s}', '{s name=split_view_already_active}The split view mode has already been activated. Please close the product mask window and start a new instance of the split view.{/s}');
            return false;
        }

        // Add inidicator to the class that the split view mode is up and running...
        if(!me.hasOwnProperty('splitViewMode') || !me.splitViewMode) {
            me.splitViewMode = true;
        }

        Shopware.Notification.createGrowlMessage('{s name=splitview_title}Split-View{/s}', '{s name=splitview_text}The split view mode has been activated.{/s}');

        // Save the position and the size of the product list
        me.defaultState = Ext.Object.merge(me.defaultState, mainWindow.getSize());
        me.defaultState = Ext.Object.merge(me.defaultState, position);

        // Prepare the article list
        me.getSidebar().collapse();
        mainWindow.setPosition(0,0);
        mainWindow.setSize(Ext.Element.getViewportWidth() / 2, Ext.Element.getViewportHeight() - 90);

        // Open the product module and set it up for the splitview mode
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                splitViewMode: true,
                articleId: record.get('Article_id')
            }
        });
    },

    /**
     * Called when the selected product changed. Will trigger an even in order
     * to show another article in the article detail window
     *
     * @param selection
     * @returns boolean
     */
    onProductChange: function(selection) {
        var me = this,
            record = selection[0];

        // No record was selected...
        if(!record) {
            return false;
        }

        Shopware.app.Application.fireEvent('moduleConnector:splitView', me, {
            articleId: record.get('Article_id')
        });
    },

    /**
     * Called when the article window was closed. Will restore the old
     * article list dimension / position
     */
    onCloseSplitView: function() {
        var me = this,
            mainWindow = me.mainWindow;

        if (!Ext.isEmpty(mainWindow)) {
            mainWindow.setSize(me.defaultState);
            mainWindow.setPosition(me.defaultState.x, me.defaultState.y);
        }
        me.splitViewMode = false;
    }

});
//{/block}
