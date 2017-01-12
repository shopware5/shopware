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
    {if $sTarget} is--target-{$sTarget|escapeHtml}{/if}
    {if $theme.checkoutHeader && (({controllerName|lower} == "checkout" && {controllerAction|lower} != "cart") || ({controllerName|lower} == "register" && $sTarget != "account"))} is--minimal-header{/if}
    {if !$theme.displaySidebar} is--no-sidebar{/if}
    {/strip}{/block}">

    {block name='frontend_index_after_body'}{/block}

    {block name="frontend_index_page_wrap"}
        <div class="page-wrap">

            {* Message if javascript is disabled *}
            {block name="frontend_index_no_script_message"}
                <noscript class="noscript-main">
                    {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="IndexNoscriptNotice"}{/s}" borderRadius=false}
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
                {if $hasEmotion && !$hasEscapedFragment}
                    <div class="emotion--overlay">
                        <i class="emotion--loading-indicator"></i>
                    </div>
                {/if}
            {/block}

            {block name='frontend_index_content_main'}
                <section class="content-main container block-group">

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
                                <div class="last-seen-products is--hidden" data-last-seen-products="true">
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

{block name="frontend_index_header_javascript"}
    <script type="text/javascript" id="footer--js-inline">
        //<![CDATA[
        {block name="frontend_index_header_javascript_inline"}
            var timeNow = {time() nocache};

            var controller = controller || {ldelim}
                'vat_check_enabled': '{config name='vatcheckendabled'}',
                'vat_check_required': '{config name='vatcheckrequired'}',
                'ajax_cart': '{url controller='checkout' action='ajaxCart'}',
                'ajax_search': '{url controller="ajax_search"}',
                'register': '{url controller="register"}',
                'checkout': '{url controller="checkout"}',
                'ajax_validate': '{url controller="register"}',
                'ajax_add_article': '{url controller="checkout" action="addArticle"}',
                'ajax_listing': '{url module="widgets" controller="Listing" action="ajaxListing"}',
                'ajax_cart_refresh': '{url controller="checkout" action="ajaxAmount"}',
                'ajax_address_selection': '{url controller="address" action="ajaxSelection" fullPath forceSecure}',
                'ajax_address_editor': '{url controller="address" action="ajaxEditor" fullPath forceSecure}'
            {rdelim};

            var snippets = snippets || {ldelim}
                'noCookiesNotice': '{"{s name='IndexNoCookiesNotice'}{/s}"|escape}'
            {rdelim};

            var themeConfig = themeConfig || {ldelim}
                'offcanvasOverlayPage': '{$theme.offcanvasOverlayPage}'
            {rdelim};

            var lastSeenProductsConfig = lastSeenProductsConfig || {ldelim}
                'baseUrl': '{$Shop->getBaseUrl()}',
                'shopId': '{$Shop->getId()}',
                'noPicture': '{link file="frontend/_public/src/img/no-picture.jpg"}',
                'productLimit': ~~('{config name="lastarticlestoshow"}'),
                'currentArticle': {ldelim}{if $sArticle}
                    {foreach $sLastArticlesConfig as $key => $value}
                        '{$key}': '{$value}',
                    {/foreach}
                    'articleId': ~~('{$sArticle.articleID}'),
                    'linkDetailsRewritten': '{$sArticle.linkDetailsRewrited}',
                    'articleName': '{$sArticle.articleName|escape:"javascript"}{if $sArticle.additionaltext} {$sArticle.additionaltext|escape:"javascript"}{/if}',
                    'imageTitle': '{$sArticle.image.description|escape:"javascript"}',
                    'images': {ldelim}
                        {foreach $sArticle.image.thumbnails as $key => $image}
                            '{$key}': {ldelim}
                                'source': '{$image.source}',
                                'retinaSource': '{$image.retinaSource}',
                                'sourceSet': '{$image.sourceSet}'
                            {rdelim},
                        {/foreach}
                    {rdelim}
                {/if}{rdelim}
            {rdelim};

            var csrfConfig = csrfConfig || {ldelim}
                'generateUrl': '{url controller="csrftoken" fullPath=false}',
                'basePath': '{$Shop->getBasePath()}',
                'shopId': '{$Shop->getId()}'
            {rdelim};
        {/block}
        //]]>
    </script>

    {if $theme.additionalJsLibraries}
        {$theme.additionalJsLibraries}
    {/if}
{/block}

{* Include jQuery and all other javascript files at the bottom of the page *}
{block name="frontend_index_header_javascript_jquery_lib"}
    {compileJavascript timestamp={themeTimestamp} output="javascriptFiles"}
    {foreach $javascriptFiles as $file}
        <script src="{$file}"></script>
    {/foreach}
{/block}

{block name="frontend_index_header_javascript_jquery"}
    {* Add the partner statistics widget, if configured *}
    {if !{config name=disableShopwareStatistics} }
        {include file='widgets/index/statistic_include.tpl'}
    {/if}
{/block}
</body>
</html>
