//{block name="extjs/overrides/base"}
Ext.override(Ext.form.Basic, {
    submit: function (options) {
        options = options || {};
        var me = this,
            action;

        if (options.standardSubmit || me.standardSubmit) {
            action = 'standardsubmit';
        } else {
            action = me.api ? 'directsubmit' : 'submit';
        }

        options.params = options.params || {};
        options.params.__csrf_token = Ext.CSRFService.getToken();

        return me.doAction(action, options);
    },

    loadRecord: function(record) {
        var me = this;

        if(record && record.associations && record.associations.length) {
            var data = record.getAssociatedData(),
                values = Ext.clone(record.data);

            Ext.each(record.associations.items, function(item) {
                if(!Ext.isObject(item)) {
                    return;
                }
                if (data[item.name] !== Ext.undefined) {
                    var model = Ext.create(item.associatedName, data[item.name][0]);
                    Ext.each(model.fields.keys, function(key) {
                        values[item.associationKey + '[' + key + ']'] = model.data[key];
                    });
                }
            });

            me.setValues(values);
        }

        if(record) {
            me.callOverridden(arguments);
        } else {
            me._record = undefined;
            me.reset();
        }

        me.fireEvent('recordchange', me, record);
    },

    /**
     * The update record override allows to handle Ext.data.Model associations within a form panel.
     * @param record
     * @return
     */
    updateRecord: function(record) {
        record = record || this._record;

        var values = this.getValues(),
            fields = record.fields,
            data = {}, associationModel, associationUpdated;

        //iterate all record associations to update the model fields with the form data
        record.associations.each(function(association) {
            var associationStore = record[association.storeName];

            associationUpdated = false;

            //check if ExtJs has created a association store dynamically.
            if (!(associationStore instanceof Ext.data.Store)) {
                associationStore = Ext.create('Ext.data.Store', {
                    model: association.associatedName
                });
            }

            //if the association store already contains data use this data to update.
            if (associationStore.getCount() > 0) {
                associationModel = associationStore.first()
            } else {
                associationModel = Ext.create(association.associatedName);
            }

            Ext.each(associationModel.fields.keys, function(key) {
                var fieldName = association.associationKey + '['+ key +']';

                if (fieldName in values) {
                    associationModel.set(key, values[fieldName]);
                    associationUpdated = true;
                    delete values[fieldName];
                }
            });

            //if the store has no record, add the updated association model.
            if (associationStore.getCount() === 0 && associationUpdated) {
                associationStore.add(associationModel);
            }
            record[association.storeName] = associationStore;
        });

        fields.each(function(field) {
            var name = field.name;
            if (name in values) {
                data[name] = values[name];
            }
        });

        record.beginEdit();
        record.set(data);
        record.endEdit();

        return this;
    }
});
//{/block}