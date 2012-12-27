/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Log
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/log/main}

/**
 * Shopware Controller - Log list backend module
 *
 * Main controller of the log module.
 * It only creates the main-window.
 * shopware AG (c) 2012. All rights reserved.
 */

//{block name="backend/log/controller/log"}
Ext.define('Shopware.apps.Log.controller.Log', {
    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

	/**
	* Creates the necessary event listener for this
	* specific controller and opens a new Ext.window.Window
	* @return void
	*/
	init: function() {
		var me = this;

		me.control({
			'log-main-list actioncolumn':{
				deleteColumn: me.onDeleteSingleLog
			},
			'log-main-list toolbar combobox': {
				change: me.onSelectFilter
			},

			'log-main-list button[action=deleteMultipleLogs]':{
				click: me.onDeleteMultipleLogs
			}
		});
	},

	/**
	 * This function is called when the user wants to delete more than one log at once.
	 * It handles the deleting of the logs.
	 *
	 * @param btn Contains the button in the toolbar
	 */
	onDeleteMultipleLogs: function(btn){
		var win = btn.up('window'),
			grid = win.down('grid'),
			selModel = grid.selModel,
			store = grid.getStore(),
			selection = selModel.getSelection(),
			message = Ext.String.format('{s name=message/deleteMultipleLogs/content}You have marked [0] logs. Are you sure you want to delete them?{/s}', selection.length);

		//Create a message-box, which has to be confirmed by the user
		Ext.MessageBox.confirm('{s name=message/deleteMultipleLogs/title}Delete logs{/s}', message, function (response){
			//If the user doesn't want to delete the articles
			if (response !== 'yes')
			{
				return false;
			}

			//each selection
			Ext.each(selection, function(item){
				store.remove(item);
			});
			store.sync({
				callback: function(batch, operation) {
					var rawData = batch.proxy.getReader().rawData;
					if(rawData.success){
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/deleteMultipleLogs/success/title}Logs deleted{/s}', "{s name=growlMessage/deleteMultipleLogs/success/content}The logs were successfully deleted{/s}", '{s name=window_title}{/s}');
						grid.getStore().load();
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/deleteMultipleLogs/error/title}An error occurred{/s}');
					}
				}
			})
		});
	},

	/**
	 * This function is called when the user wants to delete a single log.
	 * It handles the deleting of the log.
	 *
	 * @param rowIndex Contains the rowIndex of the selection, that should be deleted
	 */
	onDeleteSingleLog: function(rowIndex){
		var store = this.subApplication.stores.items[0],
			logModel = store.data.items[rowIndex],
			message = Ext.String.format('{s name=message/deleteSingleLog/content}Are you sure you want to delete this log?{/s}');

		Ext.MessageBox.confirm('{s name=message/deleteSingleLog/title}Delete log{/s}', message, function (response){
			//If the user doesn't want to delete the articles
			if (response !== 'yes')
			{
				return false;
			}
			logModel.destroy({
				callback: function(data, operation){
					var records = operation.getRecords(),
						record = records[0],
						rawData = record.getProxy().getReader().rawData;
					if(operation.success){
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/deleteSingleLog/success/title}Log deleted{/s}', "{s name=growlMessage/deleteSingleLog/success/content}The log has been deleted successfully.{/s}", '{s name=window_title}{/s}');
					}else{
						Shopware.Notification.createGrowlMessage('{s name=growlMessage/deleteSingleLog/error/title}An error has occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
					}
					store.load();
				}
			});
		})
	},

	/**
	 * This function is called, when the user selects a filter by using the combobox in the toolbar.
	 * It handles the filtering of the store.
	 *
	 * @param combobox Contains the combobox itself
	 * @param newValue Contains the new selected and active value
	 */
	onSelectFilter: function(combobox, newValue){
		var win = combobox.up('window'),
			grid   = win.down('grid'),
			store  = grid.getStore();

		//When you delete the filter of the combobox this function is called twice
		//1st time it's an empty string, 2nd time it is null
		if(newValue === null) {
			return;
		}
		//If the value is an empty string
		if(newValue.length == 0) {
			store.clearFilter();
		}else{
			//contains the displayText of the selected value in the combobox
			var selectedDisplayText = combobox.store.data.findBy(function(item){
				if(item.internalId == newValue) {
					return true;
				}
			}).data.name;
			//This won't reload the store
			store.filters.clear();
			//Loads the store with a special filter
			store.filter('searchValue',selectedDisplayText);
		}
	}
});
//{/block}