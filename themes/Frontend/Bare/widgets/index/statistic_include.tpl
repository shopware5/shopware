{block name="widgets_index_statistic_include"}
    <script async id="refresh-statistic"></script>
    <script>
        (function(window, document) {
            var cok = document.cookie.match(/session-{$Shop->getId()}=([^;])+/g),
                sid = (cok && cok[0]) ? cok[0] : null,
                par = document.location.search.match(/sPartner=([^&])+/g),
                pid = (par && par[0]) ? par[0].substring(9) : null,
                cur = document.location.protocol + '//' + document.location.host,
                ref = document.referrer.indexOf(cur) === -1 ? document.referrer : null,
                url = "{url module=widgets controller=index action=refreshStatistic forceSecure}",
                pth = document.location.pathname.replace("{url controller=index}", "/");

            url += url.indexOf('?') === -1 ? '?' : '&';
            url += 'requestPage=' + encodeURI(pth);
            url += '&requestController=' + encodeURI("{$Controller|escape}");
            if(sid) { url += '&' + sid; }
            if(pid) { url += '&partner=' + pid; }
            if(ref) { url += '&referer=' + encodeURI(ref); }
            {if $sArticle.articleID}
            url += '&articleId=' + encodeURI("{$sArticle.articleID}");
            {/if}

            {* Early simple device detection for statistics, duplicated in StateManager for resizes *}
            if (document.cookie.indexOf('x-ua-device') === -1) {
                var i = 0,
                    device = 'desktop',
                    width = window.innerWidth,
                    breakpoints = window.statisticDevices;

                if (typeof width !== 'number') {
                    width = (document.documentElement.clientWidth !== 0) ? document.documentElement.clientWidth : document.body.clientWidth;
                }

                for (; i < breakpoints.length; i++) {
                    if (width >= ~~(breakpoints[i].enter) && width <= ~~(breakpoints[i].exit)) {
                        device = breakpoints[i].device;
                    }
                }

                document.cookie = 'x-ua-device=' + device + '; path=/';
            }

            document.getElementById('refresh-statistic').setAttribute('src', url);
        })(window, document);
    </script>
{/block}
