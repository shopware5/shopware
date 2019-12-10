{block name="widgets_index_statistic_include"}
    <iframe id="refresh-statistics" width="0" height="0" style="display:none;"></iframe>
    <script>
        /**
         * @returns { boolean }
         */
        function hasCookiesAllowed () {
            if (window.cookieRemoval === 0) {
                return true;
            }

            if (window.cookieRemoval === 1) {
                if (document.cookie.indexOf('cookiePreferences') !== -1) {
                    return true;
                }

                return document.cookie.indexOf('cookieDeclined') === -1;
            }

            /**
             * Must be cookieRemoval = 2, so only depends on existence of `allowCookie`
             */
            return document.cookie.indexOf('allowCookie') !== -1;
        }

        /**
         * @returns { boolean }
         */
        function isDeviceCookieAllowed () {
            var cookiesAllowed = hasCookiesAllowed();

            if (window.cookieRemoval !== 1) {
                return cookiesAllowed;
            }

            return cookiesAllowed && document.cookie.indexOf('"name":"x-ua-device","active":true') !== -1;
        }

        (function(window, document) {
            var par = document.location.search.match(/sPartner=([^&])+/g),
                pid = (par && par[0]) ? par[0].substring(9) : null,
                cur = document.location.protocol + '//' + document.location.host,
                ref = document.referrer.indexOf(cur) === -1 ? document.referrer : null,
                url = "{url module=widgets controller=index action=refreshStatistic}",
                pth = document.location.pathname.replace("{url controller=index}", "/");

            url += url.indexOf('?') === -1 ? '?' : '&';
            url += 'requestPage=' + encodeURIComponent(pth);
            url += '&requestController=' + encodeURI("{$Controller|escape}");
            if(pid) { url += '&partner=' + pid; }
            if(ref) { url += '&referer=' + encodeURIComponent(ref); }
            {if $sArticle.articleID}
            url += '&articleId=' + encodeURI("{$sArticle.articleID}");
            {/if}
            {if $sArticle.id && $Controller === 'blog'}
            url += '&blogId=' + encodeURI("{$sArticle.id}");
            {/if}

            {* Early simple device detection for statistics, duplicated in StateManager for resizes *}
            if (isDeviceCookieAllowed()) {
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

            document
                .getElementById('refresh-statistics')
                .src = url;
        })(window, document);
    </script>
{/block}
