
//{namespace name=backend/application/main}
//{block name="backend/application/Shopware.detail.Controller"}
Ext.define('Shopware.detail.Controller', {
    extend: 'Enlight.app.Controller',

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: {
        helper: 'Shopware.model.Helper'
    },

    /**
     * Title of the Shopware growl message, which displayed when a model saved
     * successfully.
     * @type { String }
     */
    saveSuccessTitle: '{s name="detail_controller/save_success_title"}Success{/s}',

    /**
     * Message of the Shopware growl message, which displayed when a model saved
     * successfully.
     * @type { String }
     */
    saveSuccessMessage: '{s name="detail_controller/save_success_message"}Item saved successfully{/s}',

    /**
     * Title of the Shopware sticky notification message, which displayed when a model
     * can't be saved and some model violations thrown.
     * @type { String }
     */
    violationErrorTitle: '{s name="detail_controller/violation_error_title"}Violation errors{/s}',


    /**
     * Title of the Shopware growl message, which displayed when the user
     * tries to save the detail data but the form panel contains invalid data.
     *
     * @type { String }
     */
    invalidFormTitle: '{s name="detail_controller/invalid_form_title"}Form validation error{/s}',

    /**
     * Message of the Shopware growl message, which displayed when the user
     * tries to save the detail data but the form panel contains invalid data.
     *
     * @type { String }
     */
    invalidFormMessage: '{s name="detail_controller/invalid_form_message"}The form contains invalid data, please check the inserted values.{/s}',

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.controller.Detail', {
         *          extend: 'Shopware.detail.Controller',
         *          configure: function() {
         *              return {
         *                  eventAlias: 'product'
         *              }
         *          }
         *      });
         */
        displayConfig: {

            /**
             * @required
             *
             * Full class name of the Shopware.window.Detail.
             * This class is required to get the alias of the component.
             *
             * @type { string }
             */
            detailWindow: undefined,

            /**
             * @required
             *
             * Suffix alias for the different component events.
             * This alias must the same alias of the { @link Shopware.grid.Panel:eventAlias }  component.
             * If you don't know the alias you can output the alias of the grid panel as follow:
             * console.log("alias", me.eventAlias);
             *
             * If you haven't configured a custom event alias, the { @link Shopware.grid.Panel } creates
             * the event alias over the configured model.
             * @example
             * If you passed a store with an model named: 'Shopware.apps.Product.model.Product'
             * the { @link Shopware.grid.Panel } use "product" as event alias.
             *
             * @type { string }
             */
            eventAlias: undefined
        },

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = { };

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({ }, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({ }, config, definition.configure());
            }
            config = Ext.apply({ }, config, this.displayConfig);

            return config;
        },


        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function() {
        return { };
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },


    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * Initialisation of this component.
     * The function calls the internal function createListingWindow to open
     * the listing window.
     * After the window created the function adds the event controls
     * over the createControls function.
     */
    init: function () {
        var me = this;

        //Check configuration for extended detail controllers.
        //The class name check prevents the exception if the default components creates his own controller.
        if (me.$className !== 'Shopware.detail.Controller') {
            me.checkRequirements();
        }

        if (me.getConfig('eventAlias') && me.getConfig('detailWindow')) {
            me.registerEvents();
            me.control(me.createControls());
        }

        me.callParent(arguments);
    },

    /**
     * Helper function which checks all component requirements.
     */
    checkRequirements: function() {
        var me = this;

        if (!me.getConfig('eventAlias')) {
            me.throwException(me.$className + ": Component requires the `eventAlias` property in the configure() function");
        }
        if (!me.getConfig('detailWindow')) {
            me.throwException(me.$className + ": Component requires the `detailWindow` property in the configure() function");
        }
    },

    /**
     * Helper function to reload the controller event listeners.
     * This function is used from the Shopware.window.Detail.
     * Workaround for the sub application event bus.
     */
    reloadControls: function() {
        var me = this;

        me.checkRequirements();

        me.registerEvents();
        me.control(me.createControls());
    },

    /**
     * Registers all custom events of this component.
     */
    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Event fired at the beginning of the { @link #onSave } event listener function
             * If the event listener returns false, the save process will be canceled.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('start-save-record'),

            /**
             * Event fired before the record parameter will be updated with the form panel data of the
             * detail window.
             * If the event listener function returns false, the updateRecord() won't be executed.
             * This allows you to update the record manually and cancel the shopware default process.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('update-record-on-save'),

            /**
             * Event fired after the record parameter updated with the detail window form data.
             * This event can be used to modify the record data before the send request will be send.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('after-update-record-on-save'),

            /**
             * Event fired if the save request throws an exception.
             * In case of an exception the data object can contains a Doctrine violations array if the
             * Doctrine model was validated through the Symfony Constraint validator.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Object } data - Response text of the save request. Contains a violation property if the model was validate over the Doctrine validator.
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('save-exception'),

            /**
             * Event fired before the save request will be fired.
             * If the event listener function of this event returns false, the save request won't be started.
             * The record parameter contains the updated model data and used for the save request.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('before-send-save-request'),

            /**
             * Event fired after the save request done and was successfully.
             * After this event the detail window will be reloaded over the { @link Shopware.window.Detail:loadRecord } function.
             * The result parameter will be passed to the loadRecord function, to allow a php modification of the record data.
             *
             * @param { Shopware.detail.Controller } controller - Instance of this controller
             * @param { Shopware.data.Model } result - Result set of the save request.
             * @param { Shopware.window.Detail } window - Detail window which fired the save event
             * @param { Shopware.data.Model } record - The displayed record of the detail window which used for the save request.
             * @param { Ext.form.Panel } form - Form panel of the detail window. This window contains the updated record data.
             */
            me.getEventName('save-successfully')
        );
    },

    /**
     * Creates the control object which contains all event listener
     * definitions for this controller.
     *
     * This function requires the displayConfig.listingGrid parameter.
     * If this parameter isn't set, the function won't be called.
     *
     * @returns { Object }
     */
    createControls: function () {
        var me = this, alias, controls = {};

        alias = Ext.ClassManager.getAliasesByName(me.getConfig('detailWindow'));
        if (!alias || alias.length <= 0) {
            return false;
        }

        alias = alias[0];
        alias = alias.replace('widget.', '');
        controls[alias] = me.createDetailWindowControls();

        return controls;
    },


    /**
     * Creates all event listener definitions for the detail window events.
     *
     * @returns { Object }
     */
    createDetailWindowControls: function() {
        var me = this, events = {};

        events[me.getEventName('save')] = me.onSave;

        return events;
    },

    /**
     * Event listener function of the { @link Shopware.window.Detail } 'save' event.
     * This event is used to save a single record with the modified detail data of
     * the detail window.
     * This function throws the following events
     *  - start-save-record
     *  - update-record-on-save
     *  - after-update-record-on-save
     *  - save-exception
     *  - before-send-save-request
     *  - save-successfully
     *
     * @param { Shopware.window.Detail } window
     * @param { Shopware.data.Model } record
     */
    onSave: function(window, record) {
        var me = this, proxy = record.getProxy(), data, form = window.formPanel;

        //check if the Ext JS form is valid
        if (!form.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage(
                me.invalidFormTitle,
                me.invalidFormMessage
            );
            return false;
        }

        //allows to cancel the save process.
        if (!Shopware.app.Application.fireEvent(me.getEventName('start-save-record'), me, window, record, form)) {
            return false;
        }

        //this event allows to skip the update record process.
        if (Shopware.app.Application.fireEvent(me.getEventName('update-record-on-save'), me, window, record, form)) {
            //update the passed record with the form data.
            form.getForm().updateRecord(record);
        }

        Shopware.app.Application.fireEvent(me.getEventName('after-update-record-on-save'), me, window, record, form);

        //add event listener to the model proxy to get access on thrown exceptions
        proxy.on('exception', function (proxy, response) {
            //remove loading mask from the window
            window.setLoading(false);

            data = Ext.decode(response.responseText);

            Shopware.app.Application.fireEvent(me.getEventName('save-exception'), me, data, window, record, form);

            if (data.error) {
                Shopware.Notification.createGrowlMessage('', data.error);
            }

            //check if the response text contains field violations
            if (data.violations && data.violations.length > 0) {
                //if violations exists, create a growl message and try to focus the fields.
                me.createViolationMessage(data.violations);
                me.markFieldsAsInvalid(window, data.violations);
            }

        }, me, { single: true });

        if (!me.hasModelAction(record, 'update') || !me.hasModelAction(record, 'create')) {
            window.destroy();
            return;
        }

        //active loading mask of the detail window
        window.setLoading(true);

        if (!Shopware.app.Application.fireEvent(me.getEventName('before-send-save-request'), me, window, record, form)) {
            return false;
        }

        //start save request of the { @link Shopware.data.Model }
        record.save({
            //success callback function.
            success: function(result, operation) {
                window.setLoading(false);

                if (window instanceof Shopware.window.Detail) {
                    window.loadRecord(result);
                }

                Shopware.app.Application.fireEvent(me.getEventName('save-successfully'), me, result, window, record, form, operation);

                Shopware.Notification.createGrowlMessage(
                    me.saveSuccessTitle,
                    me.saveSuccessMessage
                );
            }
        });
    },

    /**
     * Helper function which creates an <ul> for the different violation messages.
     * This message will be displayed in the sticky growl message.
     *
     * @param { Array } violations
     */
    createViolationMessage: function(violations) {
        var me = this,
            template = '';

        Ext.each(violations, function(violation) {
            template += '<li style="line-height: 13px; padding: 3px 0"><b>' + violation.property + '</b>: ' + violation.message + '</li>';
        });

        template = '<ul>' + template + '</ul>';
        Shopware.Notification.createStickyGrowlMessage({
            title: me.violationErrorTitle,
            text: template,
            width: 400
        });
    },

    /**
     * Helper function to focus a violation field and set the violation message as field error message.
     *
     * @param { Shopware.window.Detail } window
     * @param { Array } violations
     */
    markFieldsAsInvalid: function(window, violations) {
        var me = this;

        Ext.each(violations, function(violation) {
            var field = me.getFieldByName(window.formPanel.getForm().getFields().items, violation.property);
            if (field) {
                field.focus();
                field.markInvalid(violation.message);
            }
        });
    },

    /**
     * Helper function to prefix the passed event name with the event alias.
     *
     * @param { String } name
     *
     * @returns { String }
     */
    getEventName: function (name) {
        return this.getConfig('eventAlias') + '-' + name;
    }

});
//{/block}
