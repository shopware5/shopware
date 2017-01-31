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

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote edit main window.
 */
//{block name="backend/vote/view/vote/window"}
Ext.define('Shopware.apps.Vote.view.vote.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'vote-vote-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.vote-vote-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,

    autoScroll : true,
    /**
     * Set fit layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:900,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-vote-edit-window',
    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        //add the order list grid panel and set the store
        me.items = [ me.createTabPanel() ];
        me.title = '{s name=form/title}Evaluation{/s}',
        me.callParent(arguments);
    },

    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            name: 'main-tab',
            items: [
                Ext.create('Shopware.apps.Vote.view.vote.Detail', {
                    record: me.record
                }),
                Ext.create('Shopware.apps.Vote.view.vote.Edit', {
                    record: me.record
                })
            ]
        });
    }
});
//{/block}
