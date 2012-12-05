Ext.define('Shopware.apps.Jira.model.edit.Detail', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id',           type: 'int' },
        { name: 'key',          type: 'string' },
        { name: 'name',         type: 'string' },
        { name: 'description',  type: 'string' },
        { name: 'type',         type: 'string' },
        { name: 'priority',     type: 'string' },
        { name: 'status',       type: 'string' },
        { name: 'reporter',     type: 'string' },
        { name: 'assignee',     type: 'string' },
        { name: 'createdAt',   type: 'string' },
        { name: 'modifiedAt',  type: 'string' },
        { name: 'versions',  type: 'string' }

    ],

    idProperty : 'id',

    proxy : {
            type : 'ajax',
            api : {
                read    : '{url controller="jira" action="getIssueDetails"}'
            },
            // Data will be delivered as json and sits in the field data
            reader : {
                type : 'json',
                root : 'data'
            }
        }
});