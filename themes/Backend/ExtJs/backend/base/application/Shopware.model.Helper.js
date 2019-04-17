
//{namespace name=backend/application/main}

//{block name="backend/application/Shopware.model.Helper"}

/**
 * Helper class which contains different global functions which used
 * by the new backend components.
 */
Ext.define('Shopware.model.Helper', {

    /**
     * Helper function which creates the model field set
     * title.
     * Shopware use as default the model name of
     * the passed record.
     *
     * @param { String } modelName - Class name of the model.
     * @return { String }
     */
    getModelName: function (modelName) {
        return modelName.substr(modelName.lastIndexOf(".") + 1);
    },

    /**
     * Helper function to create the event alias.
     *
     * @returns { String }
     */
    getEventAlias: function (modelClass) {
        return this.getModelName(modelClass).toLowerCase();
    },

    /**
     * Helper function which checks if the passed model instance
     * has an proxy api for the passed action.
     *
     * @param model { Shopware.data.Model }
     * @param action { string }
     * @returns { boolean }
     */
    hasModelAction: function (model, action) {
        return (model.proxy && model.proxy.api && model.proxy.api[action]);
    },

    /**
     * Helper function to convert a camel case word into a normal
     * word.
     *
     * @param { String} word
     * @returns { String }
     */
    camelCaseToWord: function (word) {
        var newWord;

        newWord = word.split(/(?=[A-Z])/).map(function (p) {
            return p.charAt(0).toLowerCase() + p.slice(1);
        }).join(' ');

        return newWord.charAt(0).toUpperCase() + newWord.slice(1);
    },

    /**
     * Helper function which creates a human readable field name.
     * This function use the camelCaseToWord function and additionally
     * removes the " id" suffix of foreign keys.
     *
     * @param word
     * @returns { String }
     */
    getHumanReadableWord: function(word) {
        word = this.camelCaseToWord(word);
        word = word.replace(' id', '');
        return word;
    },

    /**
     * Helper function to find specify associations of the passed model name.
     * The class name of the model for which the associations should be detected is passed on.
     * The conditions parameter can be used for filtering specific associations.
     * This parameter can contain an array with specific filter criteria.
     * The function checks every filter for each association. If one of the filters is true,
     * the association is returned in an array at the end.
     *
     * A filter criteria can have the following properties:
     *  - [string] associationKey
     *  - [string] relation
     *      -   Checks a specific type of association
     *      -   If left out, the Association type will not be checked
     *      -   Possible types: 'OneToOne', 'OneToMany', 'ManyToMany'
     *  - [boolean] hasAssociations
     *      -   Checks whether the Association has its own Associations
     *  - [string] associationTypes
     *      -   Checks whether the Associations have their own Association of a certain type
     *      -   Possible types: 'OneToOne', 'OneToMany', 'ManyToMany'
     *
     * @param className
     * @param conditions
     */
    getAssociations: function (className, conditions) {
        var me = this,
            associations = [],
            model = Ext.create(className);

        conditions = conditions || [];

        if (model.associations.length <= 0) {
            return associations;
        }
        Ext.each(model.associations.items, function (association) {
            if (me.matchAssociationConditions(association, conditions)) {
                associations.push(association);
            }
        });
        return associations;
    },


    /**
     * Helper function which returns the associated store of the passed association.
     * If the passed records contains no instance of the association, the function
     * creates an new empty store.
     *
     * @param record
     * @param association
     * @returns { Ext.data.Store }
     */
    getAssociationStore: function (record, association) {
        var store;

        store = record[association.storeName];
        if (!(store instanceof Ext.data.Store)) {
            store = Ext.create('Ext.data.Store', {
                model: association.associatedName
            });
            record[association.storeName] = store;
        }

        return store;
    },

    /**
     * Helper function for the { @link #getAssociations } function.
     * This function checks if the passed assiociation matchs on one of the passed conditions.
     * If one condition match on the passed association the function returns true.
     *
     * @param { Ext.data.Association } association - The association which has to be checked.
     * @param { Array } conditions - Array of conditions
     * @returns boolean
     */
    matchAssociationConditions: function (association, conditions) {
        var associationInstance = Ext.create(association.associatedName),
            match = false;

        //if no conditions passed, the loop won't be accessed
        if (conditions && conditions.length <= 0) {
            match = true;
        }

        Ext.each(conditions, function (condition) {
            //association key check. The condition association key has to be an array.
            if (condition.associationKey && !Ext.Array.contains(condition.associationKey, association.associationKey)) {
                return true;
            }

            if (Ext.isString(condition.relation) && !Ext.isString(association.relation)) {
                return true;
            }

            //relation type has been set? if isn't matched continue with next condition
            if (condition.relation && condition.relation.toLowerCase() !== association.relation.toLowerCase()) {
                return true;
            }

            //filter condition for association with own associations
            if (condition.hasAssociations === true && associationInstance.associations.length <= 0) {
                return true;
            }

            //filter condition for association without own associations
            if (condition.hasAssociations === false && associationInstance.associations.length > 0) {
                return true;
            }

            if (condition.associationTypes && associationInstance.association.length <= 0) {
                return true;
            }

            //filter condition for association
            if (condition.associationTypes) {
                var typeMatch = false;

                Ext.each(associationInstance.associations.items, function (item) {
                    Ext.each(condition.associationTypes, function (type) {
                        if (type.toLowerCase() === item.relation.toLowerCase()) {
                            typeMatch = true;
                        }
                    });
                });

                if (typeMatch === false) {
                    return true;
                }
            }

            match = true;
            return false;   //cancel foreach
        });

        return match;
    },

    /**
     * Adds the shopware default column configuration for a listing integer
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param { Object } column - The column object where the properties will be applied.
     * @return { Ext.grid.column.Number }
     */
    applyIntegerColumnConfig: function (column) {
        column.xtype = 'numbercolumn';
        column.renderer = this.integerColumnRenderer;
        column.align = 'right';
        column.editor = this.applyIntegerFieldConfig({});
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing string
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param { Object } column - The column object where the properties will be applied.
     * @return { Ext.grid.column.Column }
     */
    applyStringColumnConfig: function (column) {
        column.editor = this.applyStringFieldConfig({});
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing boolean
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param { Object } column - The column object where the properties will be applied.
     * @return { Ext.grid.column.Boolean }
     */
    applyBooleanColumnConfig: function (column) {
        column.xtype = 'booleancolumn';
        column.renderer = this.booleanColumnRenderer;
        column.editor = this.applyBooleanFieldConfig({});
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing date
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param { Object } column - The column object where the properties will be applied.
     * @return { Ext.grid.column.Date }
     */
    applyDateColumnConfig: function (column, format) {
        column.xtype = 'datecolumn';
        if (format) {
            column.format = format;
        }
        column.editor = this.applyDateFieldConfig({});
        return column;
    },

    /**
     * Adds the shopware default column configuration for a listing float
     * column.
     * The column configuration will be applied to the passed column object.
     *
     * @param { Object } column - The column object where the properties will be applied.
     * @return { Ext.grid.column.Number }
     */
    applyFloatColumnConfig: function (column) {
        column.xtype = 'numbercolumn';
        column.align = 'right';
        column.editor = this.applyFloatFieldConfig({});
        return column;
    },

    /**
     * Shopware default renderer function for a boolean listing column.
     * This functions expects a boolean value as first parameter.
     * The function returns a span tag with a css class for a checkbox
     * sprite.
     *
     * @param { boolean|int } value
     * @return { String }
     */
    booleanColumnRenderer: function (value) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (value === true || value === 1) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    /**
     * Shopware default renderer function for a integer listing column.
     * Grid number columns will be displayed with two precisions so this function
     * converts the passed value parameter to an integer value.
     *
     * @param { int|float } value
     * @return { int }
     */
    integerColumnRenderer: function (value) {
        return Ext.util.Format.number(value, '0');
    },



    /**
     * Adds the shopware default form field configuration for integer form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyIntegerFieldConfig: function (field) {
        field.xtype = 'numberfield';
        field.align = 'right';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for string form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyStringFieldConfig: function (field) {
        field.xtype = 'textfield';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for boolean form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyBooleanFieldConfig: function (field) {
        field.xtype = 'checkbox';
        field.uncheckedValue = false;
        field.inputValue = true;
        return field;
    },

    /**
     * Adds the shopware default form field configuration for date form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyDateFieldConfig: function (field) {
        field.xtype = 'datefield';
        field.format = 'd.m.Y';
        return field;
    },

    /**
     * Adds the shopware default form field configuration for float form field.
     * The field configuration will be applied to the passed field object.
     *
     * @param field
     * @return Ext.form.field.Number
     */
    applyFloatFieldConfig: function (field) {
        field.xtype = 'numberfield';
        field.align = 'right';
        return field;
    },


    /**
     * Helper function to get the component type for the passed
     * association.
     * The component type is defined in the { @link Shopware.data.Model:displayConfig }
     * @param association
     * @returns { string|boolean }
     */
    getComponentTypeOfAssociation: function(association) {
        switch (association.relation.toLowerCase()) {
            case 'onetoone':
                return 'detail';
            case 'onetomany':
                return 'listing';
            case 'manytomany':
                return 'related';
            case 'manytoone':
                return 'field';
        }
        return false;
    },


    /**
     * Creates an store for association search fields like many to one association
     * or for the { @link Shopware.grid.Association } component.
     * The store contains an extra parameter for the association key.
     *
     * @param model { String }
     * @param associationKey { String }
     * @param searchUrl { String }
     * @returns { Ext.data.Store }
     */
    createAssociationSearchStore: function (model, associationKey, searchUrl) {
        return Ext.create('Ext.data.Store', {
            model: model,
            proxy: {
                type: 'ajax',
                url: searchUrl,
                reader: { type: 'application', root: 'data', totalProperty: 'total' },
                extraParams: { association: associationKey }
            }
        });
    },

    /**
     * Helper function to get a field by his name.
     *
     * @param fields
     * @param name
     * @returns { undefined|Object }
     */
    getFieldByName: function(fields, name) {
        var result = undefined;

        Ext.each(fields, function(field) {
            if (field.name == name) {
                result = field;
                return false;
            }
        });

        return result;
    },

    /**
     * Helper function to throw an Shopware configuration error.
     *
     * @param { String } message
     * @param { String } title
     */
    throwException: function(message, title) {
        title = title || "Shopware configuration error";

        throw {
            name: title,
            message: message,
            toString: function() { return this.name + ": " + this.message }
        };
    },


    /**
     * Helper function which validates if the passed component
     * is configured as lazy loading component.
     * The function checks the following conditions:
     *  1. Component association has been set
     *  2. `lazyLoading` flag of association is set
     *  3. Component has a getStore function, and the store contains no data
     *  4. getStore returns an instance of Shopware.store.Association or the store configured a read url
     *
     * @param component
     * @returns { boolean }
     */
    isLazyLoadingComponent: function(component) {
        var me = this;

        if (!(component.association)) {
            return false;
        }

        if (!(component.association.lazyLoading)) {
            return false;
        }

        if (typeof component.getStore !== 'function') {
            return false;
        }

        if (component.getStore().getCount() > 0) {
            return false;
        }

        return (component.getStore() instanceof Shopware.store.Association)
            || (me.hasModelAction(component.getStore(), 'read') !== undefined);
    }
});

//{/block}
