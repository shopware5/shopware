
//{namespace name=backend/application/main}

//{block name="backend/application/Shopware.window.BatchRequests"}

Ext.define('Shopware.window.BatchRequests', {
    extend: 'Ext.window.Window',
    modal: true,
    bodyPadding: 20,
    requests: [],
    autoClose: true,
    closeDelay: 750,

    initComponent: function() {
        var me = this,
            requests = Ext.clone(me.requests);

        me.items = me.createItems(requests);
        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);

        var request = requests.shift();
        me.prepareRequest(request, requests);
    },

    createItems: function(requests) {
        var me = this, items = [];

        Ext.each(requests, function(request) {
            request.progressBar = Ext.create('Ext.ProgressBar', {
                animate: true,
                text: request.text,
                value: 0,
                height: 20,
                margin: '15 0 0'
            });
            items.push(request.progressBar);
        });

        return [{
            xtype: 'container',
            flex: 1,
            defaults: {
                anchor: '100%'
            },
            items: items
        }];
    },

    /**
     * Access point before first request iteration will be send
     * @param request
     * @param requests
     */
    prepareRequest: function(request, requests) {
        var me = this;
        me.send(request, requests);
    },

    /**
     * Executes the next iteration of the provided request
     * @param request
     * @param requests
     */
    send: function(request, requests) {
        var me = this;

        if (!request.params.hasOwnProperty('iteration')) {
            request.params.iteration = 0;
        }
        request.params.iteration++;

        Ext.Ajax.request({
            url: request.url,
            params: request.params,
            success: function(operation) {
                me.handleResponse(request, operation, requests);
            }
        });
    },

    /**
     * Called after each request iteration
     * @param request
     * @param operation
     * @param requests
     */
    handleResponse: function(request, operation, requests) {
        var me = this;
        var response = Ext.decode(operation.responseText);

        me.updateProgressBar(request, response);

        if (me.cancelProcess) {
            me.canceled();
            return true;
        }

        if (response.finish == false) {
            return me.send(request, requests);
        }

        if (requests.length <= 0) {
            return me.finish();
        }

        request = requests.shift();
        return me.prepareRequest(request, requests);
    },

    updateProgressBar: function(request, response) {
        request.progressBar.updateProgress(response.progress, response.text, true);
    },

    /**
     * called when all requests finished
     */
    finish: function() {
        var me = this;

        me.cancelButton.disable();
        if (me.autoClose) {
            Ext.defer(Ext.bind(me.destroy, me), me.closeDelay);
        }
    },

    canceled: function() {
        var me = this;

        me.cancelButton.disable();
        if (me.autoClose) {
            Ext.defer(Ext.bind(me.destroy, me), me.closeDelay);
        }
    },

    createToolbar: function () {
        var me = this;

        return me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.createToolbarItems()
        });
    },

    createToolbarItems: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name="progress_window/cancel_button_text"}{/s}',
            handler: Ext.bind(me.cancel, me)
        });

        return ['->', me.cancelButton];
    },

    cancel: function() {
        this.cancelProcess = true;
    }
});

//{/block}