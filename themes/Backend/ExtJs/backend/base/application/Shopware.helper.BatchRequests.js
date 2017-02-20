
Ext.define('Shopware.helper.BatchRequests', {

    start: function(requests) {
        this.prepareRequest(
            requests.shift(),
            requests
        );
    },

    prepareRequest: function(request, requests) {
        this.send(request, requests);
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
        // request.progressBar.updateProgress(response.progress, response.text, true);
    },

    /**
     * called when all requests finished
     */
    finish: function() { },

    cancel: function() {
        this.cancelProcess = true;
    },

    canceled: function() { }
});