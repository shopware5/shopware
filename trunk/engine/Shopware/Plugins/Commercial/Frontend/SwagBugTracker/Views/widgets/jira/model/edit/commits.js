Ext.define('Shopware.apps.Jira.model.edit.Commits', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id',           type: 'string' },
        { name: 'date',           type: 'string' },
        { name: 'author',       type: 'string' },
        { name: 'message',  type: 'string' },
        { name: 'url',    type: 'string' }
    ],

    idProperty : 'id',
    totalProperty : 'total',

    proxy : {
            type : 'ajax',
            api : {
                read    : '{url controller="jira" action="getIssueCommits"}'
            },
            // Data will be delivered as json and sits in the field data
            reader : {
                type : 'json',
                root : 'data'
            }
        }
});