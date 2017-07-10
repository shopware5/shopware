{literal}
<script type="text/javascript">
    document.getElementById('main-script').addEventListener('load', function() {

        if (!asyncCallbacks) {
            return false;
        }

        for (var i = 0; i < asyncCallbacks.length; i++) {
            if (typeof asyncCallbacks[i] === 'function') {
                asyncCallbacks[i].call(document);
            }
        }

        document.asyncReady = function (callback) {
            if (typeof callback === 'function') {
                window.setTimeout(callback.apply(document), 0);
            }
        }
    });
</script>
{/literal}
