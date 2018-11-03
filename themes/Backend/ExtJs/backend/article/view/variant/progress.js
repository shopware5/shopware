/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article variants - Detail.
 * The detail component is an extension of the Enlight.app.Window. It displayed when the
 * user clicks the pencil action column in the variant listing to edit the selected
 * variant over the detail page.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Variants
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/progress"}
Ext.define('Shopware.apps.Article.view.variant.Progress', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-variant-progress-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-variant-progress-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'anchor',
    /**
     * Define window width
     * @integer
     */
    width:420,
    /**
     * Define window height
     * @integer
     */
    height:255,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:false,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:false,

    footerButton: true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    modal: true,

    cancelProcess: false,
    inProcess: false,

    /**
     * Contains the batch size for each request of the generation.
     */
    batchSize: 50,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-variant-progress-window',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        title:'{s name=progress/title}Variant generation{/s}',
        process: '{s name=progress/message}[0] of [1] variants created...{/s}',
        errorMessage: '{s name=progress/error_message}An error has occurred while generate the article variants:{/s}',
        errorTitle: '{s name=progress/error_title}Error{/s}',
        generationType: '{s name=progress/generate_type}Merging{/s}',
        types: {
            overrideType: '{s name=progress/override_type}Override{/s}',
            mergeType: '{s name=progress/merge_type}Merge{/s}'
        },
        notice: '{s name=progress/notice}The current selection of groups and options will create [0] possible variants. <br><br>This process will take about [1] minutes depending on your system resources. <br>Do you want to continue?{/s}'
    },

    bodyPadding: 10,
    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.items = me.createItems();
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     * Creates the items for the progress window.
     */
    createItems: function() {
        var me = this, defaultValue = 2;

        if (me.groupsChanged) {
            defaultValue = 1;
        }
        if (!Ext.isNumeric(me.batchSize)) {
            me.batchSize = 50;
        }

        me.progressField = Ext.create('Ext.form.field.Display', {
            value: Ext.String.format(me.snippets.process, 0, me.configurator.get('totalCount')),
            anchor: '100%',
            margin: '10 0',
            style: 'text-align: center;'
        });

        me.typeComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            queryMode: 'local',
            margin: '0 0 10',
            anchor: '100%',
            valueField: 'id',
            labelWidth: 120,
            editable: false,
            disabled: me.groupsChanged,
            fieldLabel: me.snippets.generationType,
            displayField: 'name',
            value: defaultValue,
            store: new Ext.data.SimpleStore({
                fields:['id', 'name'],
                data: [
                    [1, me.snippets.types.overrideType],
                    [2, me.snippets.types.mergeType]
                ]
            })
        });

        me.cancelButton = Ext.create('Ext.button.Button', {
            text: '{s name=progress/cancel}{/s}',
            anchor: '50%',
            cls: 'secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.cancelProcess = true;
                me.startButton.setDisabled(false);
                if (me.inProcess) {
                    me.inProcess = false;
                    me.fireEvent('variantsGenerated', me.article, me);
                } else {
                    me.destroy();
                }
            }
        });

        me.startButton = Ext.create('Ext.button.Button', {
            text: '{s name=progress/start}{/s}',
            anchor: '50%',
            cls: 'primary',
            handler: function() {
                me.inProcess = true;
                if (!Ext.isNumeric(me.batchSize)) {
                    me.batchSize = 50;
                }
                me.startButton.setDisabled(true);
                me.startVariantGenerator(me.configurator, 0, me.batchSize, me.article);
            }
        });

        var totalTime = me.configurator.get('totalCount') / me.batchSize * 1.5 / 60;
        totalTime = Ext.Number.toFixed(totalTime, 0);

        var notice = Ext.create('Ext.container.Container', {
            html: Ext.String.format(me.snippets.notice, me.configurator.get('totalCount'), totalTime),
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0; text-align: center;',
            anchor: '100%'
        });

        return [ me.typeComboBox, notice, me.progressField, me.cancelButton, me.startButton ];
    },

    /**
     * Internal helper function which used for a queue process in the variant   generation.
     * The function uses the passed model with an offset and limit to create in each queue
     * run for example 50 variants.
     * @param model
     * @param offset
     * @param limit
     * @param article
     */
    startVariantGenerator: function(model, offset, limit, article) {
        var me = this;

        model.set('offset', offset);
        model.set('limit', limit);
        model.set('mergeType', me.typeComboBox.getValue());
        model.setDirty();
        model.save({
            success: function(record, operation) {
                var doneVariants = Ext.Array.min([offset + limit, model.get('totalCount')]);
                var progress = doneVariants / model.get('totalCount');
                progress = Ext.Number.toFixed(progress, 2);

                me.progressField.setValue(Ext.String.format(me.snippets.process, doneVariants, model.get('totalCount')));

                //if the last variant was created we can hide the window and reload the listing
                if (offset + limit >= model.get('totalCount') || me.cancelProcess) {
                    me.inProcess = false;
                    me.startButton.setDisabled(false);
                    me.fireEvent('variantsGenerated', article, me);
                } else {
                    //otherwise we have to call this function recursive with the next offset
                    offset = offset + limit;
                    me.startVariantGenerator(model, offset, limit, article);
                }
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    message = '<br>' + rawData.message;

                me.inProcess = false;
                Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.errorMessage + message, me.snippets.growlMessage);
            }
        });

    }
});
//{/block}
