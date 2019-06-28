{block name="frontend_index_start"}{/block}
{block name="frontend_index_doctype"}
<!DOCTYPE html>
{/block}

{block name='frontend_index_html'}
<html class="no-js" lang="{s name='IndexXmlLang'}{/s}" itemscope="itemscope" itemtype="http://schema.org/WebPage">
{/block}

{block name='frontend_index_header'}
    {include file='frontend/index/header.tpl'}
{/block}

<body class="{block name="frontend_index_body_classes"}{strip}
    is--ctl-{controllerName|lower} is--act-{controllerAction|lower}
    {if $sUserLoggedIn} is--user{/if}
    {if $sOneTimeAccount} is--one-time-account{/if}
    {if $sTarget} is--target-{$sTarget|escapeHtml}{/if}
    {if $theme.checkoutHeader && (({controllerName|lower} == "checkout" && {controllerAction|lower} != "cart") || ({controllerName|lower} == "register" && ($sTarget != "account" && $sTarget != "address")))} is--minimal-header{/if}
    {if !$theme.displaySidebar} is--no-sidebar{/if}
    {/strip}{/block}" {block name="frontend_index_body_attributes"}{/block}>

    {block name='frontend_index_after_body'}{/block}

    {block name="frontend_index_page_wrap"}
        <div class="page-wrap">

            {* Message if javascript is disabled *}
            {block name="frontend_index_no_script_message"}
                <noscript class="noscript-main">
                    {s name="IndexNoscriptNotice" assign="snippetIndexNoscriptNotice"}{/s}
                    {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetIndexNoscriptNotice borderRadius=false}
                </noscript>
            {/block}

            {block name='frontend_index_before_page'}{/block}

            {* Shop header *}
            {block name='frontend_index_navigation'}
                <header class="header-main">
                    {* Include the top bar navigation *}
                    {block name='frontend_index_top_bar_container'}
                        {include file="frontend/index/topbar-navigation.tpl"}
                    {/block}

                    {block name='frontend_index_header_navigation'}
                        <div class="container header--navigation">

                            {* Logo container *}
                            {block name='frontend_index_logo_container'}
                                {include file="frontend/index/logo-container.tpl"}
                            {/block}

                            {* Shop navigation *}
                            {block name='frontend_index_shop_navigation'}
                                {include file="frontend/index/shop-navigation.tpl"}
                            {/block}

                            {block name='frontend_index_container_ajax_cart'}
                                <div class="container--ajax-cart" data-collapse-cart="true"{if $theme.offcanvasCart} data-displayMode="offcanvas"{/if}></div>
                            {/block}
                        </div>
                    {/block}
                </header>

                {* Maincategories navigation top *}
                {block name='frontend_index_navigation_categories_top'}
                    <nav class="navigation-main">
                        <div class="container" data-menu-scroller="true" data-listSelector=".navigation--list.container" data-viewPortSelector=".navigation--list-wrapper">
                            {block name="frontend_index_navigation_categories_top_include"}
                                {include file='frontend/index/main-navigation.tpl'}
                            {/block}
                        </div>
                    </nav>
                {/block}
            {/block}

            {block name='frontend_index_emotion_loading_overlay'}
                {if $hasEmotion}
                    <div class="emotion--overlay">
                        <i class="emotion--loading-indicator"></i>
                    </div>
                {/if}
            {/block}

            {block name='frontend_index_content_main'}
                <section class="{block name="frontend_index_content_main_classes"}content-main container block-group{/block}">

                    {* Breadcrumb *}
                    {block name='frontend_index_breadcrumb'}
                        {if count($sBreadcrumb)}
                            <nav class="content--breadcrumb block">
                                {block name='frontend_index_breadcrumb_inner'}
                                    {include file='frontend/index/breadcrumb.tpl'}
                                {/block}
                            </nav>
                        {/if}
                    {/block}

                    {* Content top container *}
                    {block name="frontend_index_content_top"}{/block}

                    <div class="content-main--inner">
                        {* Sidebar left *}
                        {block name='frontend_index_content_left'}
                            {include file='frontend/index/sidebar.tpl'}
                        {/block}

                        {* Main content *}
                        {block name='frontend_index_content_wrapper'}
                            <div class="content--wrapper">
                                {block name='frontend_index_content'}{/block}
                            </div>
                        {/block}

                        {* Sidebar right *}
                        {block name='frontend_index_content_right'}{/block}

                        {* Last seen products *}
                        {block name='frontend_index_left_last_articles'}
                            {if $sLastArticlesShow && !$isEmotionLandingPage}
                                {* Last seen products *}
                                <div class="last-seen-products is--hidden" data-last-seen-products="true" data-productLimit="{config name='LastArticles::lastarticlestoshow'}">
                                    <div class="last-seen-products--title">
                                        {s namespace="frontend/plugins/index/viewlast" name='WidgetsRecentlyViewedHeadline'}{/s}
                                    </div>
                                    <div class="last-seen-products--slider product-slider" data-product-slider="true">
                                        <div class="last-seen-products--container product-slider--container"></div>
                                    </div>
                                </div>
                            {/if}
                        {/block}
                    </div>
                </section>
            {/block}

            {* Footer *}
            {block name="frontend_index_footer"}
                <footer class="footer-main">
                    <div class="container">
                        {block name="frontend_index_footer_container"}
                            {include file='frontend/index/footer.tpl'}
                        {/block}
                    </div>
                </footer>
            {/block}

            {block name='frontend_index_body_inline'}{/block}
        </div>
    {/block}

    {* If required add the cookiePermission hint *}
    {block name='frontend_index_cookie_permission'}
        {if {config name="show_cookie_note"}}
            {include file="frontend/_includes/cookie_permission_note.tpl"}
        {/if}
    {/block}

    {block name="frontend_index_header_javascript"}
        {$controllerData = [
            'vat_check_enabled' => {config name='vatcheckendabled'},
            'vat_check_required' => {config name='vatcheckrequired'},
            'register' => {url controller="register"},
            'checkout' => {url controller="checkout"},
            'ajax_search' => {url controller="ajax_search" _seo=false},
            'ajax_cart' => {url controller='checkout' action='ajaxCart' _seo=false},
            'ajax_validate' => {url controller="register" _seo=false},
            'ajax_add_article' => {url controller="checkout" action="addArticle" _seo=false},
            'ajax_listing' => {url module="widgets" controller="listing" action="listingCount" _seo=false},
            'ajax_cart_refresh' => {url controller="checkout" action="ajaxAmount" _seo=false},
            'ajax_address_selection' => {url controller="address" action="ajaxSelection" fullPath _seo=false},
            'ajax_address_editor' => {url controller="address" action="ajaxEditor" fullPath _seo=false}
        ]}

        {$themeConfig = [
            'offcanvasOverlayPage' => $theme.offcanvasOverlayPage
        ]}

        {$lastSeenProductsKeys = []}
        {foreach $sLastArticlesConfig as $key => $value}
            {$lastSeenProductsKeys[$key] = $value}
        {/foreach}

        {$lastSeenProductsConfig = [
            'baseUrl' => $Shop->getBaseUrl(),
            'shopId' => $Shop->getId(),
            'noPicture' => {link file="frontend/_public/src/img/no-picture.jpg"},
            'productLimit' => {"{config name=lastarticlestoshow}"|floor},
            'currentArticle' => ""
        ]}

        {if $sArticle}
            {$lastSeenProductsConfig.currentArticle = $sLastArticlesConfig}
            {$lastSeenProductsConfig.currentArticle.articleId = $sArticle.articleID}
            {$lastSeenProductsConfig.currentArticle.linkDetailsRewritten = $sArticle.linkDetailsRewrited}
            {$lastSeenProductsConfig.currentArticle.articleName = $sArticle.articleName}
            {if $sArticle.additionaltext}
                {$lastSeenProductsConfig.currentArticle.articleName = $lastSeenProductsConfig.currentArticle.articleName|cat:' ':$sArticle.additionaltext}
            {/if}
            {$lastSeenProductsConfig.currentArticle.imageTitle = $sArticle.image.description}
            {$lastSeenProductsConfig.currentArticle.images = []}

            {foreach $sArticle.image.thumbnails as $key => $image}
                {$lastSeenProductsConfig.currentArticle.images[$key] = [
                    'source' => $image.source,
                    'retinaSource' => $image.retinaSource,
                    'sourceSet' => $image.sourceSet
                ]}
            {/foreach}
        {/if}

        {$csrfConfig = [
            'generateUrl' => {url controller="csrftoken" fullPath=false},
            'basePath' => $Shop->getBasePath(),
            'shopId' => $Shop->getId()
        ]}

        {if {config name="shareSessionBetweenLanguageShops"} && $Shop->getMain()}
            {$csrfConfig['shopId'] = $Shop->getMain()->getId()}
        {/if}

        {* let the user modify the data here *}
        {block name="frontend_index_header_javascript_data"}{/block}

        <script id="footer--js-inline">
            {block name="frontend_index_header_javascript_inline"}
                var timeNow = {time() nocache};
                var secureShop = {if $Shop->getSecure() eq 1}true{else}false{/if};

                var asyncCallbacks = [];

                document.asyncReady = function (callback) {
                    asyncCallbacks.push(callback);
                };
                var controller = controller || {$controllerData|json_encode};
                var snippets = snippets || { "noCookiesNotice": {s json="true" name='IndexNoCookiesNotice'}{/s} };
                var themeConfig = themeConfig || {$themeConfig|json_encode};
                var lastSeenProductsConfig = lastSeenProductsConfig || {$lastSeenProductsConfig|json_encode};
                var csrfConfig = csrfConfig || {$csrfConfig|json_encode};
                var statisticDevices = [
                    { device: 'mobile', enter: 0, exit: 767 },
                    { device: 'tablet', enter: 768, exit: 1259 },
                    { device: 'desktop', enter: 1260, exit: 5160 }
                ];
                var cookieRemoval = cookieRemoval || {config name="cookie_note_mode"};

            {/block}
        </script>

        {include file="frontend/index/datepicker-config.tpl"}

        {if $theme.additionalJsLibraries}
            {$theme.additionalJsLibraries}
        {/if}
    {/block}

    {block name="frontend_index_header_javascript_jquery"}
        {* Add the partner statistics widget, if configured *}
        {if !{config name=disableShopwareStatistics} }
            {include file='widgets/index/statistic_include.tpl'}
        {/if}
    {/block}

    {* Include jQuery and all other javascript files at the bottom of the page *}
    {block name="frontend_index_header_javascript_jquery_lib"}
        {compileJavascript timestamp={themeTimestamp} output="javascriptFiles"}
        {foreach $javascriptFiles as $file}
            {block name="frontend_index_header_javascript_jquery_lib_file"}
                <script{if $theme.asyncJavascriptLoading} async{/if} src="{preload file=$file as="script"}" id="main-script"></script>
            {/block}
        {/foreach}
    {/block}

{block name="frontend_index_javascript_async_ready"}
    {include file="frontend/index/script-async-ready.tpl"}
{/block}

</body>
</html>
