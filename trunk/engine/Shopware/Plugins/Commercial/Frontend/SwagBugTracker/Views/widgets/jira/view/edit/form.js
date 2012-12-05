Ext.define('Shopware.apps.Jira.view.edit.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.jira-view-edit-form',
    autoScroll: true,
    region: 'center',
    initComponent: function() {
        var me = this;

        //Creates an assigns the items of the form
        me.items = me.createFormItems();
        //Adds the event which will be fired
        //when the user saves the new comment
        me.addEvents('saveComment');

        me.callParent( arguments );
    },

    createFormItems: function() {
        var me = this;

        me.basicFormField          = me.createBasicFormField();
        // If ticket is resolved hide comment function
        if (me._record.get('status')=="Resolved"){
            me.commentCreateFormField  = me.createCommentDisabled();
        }else {
            me.commentCreateFormField  = me.createCommentCreateFormField();
        }



        me.commentFormField        = me.createCommentFormField();
        me.commitsField = me.createCommitFormField();

        return [ me.basicFormField,me.commitsField, me.commentFormField , me.commentCreateFormField ];
    },

    createBasicFormField: function() {
        var me = this;

        return fieldSet = Ext.create('Ext.form.FieldSet', {
            margin: 20,
            title: 'Issue ' + me._record.get('key'),
            layout: {
                type: 'column'
            },
            items: [
                {
                    xtype: 'container',
                    defaults: {
                        labelWidth: 140
                    },
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Issue-Typ',
                            value: me._record.get('type')
                        },
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Autor',
                            value: me._record.get('reporter')
                        },
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Erstellt am',
                            value: Ext.util.Format.date(me._record.get('createdAt'), 'd.m.Y - H:i')
                        },
                         {
                            xtype: 'displayfield',
                            fieldLabel: 'Zugeordnete Versionen',
                            value: me._record.get('versions')
                        }
                    ]
                },
                {
                    xtype: 'container',
                    defaults: {
                        labelWidth: 140
                    },
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Status',
                            value: me._record.get('status')
                        },
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Direkt-Link',
                            value: '<a href="http://jira.shopware.de/Widgets/Jira/?ticket='+me._record.get('key')+'" target="_blank">http://jira.shopware.de/Widgets/Jira/?ticket='+me._record.get('key')+'</a>'
                        },
                        {
                            xtype: 'displayfield',
                            fieldLabel: 'Letzte Anpassung',
                            value: Ext.util.Format.date(me._record.get('modifiedAt'), 'd.m.Y - H:i')
                        }
                    ]
                },
                {
                    xtype: 'displayfield',
                    columnWidth: 1,
                    margin: '15 0 0 0',
                    labelWidth: 140,
                    fieldLabel: 'Beschreibung',
                    value:  me._record.get('description')
                }
            ]
        });
    },

    createCommentCreateFormField: function() {
        var me = this;

        me.fieldName = Ext.create('Ext.form.field.Text', {
	        name: 'name',
	        allowBlank: false,
	        fieldLabel: 'Ihr Name',
	        anchor: '100%'
        });

        me.fieldComment = Ext.create('Ext.form.field.TextArea', {
            name: 'comment',
            allowBlank: false,
            height: 150,
            style: 'background-color: white;',
            fieldLabel: 'Ihr Kommentar',
            anchor: '100%'
        });

        var fieldSet = Ext.create('Ext.form.FieldSet', {
            title: 'Kommentar hinzuf&uuml;gen',
            margin: 20,
            height: 360,
            defaults: {
                labelWidth: 140
            },
            items: [
            	me.fieldName
        	,
        		me.fieldComment
        	,{
                xtype: 'button',
                style: 'float:right; margin-right:2px;',
                text: 'Kommentar speichern',
                action: 'save',
                cls:'primary',
                handler: function() {
                    var name = me.fieldName;
                    var comment = me.fieldComment;
                    if(!name.isValid() || comment.getValue() == '') {
                        Ext.Msg.show({
                            title: 'Hinweis',
                            msg: 'F&uuml;llen Sie bitte die Felder "Ihr Name" und "Ihr Kommentar" aus!',
                            icon: Ext.Msg.ERROR,
                            buttons: Ext.Msg.OK
                        });
                        return;
                    }

                    var values = { name: name.getValue(), comment: comment.getValue() };
                    me.fireEvent('saveComment', me, values, me._record);
                },
                scope: me
            },{
                xtype: 'tbseparator'
            }]
        });

        return fieldSet;
    },
    createCommentDisabled: function(){
        var me = this;

           var fieldSet = Ext.create('Ext.form.FieldSet', {
               margin: 20,
               title: 'Kommentar hinzufügen',
               defaults: {
                   labelWidth: 140
               },
               html:'<span style="font-weight:bold">Diesem Ticket können keine neuen Kommentare hinzugefügt werden, da es bereits geschlossen wurde.</span> <br><br>'+
               'Dafür gibt es folgende Ursachen:<br><br>'+
               '1.) Das im Ticket beschriebene Problem wurde gelöst (Dann befinden sich in der Regel entsprechende Github-Commits im Ticket)<br><br>'+
               '2.) Das im Ticket beschriebene Problem konnte nicht reproduziert werden (Dann enthält das Ticket in den meisten Fällen einen entsprechenden Hinweis von uns)<br><br>'+
               '3.) Das beschriebene Problem wird bereits in einem Ticket behandelt<br><br>'+
               'Wenn Sie weitere Anmerkungen hierzu haben, legen Sie bitte ein neues Ticket mit Verweis auf die ID dieses Tickets '+ me._record.get('key')+ ' an!'

           });


           return fieldSet;
    },
    createCommentFormField: function() {
        var me = this;

        var fieldSet = Ext.create('Ext.form.FieldSet', {
            margin: 20,
            title: 'Kommentare',
            defaults: {
                labelWidth: 140
            },
            items: [
                { xtype: 'jira-view-edit-comments', _record: me._record }
            ]
        });

        return fieldSet;
    },
    createCommitFormField: function() {
        var me = this;

        var fieldSet = Ext.create('Ext.form.FieldSet', {
            margin: 20,
            title: 'Commits zu diesem Ticket',
            defaults: {
                labelWidth: 140
            },
            items: [
                { xtype: 'jira-view-edit-commits', _record: me._record }
            ]
        });


        return fieldSet;
    }
});