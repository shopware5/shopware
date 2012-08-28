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
 * @package    Countries
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/countries/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/countries/controller/main"}
Ext.define('Shopware.apps.Countries.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
	extend: 'Ext.app.Controller',


    refs:[
            { ref:'mainWindow', selector:'site-mainWindow' },
            { ref:'countryStates', selector:'country-states' },
            { ref:'countryProperties', selector:'country-properties' },
            { ref:'navigationTree', selector:'country-tree' },
            { ref:'deleteAreaButton', selector:'country-tree [action=onDeleteArea]' },
            { ref:'deleteCountryButton', selector:'country-tree [action=onDeleteCountry]' }
        ],

	/**
	 * Creates the necessary event listener for this
	 * specific controller and opens a new Ext.window.Window
	 * to display the subapplication
     *
     * @return void
	 */
	init: function() {

		var me = this;
        me.control({
            'country-tree': {
                itemclick: me.onItemClick,
                itemdblclick: me.onItemDblClick
            },
            '[action=addState]': {
                click: me.onAddState
            },
            '[action=onDeleteCountry]': {
                click: me.onDeleteCountry
            },
            '[action=onAddCountry]': {
               click: me.onAddCountry
            },
            '[action=onCreateArea]': {
               click: me.onAddArea
            },
            '[action=onDeleteArea]': {
               click: me.onDeleteArea
            },
            'country-states': {
                deleteState: me.onDeleteState
            },
            'country-properties': {
                saveCountry: me.onSaveCountry
            }
    });

        me.mainWindow = me.getView('main.Window').create({
            countryStore: me.subApplication.getStore('Countries'),
            stateStore: me.subApplication.getStore('States')
        });

        me.appContent = me.mainWindow.appContent;
	},

    onItemDblClick: function(tree,record){
        var me = this,
            store = me.getNavigationTree().getStore();
        Ext.MessageBox.prompt('{s name=country/areaNameEditWindow/title}Name{/s}', '{s name=country/updateAreaEnterName}Change name:{/s}', function(result,value){
            if (result !== "ok") return;
            Ext.Ajax.request({
                url: '{url controller="Countries" action="updateArea"}',
                params: {
                    id: record.data.id,
                    name: value
                },
                callback: function(data, operation, response){
                    if(Ext.JSON.decode(response.responseText).success){
                        store.load();
                        Shopware.Msg.createGrowlMessage('','{s name="country/createAreaSuccessful"}Area has been created.{/s}', '{s name=window_title}{/s}')
                    }else{
                        Shopware.Notification.createGrowlMessage('{s name=country/createAreaError}An error has occurred.{/s}', Ext.JSON.decode(response.responseText).errorMsg, '{s name=window_title}{/s}');
                    }
                },
                scope:this
            });
        }, this,false,record.data.text);
    },

    onDeleteArea: function(){
        var me = this,
            tree = me.getNavigationTree(),
            store = tree.getStore(),
            selection = tree.getSelectionModel().getSelection(),
            areaModel = Ext.create('Shopware.apps.Countries.model.Areas');
        areaModel.set('id',selection[0].get('id'));
        Ext.MessageBox.confirm('{s name=country_areas/titleDeleteAreas}Delete area{/s}', '{s name=country_areas/messageDeleteAreas}Are you sure you want to delete the area?{/s}', function (response){
            if (response !== 'yes')  return false;
            areaModel.destroy({
                callback: function(){
                    store.load();
                    Shopware.Msg.createGrowlMessage('','{s name="country/deleteAreaSuccessful"}Area has been deleted.{/s}', '{s name=window_title}{/s}')
                }
            });
        });

    },

    onAddArea: function(){
        var me = this,
            store = me.getNavigationTree().getStore();

        Ext.MessageBox.prompt('{s name=country/areaNameEditWindow/title}Name{/s}', '{s name=country/createAreaEnterName}Enter a name for the new area:{/s}', function(result,value){
            if (result !== "ok") return;
            Ext.Ajax.request({
                url: '{url controller="Countries" action="updateArea"}',
                params: {
                    name: value,
                    active: 0
                },
                callback: function(data, operation, response){
                    if(Ext.JSON.decode(response.responseText).success){
                        store.load();
                        Shopware.Msg.createGrowlMessage('','{s name="country/createAreaSuccessful"}Area has been created.{/s}', '{s name=window_title}{/s}')
                    }else{
                        Shopware.Notification.createGrowlMessage('{s name=country/createAreaError}An error has occurred.{/s}', Ext.JSON.decode(response.responseText).errorMsg, '{s name=window_title}{/s}');
                    }
                },
                scope:this
            }, this, false);
        });
    },
    onAddCountry: function(){
        var me = this;
        me.getCountryProperties().enable();

        me.getCountryProperties().getForm().reset();
    },
    onSaveCountry: function(record, formPnl) {
        if (!formPnl.getForm().isValid()){
            return;
        }
        var me = this;

        var values = formPnl.getForm().getValues();

        var record = Ext.create('Shopware.apps.Countries.model.Properties', values);


        formPnl.getForm().updateRecord(record);


        formPnl.setLoading(true);

        record.save({
            callback: function(record) {
                me.getStore('States').getProxy().extraParams = {
                   countryId: record.data.id
                };
                me.getCountryStates().enable();
                me.getStore('States').load();
                formPnl.setLoading(false);
                me.getStore('Countries').load();
                Shopware.Msg.createGrowlMessage('','{s name="country/editSuccessful"}Country ' +  formPnl.getForm().getValues().name + ' was updated{/s}', '{s name=window_title}{/s}')

            }
        });

    },
    onDeleteState: function(view,rowIndex){
        var me = this,
        stateStore = me.getStore('States'),
        message,
        record = stateStore.getAt(rowIndex);

        message = Ext.String.format('{s name=country_states/messageDeleteState}Are you sure you want to delete the state [0]?{/s}', record.data.name);
        Ext.MessageBox.confirm('{s name=country_states/titleDeleteState}Delete state{/s}', message, function (response){
            if (response !== 'yes')  return false;
            record.destroy({
                success : function () {
                    stateStore.load();
                    Shopware.Msg.createGrowlMessage('','{s name=country_states/deletedSuccesfully}State has been deleted.{/s}', '{s name=window_title}{/s}')
                },
                failure : function () {
                    Shopware.Msg.createGrowlMessage('', '{s name=country_states/deletedError}An error has occurred while deleting state.{/s}', '{s name=window_title}{/s}');
                }
            });
        });
    },
    onDeleteCountry: function(){
        var me = this;
        var tree = me.getNavigationTree();
        var record = tree.getSelectionModel().getSelection()[0];

        var message = Ext.String.format('{s name=countries/messageDeleteCountry}Are you sure you want to delete the country [0]?{/s}', record.data.text);
        Ext.MessageBox.confirm('{s name=countries/titleDeleteCountry}Delete country{/s}', message, function (response){
            if (response !== 'yes')  return false;
            record.destroy({
                success : function () {
                    Shopware.Msg.createGrowlMessage('','{s name=countries/deletedSuccessfully}Country has been deleted.{/s}', '{s name=window_title}{/s}')
                },
                failure : function () {
                    Shopware.Msg.createGrowlMessage('', '{s name=countries/deletedError}An error has occurred while deleting country.{/s}', '{s name=window_title}{/s}');
                }
            });
        });
    },
    onAddState: function(){
        var me = this;
        var grid = me.getCountryStates();

        grid.rowEditing.cancelEdit();
        var states = me.getStore('States');
        var newState = me.getModel('States').create(
            {
                name: '{s name=country/addStateEmptyText/Name}Enter name...{/s}'
            }
        );
        states.insert(0,newState);
        grid.rowEditing.startEdit(0, 0);
    },
    onItemClick: function(item,record) {
        var me = this;
        me.getDeleteAreaButton().disable();
        me.getDeleteCountryButton().disable();

        if (record.data.id.match(/area/)){
            if(!record.data.hasCountriesAssigned)
                me.getDeleteAreaButton().enable();
            me.getDeleteCountryButton().disable();
            return false;
        }else {
            me.getDeleteAreaButton().disable();
            me.getDeleteCountryButton().enable();
        }

        // Proceed with country selection

        var countryId = record.data.databaseId;

        // Load & enable country properties form
        me.getStore('Properties').getProxy().extraParams = {
            id: countryId
        };

        me.getStore('Properties').load({
            callback:function (records) {
               me.getCountryProperties().enable();
               me.getCountryProperties().loadRecord(records[0]);
            }
        });

       // Load and enable states grid
        me.getStore('States').getProxy().extraParams = {
            countryId: countryId
        };
        me.getCountryStates().enable();
        me.getStore('States').load();


    }
});
//{/block}