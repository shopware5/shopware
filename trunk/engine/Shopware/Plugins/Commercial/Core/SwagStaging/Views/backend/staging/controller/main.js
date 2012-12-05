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
 * @package    Plugins
 * @subpackage Staging
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/plugins/staging/main}
Ext.define('Shopware.apps.Staging.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',
    /**
     * Is any job is currently in progress
     * @bool
     */
    inProcess: false,
    /**
     * Id of job that is current processed
     */
    currentJob: 0,

    /**
     * Id of profile that is opened
     */
    profileId: 0,
    /**
     * Type of current active profile
     */
    currentProfileType: "master",
    /**
     * Current-Scope - master or slave system
     * @bool
     */
    isMaster: true,
    mainWindow: null,
    refs:[
    	   { ref: 'statusPanel', selector: 'staging-main-status' },
           { ref:'statusDetailPanel', selector:'staging-main-status [ref=jobProperties]' },
           { ref:'statusQueue', selector:'staging-main-status [ref=jobQueue]' },
           { ref:'progressbar', selector:'staging-main-status progressbar' },
           { ref:'jobTree', selector:'staging-main-status treepanel' },
           { ref:'jobStartedBy', selector:'staging-main-status [ref=startedBy]'},
           { ref:'jobStartedOn', selector:'staging-main-status [ref=startedOn]'},
           { ref:'jobFinishedOn', selector:'staging-main-status [ref=finishedOn]'},
           { ref:'jobBackupStatus', selector:'staging-main-status [ref=backupExists]'},
           { ref:'jobButtonStart', selector:'staging-main-status button[ref=startJob]'},
           { ref:'jobButtonReset', selector:'staging-main-status button[ref=resetJob]'},
           { ref:'jobButtonStop', selector:'staging-main-status button[action=stopJob]'},
           { ref:'jobButtonDeleteBackup', selector:'staging-main-status button[action=deleteBackup]'},
           { ref:'jobButtonRestoreBackup', selector:'staging-main-status button[action=restoreBackup]'},
           { ref:'profileTree', selector: 'staging-main-profiles treepanel'},
           { ref:'jobTreePanel', selector: 'staging-main-status treepanel'},
           { ref: 'tableSettings', selector: 'staging-main-table'},
           { ref: 'strategySelect',selector: 'staging-main-table combobox[ref=strategySelect]'},
           { ref: 'mainWindow', selector: 'staging-main-window' }
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

        //{if $system == "master"}

        me.isMaster = true;

        //{else}

        me.isMaster = false;

        // {/if}

        me.control({
           // Save button
           'button[action=addNewJob]':{
               'click' : me.onAddNewJob
           },
           'staging-main-profile': {
               'saveProfile' : me.afterEditProfile
           },
           'staging-main-table': {
               'searchTable' : me.onSearchTable,
               'updateTablesInBatch' : me.onUpdateTableStateInBatch,
               'defineCols' : me.onOpenColAssignment
           },
           'staging-main-profiles button[action=addNewProfile]': {
               'click' : me.onAddProfile
           },
           'staging-main-profiles button[action=editProfile]': {
               'click' : me.onEditProfile
           },
           'staging-main-profiles button[action=deleteProfile]': {
               'click' : me.onDeleteProfile
           },
           'staging-main-profiles treepanel': {
               'itemclick' : me.onSelectProfile
           },
           'staging-main-status treepanel': {
               'itemclick' : me.onJobClick
           },
           'staging-main-newjob': {
               'addNewJob' : me.onAddNewJobConfirmed
           },
           'staging-main-status': {
               'deleteJob' : me.onDeleteJob,
               'startJob' : me.onStartJob,
               'resetJob': me.onResetJob,
               'stopJob': me.onStopJob
           },
           'staging-main-status button[action=deleteBackup]': {
               'click' : me.onDeleteBackup
           },
            'staging-main-status button[action=restoreBackup]': {
               'click' : me.onRestoreBackup
            },
           'button[action=syncTables]': {
               'click' : me.onSyncTables
           },
           'staging-main-profiles-assigncols': {
               'updateColumns' : me.onUpdateColumns
           }
        });
        var profileStore = me.getStore('Profiles');
        profileStore.load();

        var jobStore = me.getStore('Jobs');
        var queueStore = me.getStore('Queue');
        var tableStore = me.getStore('Tables');
        jobStore.load();
        var testStore = me.getStore('Tests');
        testStore.load({
            callback:function (records,operation,status) {
                if(!operation.wasSuccessful()) {
                   var rawData = operation.getProxy().getReader().rawData;
                   Shopware.Notification.createGrowlMessage('Error', rawData.message, 'Error');
                   return;
                }

                var testStatus = testStore.getProxy().reader.jsonData.testStatus,
                    status = true;
                    
                /** Terminate the status based on the returned records */
                Ext.each(records, function(record) {
	                if(record.get('status') === 0) {
		                status = false;
	                }
                });
                testStatus = status;
                
                me.mainWindow = me.getView('main.Window').create({
                   profileStore: profileStore,
                   isMaster: me.isMaster,
                   testStatus: testStatus,
                   jobStore: jobStore,
                   queueStore: queueStore,
                   tableStore: tableStore,
                   testStore: testStore
                });
            },
            scope: this
        });
        me.callParent(arguments);
    },
    /**
     * Reset a selected job
     * Event that will be fired when the corresponding button is clicked
     */
    onResetJob: function(){
        var me = this,
            view = me.getStatusPanel(),
            msg = '{s name=controller/message/selected_job_reset}The selected job was resetted{/s}';

        view.setLoading(true);
        Ext.Ajax.request({
              url: '{url action=resetJob}',
              params: {
                jobId: me.currentJob
              },
              success: function(response) {
                  view.setLoading(false);
                  Shopware.Notification.createGrowlMessage('{s name=controller/message/successful}Successful{/s}', '{s name=controller/message/job_reset}Job has been resetted{/s}', '{s name=window/title}Staging{/s}');
                  view.startJobButton.setDisabled(false);
                  view.resetJobButton.setDisabled(true);
                  view.stopJobButton.setDisabled(true);
                  
                  me.getJobStartedOn().setValue(msg);
                  me.getJobFinishedOn().setValue(msg);
                  
                  me.currentDoneJobs = 0;
                  me.getProgressbar().updateProgress(0, Ext.String.format('Job {literal}{0}/{1}{/literal}', me.currentDoneJobs, me.currentTotalJobs), true);
              },
              failure: function() {
                  view.setLoading(false);
                  Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/job_reset}Error while resetting job{/s}', '{s name=window/title}Staging{/s}');
              }
       });
    },
    /**
     * Delete a backup
     */
    onDeleteBackup: function(){
        var me = this;

        if (!me.currentJob){
            Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/no_selection}No job selected{/s}', '{s name=window/title}Staging{/s}');
        }

        Ext.MessageBox.confirm('{s name=controller/confirm/delete_backup_title}Delete Backup{/s}', '{s name=controller/confirm/delete_backup_message}Confirm deleting backup: The following tables are involved: {/s}'+me.backupTables, function (response){
          if (response !== 'yes') return false;
             Ext.Ajax.request({
                  url: '{url action=deleteBackup}',
                  params: {
                    jobId: me.currentJob
                  },
                  success: function(response) {
                      Shopware.Notification.createGrowlMessage('{s name=controller/message/successful}Successful{/s}', '{s name=controller/message/backup_delete}Backup has been deleted{/s}', '{s name=window/title}Staging{/s}');
                  },
                  failure: function(a,operation) {
                      Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/backup_reset}Error while resetting backup{/s}', '{s name=window/title}Staging{/s}');
                  }
             });
        });

    },
    /**
     * Restore a backup
     */
    onRestoreBackup: function(){
        var me = this;

        if (!me.currentJob){
            Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/no_selection}No job selected{/s}', '{s name=window/title}Staging{/s}');
        }

        Ext.MessageBox.confirm('{s name=controller/confirm/delete_backup_title}Delete Backup{/s}', '{s name=controller/confirm/restore_backup_message}Confirm restore backup: WARNING THE BACKUP MAY BE OUT OF DATE! The following tables are involved: {/s}'+me.backupTables, function (response){
          if (response !== 'yes') return false;
                 Ext.Ajax.request({
                      url: '{url action=restoreBackup}',
                      params: {
                        jobId: me.currentJob
                      },
                      success: function(response) {
                          Shopware.Notification.createGrowlMessage('{s name=controller/message/successful}Successful{/s}', '{s name=controller/message/backup_restore}Backup has been restored{/s}', '{s name=window/title}Staging{/s}');
                      },
                      failure: function(a,operation) {
                          Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/backup_restore}Error while restoring backup{/s}', '{s name=window/title}Staging{/s}');
                      }
                 });
          });
    },
    /**
     * Update a column in profile > table > column assignment
     * @param grid
     * @param window
     */
    onUpdateColumns: function(grid,window){
       var sm = grid.getSelectionModel(),
       selected = sm.selected.items,
       store = grid.getStore();

       var selectedColumns = new Array();

       Ext.each(selected, function(record) {
           selectedColumns.push(record.data);
       });
       if (selectedColumns.length > 0){
           Ext.Ajax.request({
               url: '{url action=updateTableColumns}',
               params: {
                 tableId: window.tableId,
                 selectedColumns: Ext.JSON.encode(selectedColumns)
               },
               success: function(response) {
                   window.close();
               },
               failure: function() {
                   Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/update_column}Error while updating column{/s}', '{s name=window/title}Staging{/s}');

               }
            });
       }

    },
    /**
     * Search / filter for tables in profile view
     * @param store
     * @param value
     */
    onSearchTable: function(store,value){
        store.getProxy().extraParams = { profileId: store.getProxy().extraParams.profileId,filtertable:value};
        store.load();
    },
    /**
     * Open table column assignment window
     * @param store
     * @param recordIdx
     */
    onOpenColAssignment: function(store,recordIdx){
      var me = this;
      var record = store.getAt(recordIdx).data;
      var id = record.id; // Id of profile <> table assignment
      var tableName = record.tableName;
      var strategy = record.strategy;
      var profileId = me.profileId;
      var profileType = me.profileType;

      me.getStore('Cols').getProxy().extraParams = {
        tableId: id,
        tableName: tableName
      };
      me.getStore('Cols').load({
           tableId: id,
           tableName: tableName,
           callback:function (records) {
               me.getView('main.AssignCols').create({
                 tableName: tableName,
                 tableId: id,
                 strategy: strategy,
                 profileId: profileId,
                 profileType: profileType,
                 records: records,
                 store:me.getStore('Cols')
               });
           }
      });


    },
    /**
     * Update selected tables in batch
     * @param grid
     */
    onUpdateTableStateInBatch: function(grid){
        var sm = grid.getSelectionModel(),
            selected = sm.getSelection(),
            store = grid.getStore(),
            selectedStrategy = this.getStrategySelect().getValue();

        var selectedTables = [];
        Ext.each(selected, function(item) {
           selectedTables.push({ id: item.get('id'), tableName: item.get('tableName') });
        });

        grid.setLoading(true);
        Ext.Ajax.request({
	    	url:'{url controller=Staging action=updateTable}',
	    	params: {
		    	tables:  Ext.JSON.encode(selectedTables),
		        selectedStrategy: selectedStrategy
	    	},
	        success: function() {
		        store.load({
			        callback: function() {
				        grid.setLoading(false);
			        }
		        });
	        } 
        });
    },
    
    /**
     * Add new job button
     */
    onAddNewJob: function(){
        var me= this;
        // Load profiles that match to current system (master/staging)
        me.getStore('ProfilesCombo').getProxy().extraParams = { profileFilter: me.isMaster ? "master" : "slave"  };
        me.getStore('ProfilesCombo').load({
          callback:function (records) {
              if (!records[0]){
				  var msg = '{s name=controller/error/no_active_profile}There is no active profile for {/s}' + (me.isMaster ? "{s name=controller/error/master_staging}master > staging replication{/s}" : "{s name=controller/error/staging_master}staging > master replication{/s}");

                  Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', msg, '{s name=window/title}Staging{/s}');
                  return;
              }
              me.getView('main.Newjob').create({
                  isMaster: me.isMaster,
                  profileSelector: me.getStore('ProfilesCombo')
              });
          }
       });

    },
    /**
     * Add new profile
     * @param button
     */
    onAddProfile: function(button){
        var me = this,
            record = Ext.create('Shopware.apps.Staging.model.Profile',{
                jobsPerRequest: 20
            });
            
        me.getView('main.Profile').create({
            record: record,
            isMaster: me.isMaster
        });
    },
    /**
     * Event that will be fired if a profile was edited
     */
    onEditProfile: function(){
        var me = this;
        var model = me.getProfileTree().getSelectionModel().getSelection()[0];
        if (!model) return;
        if (!model.data.id) return;
        // Load profile details via ajax
        me.getStore('Profile').load({
           id: model.data.id,
           callback:function (records) {
              me.getView('main.Profile').create({
                record: records[0],
                isMaster: me.isMaster
              });
           }
        });

    },
    /**
     * Event that will be fired when a profile was deleted
     */
    onDeleteProfile: function(){
        var me = this;
        var model = me.getProfileTree().getSelectionModel().getSelection()[0];

        if(model.data.id){
            Ext.MessageBox.confirm('{s name=controller/confirm/delete_profile_title}Delete Profile{/s}', "{s name=controller/confirm/delete_profile}Confirm deleting profile{/s}", function (response){
              if (response !== 'yes') return false;
                 model.destroy({
                     success : function () {
                         me.getProfileTree().store.load();
                         Shopware.Notification.createGrowlMessage('{s name=controller/message/successful}Successful{/s}', '{s name=controller/message/delete_profile}Profile has been deleted{/s}', '{s name=window/title}Staging{/s}');
                     },
                     failure : function () {
                         Shopware.Notification.createGrowlMessage('Error', 'An error has occurred while deleting the profile', 'Staging');
                     }
                 });
          });
        }
    },
    /**
     * Event that will be fired after edit a profile
     * @param record
     * @param formPnl
     */
    afterEditProfile: function (record,formPnl){
        if (!formPnl.getForm().isValid()){
            return;
        }
        var me = this;
        var values = formPnl.getForm().getValues();

        formPnl.getForm().updateRecord(record);
        formPnl.up('window').setLoading(true);

        record.save({
            callback: function(record) {
                formPnl.up('window').setLoading(false);
                me.getStore('Profiles').load();
                formPnl.up('window').destroy();
                Shopware.Notification.createGrowlMessage(
                    '{s name=controller/message/successful}Successful{/s}',
                    '{s name=controller/message/profile}Profile {/s}' +  formPnl.getForm().getValues().profileText + '{s name=controller/message/was_updated} was updated{/s}',
                    '{s name=window/title}Staging{/s}'
                );
            }
        });
    },
    /**
     * Synchronize tables
     */
    onSyncTables: function(){
        var me = this;
        var profileId = 0;
        var model = me.getProfileTree().getSelectionModel().getSelection()[0];
        profileId = model.data.id;

        Ext.Ajax.request({
         url: '{url action=syncTables}',
         params: {
           profileId: profileId
         },
         success: function(response) {
              // Reload grid
              me.getStore('Tables').load();
         },
         failure: function() {

         }
      });
    },
    /**
     * Event that will be fired as soon as some profile was selected in tree
     * @param view
     * @param record
     */
    onSelectProfile: function(view,record){
        var me = this;
        var tableStore = me.getStore('Tables');

        // Select correct table strategy properties
        if (record.data.profileAssignment == "master"){
          // me.getTableSettings().profileType = 'master';
           me.getTableSettings().setProfileType('master');
        }else {
          // me.getTableSettings().profileType = 'slave';
           me.getTableSettings().setProfileType('slave');
        }

        this.profileId = record.data.id;
        this.currentProfileType = record.data.profileAssignment;

        tableStore.getProxy().extraParams = { profileId: record.data.id};

        tableStore.load(
        {
           scope:this,
           callback:function (records){
               me.getTableSettings().store = tableStore;
               me.getTableSettings().enable();
           }
        });

    },
    /**
     * Fires if a job in tree is selected
     * @param view
     * @param record
     */
    onJobClick: function(view, record){
        var me = this,
            jobId = record.get('id'),
            jobStore = me.getStore('Job'),
            msg = '{s name=controller/message/job_not_executed}Job has not been executed yet{/s}';

        me.currentJob = jobId;
        jobStore.getProxy().extraParams = { id: jobId};
        jobStore.load(
        {
            scope:this,
            callback:function (records,operation,success) {
            	
            	// If there's occurs an error, get the raw data and it's message, so we could display it in a growl message.
                if (!success){
                	var rawData = operation.request.proxy.reader.rawData;
                    Shopware.Notification.createGrowlMessage('{s name=controller/message/error}Error{/s}', '{s name=controller/error/load_job}Could not load job<br/>{/s}' + rawData.message, '{s name=window/title}Staging{/s}');
                    return;
                }
                /**
                 *
                 * { ref:'jobBackupStatus', selector:'staging-main-status [ref=backupExists]'},
                    { ref:'buttonJobStart', selector:'staging-main-status button[action=startJob]'},
                    { ref:'buttonJobStop', selector:'staging-main-status button[action=stopJob]'},
                    { ref:'buttonJobDeleteBackup', selector:'staging-main-status button[action=deleteBackup]'},
                    { ref:'buttonJobRestoreBackup', selector:'staging-main-status button[action=restoreBackup]'},
                 */
                var data = records[0].data;
                view.selectedJob = data;


                me.getJobStartedBy().setValue(data.user);
                if(data.startDate === null) {
	                me.getJobStartedOn().setValue(msg);
                } else {
                	me.getJobStartedOn().setValue(Ext.Date.format(data.startDate,'Y-m-d H:i:s'));
                }
                
                if(data.endDate === null) {
	                me.getJobFinishedOn().setValue(msg);
                } else {
                	me.getJobFinishedOn().setValue(Ext.Date.format(data.endDate,'Y-m-d H:i:s'));
                }

                // Reset buttons / Information

                me.getJobButtonDeleteBackup().disable();
                me.getJobButtonRestoreBackup().disable();
                me.getJobButtonStart().enable();
                me.getJobButtonStop().disable();
                
                if(data.jobsCurrent !== 0) {
	                me.getJobButtonReset().enable();
                }
                me.getJobBackupStatus().setValue('{s name=controller/message/no_backup_available}There is no backup available{/s}');

                // If job was finished, disable start button
                if (data.successful){
                    me.getJobButtonStart().disable();
                    me.getJobButtonStop().disable();
                    me.getJobButtonReset().enable();
                }
                 // If a backup exists, show delete backup & restore backup methods
                me.backupTables = '';
                if (data.backupExistsForThisJob){

                    me.getJobBackupStatus().setValue("{s name=controller/message/backup_available}A backup is available for this job{/s}");
                    var tempArray = new Array();
                    if (data.backupTables){
                        //me.backupTables = data.backupTables.join(',');
                        Ext.Array.each(data.backupTables, function(v){
                            tempArray.push(v.table);
                        });

                        me.backupTables = tempArray.join(',');
                    }

                    me.getJobButtonDeleteBackup().enable();
                    me.getJobButtonRestoreBackup().enable();
                }

                if ((data.profileassignment == "master" && !this.isMaster) || (data.profileassignment=="slave" && this.isMaster)){

                    me.getJobButtonStart().disable();
                    me.getJobButtonStop().disable();
                    me.getJobButtonReset().disable();
                    me.getJobButtonDeleteBackup().disable();
                    me.getJobButtonRestoreBackup().disable();
                    Shopware.Notification.createGrowlMessage('{s name=controller/message/notice}Notice{/s}', '{s name=controller/notice/job_execution_permitted}Job execution permitted!{/s}', '{s name=window/title}Staging{/s}');
                }
                // If a backup exists in any other job, inform the user, that the backup will be overwritten
                if (data.backupExists && !data.backupExistsForThisJob){
                    me.getJobBackupStatus().setValue("{s name=controller/message/warning_backup}Warning: Another job has an active backup. Executing this job will delete all existing backups!{/s}");
                }
                var doneJobs =  data.jobsCurrent;
                var totalJobs = data.jobsTotal;
                var progress = doneJobs / totalJobs;
                progress = Ext.Number.toFixed(progress, 2);
                
                me.currentProgress = progress;
                me.currentDoneJobs = doneJobs;
                me.currentTotalJobs = totalJobs;
                
                //{literal}
                me.getProgressbar().updateProgress(progress, Ext.String.format('Job {0}/{1}',doneJobs,totalJobs), false);
                //{/literal}
                me.getStatusDetailPanel().enable();
                // Load queue
                var queueStore = this.getStore('Queue');
                queueStore.getProxy().extraParams = { id: jobId};
                queueStore.load();
            },
            failure: function(){

            }
        }
        );
    },
    /**
     * Start execution of job
     */
    onStartJob: function(view){

        var me = this;

        Ext.MessageBox.confirm('{s name=controller/confirm/start_job_title}Confirm starting job{/s}', '{s name=controller/confirm/start_job}Do you want to begin replicating?{/s}', function (response){
          if (response !== 'yes') return false;
            var model = Ext.create('Shopware.apps.Staging.model.Status');
            view.startJobButton.setDisabled(true);
            view.stopJobButton.setDisabled(false);
            if (me.currentJob){
                model.set('offset',50);
                model.set('jobId',me.currentJob);
                me.inProcess = true;

                // Disable the tabs and the tree on the left hand
                me.getJobTreePanel().setDisabled(true);
            	var tabs = me.getMainWindow().tabPanel.items;
                tabs.each(function(tab) {
                	if(tab.initialTitle !== 'jobs') {
	                	tab.setDisabled(true); 
                	}
                });
                
                me.executeJob(model,50,me.currentJob);
            }else {
                alert('{s name=controller/alert/no_job}No job selected{/s}');
            }

       });
    },
    
    /**
     * Event listener method which will be triggered when the user clicks
     * on the "Stop Job" button.
     *
     * This method stops the currently active job and enables the start job button
     *
     * @event click
     * @public
     * @param [object] view - Shopware.apps.Staging.view.main.Status
     * @return void
     */
    onStopJob: function(view) {
	    var me = this;
	    me.inProcess = false;
	    me.getJobTreePanel().setDisabled(false);
	    view.stopJobButton.setDisabled(true);
	    view.startJobButton.setDisabled(false);
	    
	    var tabs = me.getMainWindow().tabPanel.items;
        tabs.each(function(tab) {
        	tab.setDisabled(false); 
        });
        view.resetJobButton.setDisabled(false);
        
        me.getJobTreePanel().setDisabled(false);
    },
    
    /**
     * Start batch processing job
     * @param model
     * @param limit
     * @param jobId
     */
    executeJob: function(model, limit, jobId) {
        var me = this;

        model.set('jobId',jobId);
        model.set('limit', limit);

        model.setDirty();
        model.save({
            success: function(record, operation) {
                var doneJobs = model.get('doneQueueJobs');
                var progress = doneJobs / model.get('totalQueueJobs');
                progress = Ext.Number.toFixed(progress, 2);
                
                // Refresh member properties which represents the current working job
                me.currentProgress = progress;
                me.currentDoneJobs = doneJobs;
                me.currentTotalJobs = model.get('totalQueueJobs');
                
                //{literal}
                me.getProgressbar().updateProgress(progress, Ext.String.format('Job {0}/{1}',doneJobs,model.get('totalQueueJobs')), true);
                //{/literal}
                //if the last variant was created we can hide the window and reload the listing
                if (model.get('done')) {
                    Shopware.Notification.createGrowlMessage('{s name=controller/message/successful}Successful{/s}', '{s name=controller/message/job_execution_successful}Job was executed successfully{/s}', '{s name=window/title}Staging{/s}');
                    me.inProcess = false;
                    // Reload queue grid
                    var queueStore = me.getStore('Queue');
                    queueStore.getProxy().extraParams = { id: jobId};
                    queueStore.load();
                    
                    // Handle the buttons
                    var view = me.getStatusPanel();
                    view.startJobButton.setDisabled(false);
                    view.stopJobButton.setDisabled(true);
                    view.resetJobButton.setDisabled(false);
                    me.getJobTreePanel().setDisabled(false);
                    // Enable the tab and the tree
                    var tabs = me.getMainWindow().tabPanel.items;
                    tabs.each(function(tab) {
	                	tab.setDisabled(false); 
                    });
                } else if(me.inProcess === false) {
	                Shopware.Notification.createGrowlMessage('{s name=controller/message/aborted}Aborted{/s}', '{s name=controller/message/job_aborted}Job was aborted successfully.{/s}', '{s name=window/title}Staging{/s}');
	                
	                // Reload queue grid
                    var queueStore = me.getStore('Queue');
                    queueStore.getProxy().extraParams = { id: jobId};
                    queueStore.load();
                    
                } else {	
                    //otherwise we have to call this function recursive with the next offset
                    me.executeJob(model, limit, jobId);
                }
            },
            failure: function(record, operation) {


                var rawData = operation.request.proxy.reader.rawData,
                    message = '<br>' + rawData.message;
                Shopware.Notification.createGrowlMessage('Error', message, 'Error');
                me.inProcess = false;
                var tabs = me.getMainWindow().tabPanel.items;
               tabs.each(function(tab) {
                tab.setDisabled(false);
               });
                me.getJobTreePanel().setDisabled(false);
                // Reload queue grid
                var queueStore = me.getStore('Queue');
                queueStore.getProxy().extraParams = { id: jobId};
                queueStore.load();

            }
        });

    },
    /**
     * Add new replication job
     * @param profile Ext.record with selected profile to create job for
     */
    onAddNewJobConfirmed: function(profile,win){
        var profile = profile.data;
        var me= this;
        
        win.setLoading(true);
        Ext.Ajax.request({
          url: '{url action=createJob}',
          params: {
            profileId: profile.id,
            profileType: profile.profileAssignment,
            profileText: profile.profileText
          },
          success: function(response) {
              var json = Ext.decode(response.responseText);
              me.getJobTree().store.load();
              win.setLoading(false);
              win.destroy();
          },
          failure: function(response) {
              win.setLoading(false);
              Shopware.Notification.createGrowlMessage('Error', response, 'Error');
          }
       });
    },
    /**
     * Delete job
     */
    onDeleteJob: function(){
        var me = this;
        var model = me.getJobTree().getSelectionModel().getSelection()[0];

        if(model.data.id){
            Ext.MessageBox.confirm('Delete Job', "Confirm deleting job", function (response){
              if (response !== 'yes') return false;
                 model.destroy({
                     success : function () {
                         me.getJobTree().store.load();
                         Shopware.Notification.createGrowlMessage('Successful', 'Job has been deleted', 'User Manager');
                     },
                     failure : function () {
                         Shopware.Notification.createGrowlMessage('Error', 'An error has occurred while deleting the job', 'User Manager');
                     }
                 });
          });

        }
    }
});