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
 * @package    Vote
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote controller
 *
 * This controller handles all actions.
 * It accepts new votes,
 * deletes votes and offers the possibility to answer to them.
 *
 */
//{block name="backend/vote/controller/vote"}
Ext.define('Shopware.apps.Vote.controller.Vote', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',
    init: function() {
        var me = this;

        me.control({
            'window textfield[action=searchVotes]':{
                fieldchange: me.onSearch
            },
            'vote-main-list':
            {
                itemclick: me.onGridRowClick,
                deleteColumn: me.onGridButtonDelete,
                commentColumn: me.onGridButtonComment,
                addColumn: me.onGridButtonAccept
            },

            //When the add-button in the infopanel is pressed
            'window panel[name=infopanel] button[action=acceptVote]':
            {
                click: me.onInfoPanelButtonAccept
            },

            //When the delete-button in the infopanel is pressed
            'window panel[name=infopanel] button[action=deleteVote]':
            {
                click: me.onInfoPanelButtonDelete
            },

            //When the delete-button in the toolbar is pressed
            'window button[action=deleteMultipleVotes]':
            {
                click: me.onDeleteMultipleVotes
            },
            //When the add-button in the toolbar is pressed
            'window button[action=acceptMultipleVotes]':
            {
                click: me.onAcceptMultipleVotes
            },

            'vote-main-edit button[action=saveVoteEdit]':
            {
                click: me.onSaveEdit
            }
        });

        me.callParent(arguments);
    },

	/**
	 * This function is called, when the user wants to save his edits
	 * @param btn
	 */
    onSaveEdit: function(btn){
        var win = btn.up('window'),
            form = win.down('form'),
            values = form.getForm().getValues(),
            record = form.getRecord(),
            store = this.subApplication.voteStore,
            data = Ext.Object.merge(record.data, values);

        if(!data['answer_datum']){
            data['answer_datum'] = this.getDateNow();
        }

        var model = Ext.create('Shopware.apps.Vote.model.Vote', data);

        win.close();
        model.save({
			callback: function(data, operation){
				var records = operation.getRecords(),
					record = records[0],
					rawData = record.getProxy().getReader().rawData;
				if(operation.success){
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/saveVote/title/success}Vote saved{/s}', '{s name=growlMessage/saveVote/content/success}The vote was successfully saved{/s}', '{s name=window_title}{/s}');
					store.load();
				}else{
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
				}
            }
        });
    },

    getDateNow: function(){
        var newDate = Ext.Date.format(new Date(), 'Y-m-d H:i:s');
        return newDate;
    },

    onGridButtonComment: function(rowIndex){
        var store = this.subApplication.voteStore,
            record = store.data.items[rowIndex];

        //Create edit-window
        Ext.create('Shopware.apps.Vote.view.vote.Window', { record: record, mainStore: store });
    },

    /**
     * Triggered when a value >=3 is entered in the search-textfield
     * @param field Contains the textfield
     */
    onSearch: function(field){
        var me = this,
            store = me.subApplication.voteStore;

        //If the search-value is empty, reset the filter
        if (field.getValue().length == 0) {
            store.clearFilter();
        } else {
            //This won't reload the store
            store.filters.clear();
            //Loads the store with a special filter
            store.filter('searchValue',field.getValue());
        }
    },

    /**
     * Function to delete a vote
     * Is called, when the user presses on the actioncolumn delete-button
     * @param view Contains the view
     * @param index Contains the row-index
     */
    onGridButtonDelete: function(index){
        var store = this.subApplication.voteStore,
            data = store.data.items[index].data;

        Ext.MessageBox.confirm('{s name=messagebox_singleDelete/title}Delete vote{/s}', '{s name=messagebox_singleDelete/message}Do you really want to delete the vote?{/s}', function (response){
            if (response !== 'yes')
            {
                return false;
            }

            var model = Ext.create('Shopware.apps.Vote.model.Vote', data);
            model.destroy({
				callback: function(data, operation){
					var records = operation.getRecords(),
						record = records[0],
						rawData = record.getProxy().getReader().rawData;
					if(operation.success){
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/singleDelete/title/success}Vote deleted{/s}', '{s name=growlMessage/singleDelete/content/success}The vote was successfully deleted{/s}', '{s name=window_title}{/s}');
						store.load();
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
					}
                }
            });
        });
    },

    /**
     * Function to accept a vote
     * Is called, when the user presses on the actioncolumn add-button
     * @param view Contains the view
     * @param index Contains the rowindex
     */
    onGridButtonAccept: function(index){
        var store = this.subApplication.voteStore,
            data = store.data.items[index].data;

        //Set active to 1, so the article will be accepted
        data['active'] = 1;

        var model = Ext.create('Shopware.apps.Vote.model.Vote', data);
        model.save({
			callback: function(data, operation){
				var records = operation.getRecords(),
					record = records[0],
					rawData = record.getProxy().getReader().rawData;
				if(operation.success){
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/singleAccept/title/success}Vote accepted{/s}', '{s name=growlMessage/singleAccept/content/success}The vote was successfully accepted{/s}', '{s name=window_title}{/s}');
					store.load();
				}else{
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
				}
            }
        });
    },

    /**
     * Function to accept a vote
     * Is called, when the user presses on the infopanel add-button
     * @param btn Contains the clicked button
     */
    onInfoPanelButtonAccept:function(btn){
        var infoPanel = btn.up('panel[name=infopanel]'),
            infoView = infoPanel.down('dataview[name=infoView]'),
            data = infoView.data,
            store = this.subApplication.voteStore, model;

        data['active'] = 1;

        model = Ext.create('Shopware.apps.Vote.model.Vote', data);
        model.save({
			callback: function(data, operation){
				var records = operation.getRecords(),
					record = records[0],
					rawData = record.getProxy().getReader().rawData;
				if(operation.success){
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/singleAccept/title/success}Vote accepted{/s}', '{s name=growlMessage/singleAccept/content/success}The vote was successfully accepted{/s}', '{s name=window_title}{/s}');
					store.load();
				}else{
					Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
				}
            }
        });
    },

    /**
     * Function to delete a vote
     * Is called, when the user presses on the infopanel delete-button
     * @param btn Contains the clicked button
     */
    onInfoPanelButtonDelete: function(btn){
        var infoPanel = btn.up('panel[name=infopanel]'),
            infoView = infoPanel.down('dataview[name=infoView]'),
            data = infoView.data,
            store = this.subApplication.voteStore, model;

        Ext.MessageBox.confirm('{s name=messagebox_singleDelete/title}Delete vote{/s}', '{s name=messagebox_singleDelete/message}Do you really want to delete the vote?{/s}', function (response){
            if (response !== 'yes') {
                return false;
            }

            model = Ext.create('Shopware.apps.Vote.model.Vote', data);
            model.destroy({
				callback: function(data, operation){
					var records = operation.getRecords(),
						record = records[0],
						rawData = record.getProxy().getReader().rawData;
					if(operation.success){
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/singleDelete/title/success}Vote deleted{/s}', '{s name=growlMessage/singleDelete/content/success}The vote was successfully deleted{/s}', '{s name=window_title}{/s}');
						store.load();
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
					}
				}
            });
        });
    },

    /**
     * Function to accept multiple votes
     * Is called, when the user presses on the toolbar add-button
     * Votes, which are already active, are ignored
     * @param btn Contains the clicked button
     */
    onAcceptMultipleVotes: function(btn){
        var win = btn.up('window'),
            grid = win.down('grid'),
            selModel = grid.selModel,
            store  = this.subApplication.voteStore,
            selection = selModel.getSelection();

        Ext.MessageBox.confirm('{s name=messagebox_accept/title}Accept votes{/s}', Ext.String.format('{s name=messagebox_accept/message}You marked [0] votes. Do you really want to accept them?{/s}', selection.length), function (response){
            if (response !== 'yes') {
                return false;
            }
            Ext.each(selection, function(item){
                item.set('active', 1);
			});

			store.sync({
				callback: function(batch, operation) {
					var rawData = batch.proxy.getReader().rawData;
					if (rawData.success) {
						store.load();
						Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/acceptMultipleSuccess}Votes accepted{/s}', "{s name=growlMessage_message/acceptMultipleSuccess}The votes were successfully accepted{/s}", '{s name=window_title}{/s}');
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
					}
				}
			})
        });

    },

    /**
     * Function to delete multiple votes
     * Is called, when the user presses the toolbar delete-button
     * @param btn Contains the clicked button
     */
    onDeleteMultipleVotes: function(btn){
        var win = btn.up('window'),
            grid = win.down('grid'),
            selModel = grid.selModel,
            index = 0,
            selection = selModel.getSelection(),
            store = this.subApplication.voteStore;

        Ext.MessageBox.confirm('{s name=messagebox_multipleDelete/title}Delete votes{/s}', Ext.String.format('{s name=messagebox_mutipleDelete/message}You marked [0] votes. Do you really want to delete them?{/s}', selection.length), function (response){

            if (response !== 'yes') {
                return false;
            }

            Ext.each(selection, function(item){
				store.remove(item);
			});

			store.sync({
				callback: function(batch, operation) {
					var rawData = batch.proxy.getReader().rawData;
					if (rawData.success) {
						store.load();
						Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/deleteMultipleSuccess}Votes deleted{/s}', "{s name=growlMessage_message/deleteMultipleSuccess}The votes were successfully deleted{/s}", '{s name=window_title}{/s}');
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
					}
				}
			})
        });
    },

    /**
     * Function to display information in the infopanel
     * Is called, when the user clicks a grid-row
     * @param view Contains the view
     * @param record Contains the clicked record
     */
    onGridRowClick: function(view, record){
        var win = view.up('window'),
            infoPanel = win.down('panel[name=infopanel]'),
            infoView = infoPanel.down('dataview[name=infoView]'),
            toolBar = infoPanel.dockedItems.items[1],
            buttons = toolBar.items.items,
            addButton = buttons[0],
            deleteButton = buttons[1];

        addButton.setDisabled(record.data['active'] == 1);
        deleteButton.setDisabled(false);

        infoView.update(record.data);
    }
});
//{/block}
