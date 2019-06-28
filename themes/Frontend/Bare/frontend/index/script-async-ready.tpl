{literal}
<script>
    /**
     * Wrap the replacement code into a function to call it from the outside to replace the method when necessary
     */
    var replaceAsyncReady = window.replaceAsyncReady = function() {
        document.asyncReady = function (callback) {
            if (typeof callback === 'function') {
                window.setTimeout(callback.apply(document), 0);
            }
        };
    };

    document.getElementById('main-script').addEventListener('load', function() {
        if (!asyncCallbacks) {
            return false;
        }

        for (var i = 0; i < asyncCallbacks.length; i++) {
            if (typeof asyncCallbacks[i] === 'function') {
                asyncCallbacks[i].call(document);
            }
        }

        replaceAsyncReady();
    });
</script>
{/literal}
