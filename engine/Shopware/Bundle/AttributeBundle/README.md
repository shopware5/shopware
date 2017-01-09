# AttributeBundle

- **License**: Dual license AGPL v3 / Proprietary
- **Github Repository**: <https://github.com/shopware/shopware>
- **Issue-Tracker**: <https://issues.shopware.com>

## Controllers

There are only two controllers for handle all actions regarding attributes.

### Attributes controller

The attributes controller is used to manage the attribute tables of entities as well as loading and saving their data.

#### Alter attribute schemas

CRUD operations on attribute tables can be made with their corresponding actions listed below.

**listAction**

* Required parameters: `table`

**createAction**

* Required parameters:
    * `tableName` - Name of the attribute table
    * `columnName` - Name of the attirbute
    * `columnType` - Field type like `string`, `float`, ...
* Optional parameters: see `\Shopware\Models\Attribute\Configuration`

**updateAction**

* Required parameters:
    * `tableName` - Name of the attribute table
    * `originalName` - The attribute name to be updated
    * `columnType` - Field type like `string`, `float`, ...
* Optional parameters: see `\Shopware\Models\Attribute\Configuration`

**deleteAction**

* Required parameters:
    * `tableName` - Name of the attribute table
    * `originalName` - Attribute name to be deleted

**generateModelsAction**

Generate the attribute model for the given table. Should be called after changing an attribute schema.

* Required parameters:
    * `tableName` - Name of the attribute table

#### Loading and saving data

The **loadData** action requires two parameters, `_table` and `_foreignKey`. You will get an key/value array which holds all attributes of the given table. To save attributes into their tables, you have to use the **saveData** action which requires the same parameters as the **loadData** action. All data keys are prefixed with the `EXT_JS_PREFIX` prefix constant to prevent duplicate ext js form field names.

**Example**
```
$payload = [
    '_table' => 's_media',
    '_foreignKey' => 123,
    '__attribute_text1' => 'simple text attribute',
    '__attribute_my_custom_field' => 'text for my custom field'
]
```

### EntitySearch controller

There is only one action where you can search for any entity in Shopware.

* Required parameters:
    * `model` - Class name of the model to search, e.g. `\Shopware\Models\Article\Supplier`
* Extra parameters:
    * `ids` - If provided, only selects the given IDs and ignores all other parameters
* Optional parameters:
    * `limit` - Limits the result set
    * `offset` - Sets an offset to the result set
    * `term` - Term to search for in any column of entity
    * `sortings` - Sort results using the Doctrine sorting syntax
    * `conditions` - Filter results using the Doctrine filter syntax

## Services

These services are the key services for maintaining attributes.

### Create, update and delete attribute columns (CrudService)

This service allows you to manage attributes for nearly all attribute entities in Shopware. It implements all CRUD operations and enables you to change column types or create new attribute fields like you want.

**create($table, $column, $data) : void**

Create a new attribute column.

* Parameters:
    * `$table` - Name of the attribute table
    * `$column` - Name of the attribute column
    * `$unifiedType` - Data type of the column, see \Shopware\Bundle\AttributeBundle\Service\TypeMapping::$types
    * `$data` - Array with multiple options and configurations
        * For all options available, see `\Shopware\Models\Attribute\Configuration`

**update($table, $column, $data) : void**

Update an existing attribute column.

* Parameters:
    * `$table` - Name of the attribute table
    * `$originalColumnName` - Original name of the attribute column
    * `$newColumnName` - New attribute name, may be the same in case you don't want to change the name
    * `$unifiedType` - Data type of the column, see \Shopware\Bundle\AttributeBundle\Service\TypeMapping::$types
    * `$data` - Array with multiple options and configurations
        * For all options available, see `\Shopware\Models\Attribute\Configuration`


**delete($table, $column) : void**

Delete an attribute column.

* Parameters:
    * `$table` - Name of the attribute table
    * `$column` - Name of the attribute column

**get($table, $column) : ConfigurationStruct**

Get information about a single attribute column.

* Parameters:
    * `$table` - Name of the attribute table
    * `$column` - Name of the attribute column

**getList($table) : ConfigurationStruct[]**

Get information about all attribute columns of the provided table.

* Parameters:
    * `$table` - Name of the attribute table


### Perform database schema changes (SchemaOperator)

This Service is used by the `CrudService` to perform the schema changes with the configuration. In fact, it executes the queries to the database whereas the `CrudService` saves information about the columns and delegates the operations to the `SchemaOperator` service.

**createColumn($table, $column, $type) : void**

Creates a new column in given attribute table.

* Parameters:
    * `tableName` - Name of the attribute table
    * `column` - The attribute name to be updated
    * `columnType` - MySQL column type like `int(11)`, `varchar(255)`, ...

**changeColumn($table, $originalName, $newName, $type) : void**

Change the given column to a new name and type.

* Parameters:
    * `tableName` - Name of the attribute table
    * `originalName` - The attribute name to be updated
    * `newName` - New attribute name, may be the same in case you don't want to change the name
    * `type` - MySQL column type like `int(11)`, `varchar(255)`, ...

**dropColumn($table, $column) : void**

Drops the given column.

* Parameters:
    * `tableName` - Name of the attribute table
    * `column` - The attribute name to be dropped

**resetColumn($table, $column) : void**

Sets the value of all entries in the given column to `NULL`.

* Parameters:
    * `tableName` - Name of the attribute table
    * `column` - The attribute name to be dropped


## Backend components

If you want to show the attribute form in your own module, you can use either a grid plugin, a dedicated attribute button or a form component. The only required parameter is `table` which holds the name of the attribute table. In addition, you can set `allowTranslation` to `false` to disable the translation of the attributes. By default, it's set to `true` and therefore allows translation.

### Grid implementation

The grid plugin will add a new icon to your action column. The implementation is quite simple and can be done by adding the plugin to your grid.

**Example**

```
plugins: [
    {
        ptype: 'grid-attributes',
        table: 's_order_documents_attributes'
    }
]
```

### Button implementation

The button implementation is mostly used in situations where the grid plugin or form doesn't fit into the view, e.g. the media manager.

**Example**

```
Ext.create('Shopware.attribute.Button', {
    table: 's_media_attributes'
});
```

### Form implementation

The form implementation is the most used type of implementation. It will create a new `Ext.form.Panel` and can be implemented right in your existing forms or as a new tab - it's up to you.

**Example**

```
Ext.create('Shopware.attribute.Form', {
    title: 'Blog attributes',
    table: 's_blog_attributes',
});
```

#### Loading data

When using the form implementation, you have to handle the data loading and saving yourself.

A good approach could be to set the attribute form onto a variable available in your component so you can use it later on. A common usage is to load the attributes in the `initComponent()` method of the component. Keep in mind that you have to first render the component and then load the data afterwards using the `loadAttribute` method on the attribute form component. It takes a single argument which is the foreign key of the attribute table, e.g. the `user_id`.

**Example**

```
initComponent: function() {
    this.attributeForm = this.createAttributeForm();
    this.callParent(arguments);

    this.attributeForm.loadAttribute(this.record.get('id'));
}
```

#### Saving data

To save the user input, you just have to call the `saveAttribute` method with the foreign key on the attribute form since you already configured it when creating it.

**Example**

```
onSave: function(record) {
    var me = this;

    record.save({
        callback: function() {
            me.attributeForm.saveAttribute(record.get('id'));
        }
    });
}
```
