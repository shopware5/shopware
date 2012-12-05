Ext.define('Shopware.apps.Jira.model.edit.Comments', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id',           type: 'int' },
        { name: 'author',       type: 'string' },
        { name: 'description',  type: 'string' },
        { name: 'createdAt',    type: 'string' }
    ],

    idProperty : 'id',
    totalProperty : 'total',

    proxy : {
            type : 'ajax',
            api : {
                read    : '{url controller="jira" action="getIssueComments"}'
            },
            // Data will be delivered as json and sits in the field data
            reader : {
                type : 'json',
                root : 'data'
            }
        }
});