{include file="widgets/jira/controller/overview.js"}
{include file="widgets/jira/model/edit/comments.js"}
{include file="widgets/jira/model/edit/commits.js"}
{include file="widgets/jira/model/edit/detail.js"}
{include file="widgets/jira/model/overview/list.js"}
{include file="widgets/jira/model/overview/version.js"}
{include file="widgets/jira/store/edit/comments.js"}
{include file="widgets/jira/store/edit/detail.js"}
{include file="widgets/jira/store/edit/commits.js"}
{include file="widgets/jira/store/overview/list.js"}
{include file="widgets/jira/store/overview/version.js"}
{include file="widgets/jira/view/overview/list.js"}
{include file="widgets/jira/view/overview/version.js"}
{include file="widgets/jira/view/create/form.js"}
{include file="widgets/jira/view/edit/comments.js"}
{include file="widgets/jira/view/edit/commits.js"}

{include file="widgets/jira/view/main/panel.js"}

Ext.define('Shopware.apps.Jira.controller.Main', {
    extend: 'Enlight.app.Controller',
    requires: ['Shopware.apps.Jira.controller.Overview'],
    store: ['overview.List', 'edit.Comments','edit.Commits','overview.Version'],
    models: ['overview.List', 'edit.Comments','edit.Commits','overview.Version'],
    views: ['main.Panel', 'create.Form', 'edit.Form', 'edit.Comments','edit.Commits'],
    versions: [
        [0, 'Alle anzeigen'],
        [10207, 'Community Feedback'],
        [10403, '4.0.4'],
        [10412, '4.0.5']
    ],
    init: function() {
        var me = this;

        try {
            var ticket = Ext.get('ticket').getHTML();
        } catch(err){
            var ticket = "";
        }

        try {
            var version = Ext.get('version').getHTML();
        } catch(err){
            var version = "";
        }


        me.control({
            'jira-view-overview-list': {
                searchChanged: me.onSearchChanged,
                versionChanged: me.onVersionChanged,
                editIssue: me.onEditIssue
            },
            'jira-view-overview-version': {
                editIssue: me.onEditIssue,
                searchChanged: me.onSearchChanged
            },
            'jira-view-edit-form': {
                saveComment: me.onSaveComment
            },
            'jira-view-create-form': {
                createIssue: me.onCreateIssue
            }
        });

        //Initializes the main panel which holds all components
        me.createForm = me.getView('create.Form').create();
        me.overviewList = me.getView('overview.List').create({
            store: me.getStore('overview.List'),
            versions: me.versions
        });

        me.mainTab = me.getView('main.Panel').create({
            /*{if $viewport == true}*/
            region: 'center',
            layout: 'fit',
            /*{else}*/
            renderTo: 'ext-view',
            /*{/if}*/
            items: [me.createForm, me.overviewList]
           // items: [me.createForm]
        });

        if (ticket){
            me.onOpenIssueAtStartup(ticket);
        }

        if (version){
            me.onOpenVersionAtStartup(version);
        }


    },
    searchVersion: function (a, version) {
        var i = a.length;
        while (i--) {
           if (a[i][0] == version) {
               return a[i][1];
           }
        }
        return false;
    },
    onOpenVersionAtStartup: function (version){
        var me = this;

        me.versionsStartupStore =  Ext.create('Shopware.apps.Jira.store.overview.Version');
            var me = this;
            me.versionsStartupStore.getProxy().extraParams = { version: version };
            me.versionsStartupStore.load({
                  callback : function(r, options, success) {
                      if (success){
                          //console.log(me.versions);
                          version = me.searchVersion(me.versions,version);

                          editForm = me.getView('overview.Version').create({
                             title:version,
                             closable: true,
                             store: me.versionsStartupStore
                         });

                          me.mainTab.add(editForm);
                          me.mainTab.setActiveTab(editForm);
                      }
                  }
      });
    },
    /**
     * Fires when the value of the search field was changed
     *
     * @param textfield Ext.form.field.Textfield
     * @param value The new value of the serach field
     */
    onSearchChanged: function(textfield, value, storeScope) {
        var me = this,
            searchString = Ext.String.trim(value);

        if(storeScope == 'version') {
            var store = me.versionsStartupStore;
        } else {
            var store = me.overviewList.store;
        }

        store.currentPage = 1;
        store.getProxy().extraParams = { search: searchString };
        store.load();
    },

    /**
     * Filter ticket result after version
     * @param textfield
     * @param value
     */
    onVersionChanged: function(textfield, value) {
           var me = this;


           store = me.overviewList.store;

           store.currentPage = 1;
           store.getProxy().extraParams = { version: value };
           store.load();
    },

    onOpenIssueAtStartup: function(ticket) {
        var store =  Ext.create('Shopware.apps.Jira.store.edit.Detail');
        var me = this;
        store.getProxy().extraParams = { issueKey: ticket };
        store.load({
            callback : function(r, options, success) {
                if (success){
                    r = r[0];

                    editForm = me.getView('edit.Form').create({
                       title: r.data.key,
                       closable: true,
                       _record:r
                   });

                    me.mainTab.add(editForm);
                    me.mainTab.setActiveTab(editForm);
                }
            }
        });

        return;
            var me = this,
                store = me.overviewList.store,
                record = store.getAt(rowIndex),
                editForm = null;

            editForm = me.getView('edit.Form').create({
                title: record.get('key'),
                closable: true,
                _record: record
            });

            me.mainTab.add(editForm);
            me.mainTab.setActiveTab(editForm);
    },
    /**
     * Fires when the user has clicked on the edit button
     * in the issue list
     *
     * @param view Ext.grid.View
     * @param rowIndex int
     * @param colIndex int
     * @param item Ext.grid.column.Action
     */
    onEditIssue: function(view, rowIndex, colIndex, item, storeScope) {
        var me = this;

        if(storeScope == 'version') {
            var store = me.versionsStartupStore;
        } else {
            var store = me.overviewList.store;
        }

        var record = store.getAt(rowIndex),
            editForm = null;

        editForm = me.getView('edit.Form').create({
            title: record.get('key'),
            closable: true,
            _record: record
        });

        me.mainTab.add(editForm);
        me.mainTab.setActiveTab(editForm);
    },

    /**
     * Fires when the user saves a new comment for
     * an existed issue
     *
     * @param form Shopware.apps.Jira.view.edit.Form
     *  The issue detail form component
     * @param values Json-Array
     *  The name of the author and the new comment
     * @param record Shopware.apps.Jira.model.overview.List
     *  The record of the current issue
     */
    onSaveComment: function(form, values, record) {
        //Shows the loading mask for the form
        form.setLoading(true);

        //Sends an ajax request to save the comment
        Ext.Ajax.request({
            url: '{url controller="jira" action="addComment"}',
            params: {
                issueId: record.get('id'),
                issueKey: record.get('key'),
                name: values.name,
                comment: values.comment
            },
            success: function() {
                form.fieldName.reset();
                form.fieldComment.reset();
                form.setLoading(false);
                form.down('jira-view-edit-comments').store.load();
                Ext.Msg.show({
                    title: 'Hinweis',
                    msg: 'Ihr Kommentar wurde erfolgreich &uuml;bertragen.',
                    icon: Ext.Msg.INFO,
                    buttons: Ext.Msg.OK
                });
            }
        });
    },

    /**
     *
     * @param form Shopware.apps.Jira.view.create.Form
     *  Holds the issue create form instance
     * @param values Object
     *  Holds the values of the new issue
     */
    onCreateIssue: function(form, values)
    {
        var me = this;

        //Shows the loading mask for the form
        form.setLoading(true);

        //Sends an ajax request to save the comment
        Ext.Ajax.request({
            url: '{url controller="jira" action="addIssue"}',
            params: {
                type: values.type,
                name: values.name,
                email: values.email,
                author: values.author,
                description: values.description
            },
            success: function(result) {
                var json = Ext.JSON.decode(result.responseText);


                form.getForm().reset();
                form.setLoading(false);

                //Starts a reload of the issues store
                me.overviewList.store.load();

                Ext.Msg.show({
                    title: 'Hinweis',
                    msg: 'Ihr Ticket wurde erfolgreich angelegt und hat die Nummer '+json.issueKey+ '. Sie finden den aktuellen Status des Tickets, Kommentare und weitere Infos in der Ticket-Übersicht.',

                    icon: Ext.Msg.INFO,
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        //Activates the overview tab

                        me.mainTab.setActiveTab(me.overviewList);
                    }
                });
            },
            failure: function() {
                form.setLoading(false);
                Ext.Msg.show({
                    title: 'Hinweis',
                    msg: 'Ihr Ticket konnte nicht &uuml;bertragen werden.',
                    icon: Ext.Msg.INFO,
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        //Activates the overview tab
                        me.mainTab.setActiveTab(me.overviewList);
                    }
                });
            }
        });
    }
});