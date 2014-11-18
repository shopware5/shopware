{extends file='frontend/index/index.tpl'}

{* Custom header *}
{block name='frontend_index_header'}
    {include file="frontend/detail/header.tpl"}
{/block}

{* Modify the breadcrumb *}
{block name='frontend_index_breadcrumb_inner' prepend}
    {block name="frontend_detail_breadcrumb_overview"}
        {if !{config name=disableArticleNavigation}}
            {$breadCrumbBackLink = $sBreadcrumb[count($sBreadcrumb) - 1]['link']}
            <a class="btn is--icon-left breadcrumb--button breadcrumb--link is--large" href="{if $breadCrumbBackLink}{$breadCrumbBackLink}{else}#{/if}" title="{s name="DetailNavIndex"}{/s}">
                <i class="icon--arrow-left"></i> {s name='DetailNavIndex' namespace="frontend/detail/navigation"}{/s}
            </a>
        {/if}
    {/block}
{/block}

{block name="frontend_index_content_top" append}
    {* Product navigation - Previous and next arrow button *}
    {block name="frontend_detail_index_navigation"}
        {if !{config name=disableArticleNavigation}}
            <nav class="product--navigation">
                {include file="frontend/detail/navigation.tpl"}
            </nav>
        {/if}
    {/block}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="content product--details" itemscope itemtype="http://schema.org/Product"{if !{config name=disableArticleNavigation}} data-product-navigation="{url module="widgets" controller="listing" action="productNavigation" fullPath}" data-category-id="{$sArticle.categoryID}" data-main-ordernumber="{$sArticle.mainVariantNumber}"{/if}>

        {* The configurator selection is checked at this early point
           to use it in different included files in the detail template. *}
        {block name='frontend_detail_index_configurator_settings'}
    
            {* Variable for tracking active user variant selection *}
            {$activeConfiguratorSelection = true}
    
            {if $sArticle.sConfigurator && ($sArticle.sConfiguratorSettings.type == 1 || $sArticle.sConfiguratorSettings.type == 2)}
                {* If user has no selection in this group set it to false *}
                {foreach $sArticle.sConfigurator as $configuratorGroup}
                    {if !$configuratorGroup.selected_value}
                        {$activeConfiguratorSelection = false}
                    {/if}
                {/foreach}
            {/if}
        {/block}
    
        {* Product header *}
        {block name='frontend_detail_index_header'}
            <header class="product--header block-group">
                <div class="product--info block">
    
                    {* Product name *}
                    {block name='frontend_detail_index_name'}
                        <h1 class="product--title" itemprop="name">
                            {$sArticle.articleName}
                        </h1>
                    {/block}
    
                    {* Product - Supplier information *}
                    {block name='frontend_detai_supplier_info'}
                        {if $sArticle.supplierImg}
                            <div class="product--supplier block">
                                <a href="{url controller='supplier' sSupplier=$sArticle.supplierID}"
                                   title="{"{s name="DetailDescriptionLinkInformation" namespace="frontend/detail/description"}{/s}"|escape}"
                                   class="product--supplier-link">
                                    <img src="{$sArticle.supplierImg}" alt="{$sArticle.supplierName|escape}">
                                </a>
                            </div>
                        {/if}
                    {/block}
    
                    {* Product rating *}
                    {block name="frontend_detail_comments_overview"}
                        {if !{config name=VoteDisable}}
                            <div class="product--rating-container">
                                <a href="#product--publish-comment" class="product--rating-link" rel="nofollow" title="{"{s name='DetailLinkReview'}{/s}"|escape}">
                                    {include file='frontend/_includes/rating.tpl' points=$sArticle.sVoteAverange.averange type="aggregated" count=$sArticle.sVoteAverange.count}
                                </a>
                            </div>
                        {/if}
                    {/block}
    
                </div>
            </header>
        {/block}
    
        <div class="product--detail-upper block-group">
            {* Product image *}
            {block name='frontend_detail_index_image_container'}
                <div class="product--image-container image-slider{if $sArticle.image && {config name=sUSEZOOMPLUS}} product--image-zoom{/if}"
                     data-image-slider="true"
                     data-thumbnails=".image--thumbnails">
                    {include file="frontend/detail/image.tpl"}
                </div>
            {/block}
    
            {* "Buy now" box container *}
            {block name='frontend_detail_index_buy_container'}
                <div class="product--buybox block{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2} is--wide{/if}">
    
                    {block name="frontend_detail_rich_snippets_brand"}
                        <meta itemprop="brand" content="{$sArticle.supplierName|escape}"/>
                    {/block}
    
                    {block name="frontend_detail_rich_snippets_weight"}
                        {if $sArticle.weight}
                            <meta itemprop="weight" content="{$sArticle.weight} kg"/>
                        {/if}
                    {/block}
    
                    {block name="frontend_detail_rich_snippets_height"}
                        {if $sArticle.height}
                            <meta itemprop="height" content="{$sArticle.height} cm"/>
                        {/if}
                    {/block}
    
                    {block name="frontend_detail_rich_snippets_width"}
                        {if $sArticle.width}
                            <meta itemprop="width" content="{$sArticle.width} cm"/>
                        {/if}
                    {/block}
    
                    {block name="frontend_detail_rich_snippets_depth"}
                        {if $sArticle.length}
                            <meta itemprop="depth" content="{$sArticle.length} cm"/>
                        {/if}
                    {/block}
    
                    {block name="frontend_detail_rich_snippets_release_date"}
                        {if $sArticle.sReleasedate}
                            <meta itemprop="releaseDate" content="{$sArticle.sReleasedate}"/>
                        {/if}
                    {/block}
    
                    {* Product eMail notification *}
                    {block name="frontend_detail_index_notification"}
                        {if $sArticle.notification && $sArticle.instock <= 0 && $ShowNotification}
                            {include file="frontend/plugins/notification/index.tpl"}
                        {/if}
                    {/block}
    
                    {* Product data *}
                    {block name='frontend_detail_index_buy_container_inner'}
                        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="buybox--inner">
    
                            {block name='frontend_detail_index_data'}
                                <meta itemprop="priceCurrency" content="{$Shop->getCurrency()->getCurrency()}"/>
                                {include file="frontend/detail/data.tpl" sArticle=$sArticle sView=1}
                            {/block}
    
                            {block name='frontend_detail_index_after_data'}{/block}
    
                            {* Configurator drop down menu's *}
                            {block name="frontend_detail_index_configurator"}
                                {if $sArticle.sConfigurator}
                                    {if $sArticle.sConfiguratorSettings.type == 1}
                                        {include file="frontend/detail/config_step.tpl"}
                                    {elseif $sArticle.sConfiguratorSettings.type == 2}
                                        {include file="frontend/detail/config_variant.tpl"}
                                    {else}
                                        {include file="frontend/detail/config_upprice.tpl"}
                                    {/if}
                                {/if}
                            {/block}
    
                            {* Include buy button and quantity box *}
                            {block name="frontend_detail_index_buybox"}
                                {include file="frontend/detail/buy.tpl"}
                            {/block}
    
                            {* Product actions *}
                            {block name="frontend_detail_index_actions"}
                                <nav class="product--actions">
                                    {include file="frontend/detail/actions.tpl"}
                                </nav>
                            {/block}
                        </div>
                    {/block}
    
                    {* Product - Base information *}
                    {block name='frontend_detail_index_buy_container_base_info'}
                        <ul class="product--base-info list--unstyled">
    
                            {* Product SKU *}
                            {block name='frontend_detail_data_ordernumber'}
                                <li class="base-info--entry entry--sku">
    
                                    {* Product SKU - Label *}
                                    {block name='frontend_detail_data_ordernumber_label'}
                                        <strong class="entry--label">
                                            {s name="DetailDataId" namespace="frontend/detail/data"}{/s}
                                        </strong>
                                    {/block}
    
                                    {* Product SKU - Content *}
                                    {block name='frontend_detail_data_ordernumber_content'}
                                        <meta itemprop="productID" content="{$sArticle.articleDetailsID}"/>
                                        <span class="entry--content" itemprop="sku">
                                            {$sArticle.ordernumber}
                                        </span>
                                    {/block}
                                </li>
                            {/block}
    
                            {* Product attributes fields *}
                            {block name='frontend_detail_data_attributes'}
    
                                {* Product attribute 1 *}
                                {block name='frontend_detail_data_attributes_attr1'}
                                    {if $sArticle.attr1}
                                        <li class="base-info--entry entry-attribute">
                                            <strong class="entry--label">
                                                {s name="DetailAttributeField1Label"}Freitextfeld 1{/s}:
                                            </strong>
    
                                            <span class="entry--content">
                                                {$sArticle.attr1}
                                            </span>
                                        </li>
                                    {/if}
                                {/block}
    
                                {* Product attribute 2 *}
                                {block name='frontend_detail_data_attributes_attr2'}
                                    {if $sArticle.attr2}
                                        <li class="base-info--entry entry-attribute">
                                            <strong class="entry--label">
                                                {s name="DetailAttributeField2Label"}Freitextfeld 2{/s}:
                                            </strong>
    
                                            <span class="entry--content">
                                                {$sArticle.attr2}
                                            </span>
                                        </li>
                                    {/if}
                                {/block}
                            {/block}
                        </ul>
                    {/block}
                </div>
            {/block}
        </div>
    
        {* Product bundle hook point *}
        {block name="frontend_detail_index_bundle"}{/block}
    
        {block name="frontend_detail_index_detail"}
    
            {* Tab navigation *}
            {block name="frontend_detail_index_tabs"}
                <div class="additional-info--tabs" data-tab-content="true">
                    {include file="frontend/detail/tabs.tpl"}
    
                    {* Tab content *}
                    {block name="frontend_detail_index_outer_tabs"}
                        <div class="tabs--content-container tab--content panel--body has--border is--rounded">
                            {block name="frontend_detail_index_inner_tabs"}
                                {block name='frontend_detail_index_before_tabs'}{/block}
    
                                {* Product description *}
                                {block name="frontend_detail_index_tabs_description"}
                                    {include file="frontend/detail/tabs/description.tpl"}
                                {/block}
    
                                {* Article rating *}
                                {block name="frontend_detail_index_tabs_rating"}
                                    {if !{config name=VoteDisable}}
                                        {include file="frontend/detail/tabs/comment.tpl"}
                                    {/if}
                                {/block}
    
                                {block name='frontend_detail_index_after_tabs'}{/block}
                            {/block}
                        </div>
                    {/block}
                </div>
            {/block}
        {/block}
    
        {* Related and similar products tab panel *}
        {block name="frontend_detail_index_related_similiar_tabs"}
            {if ($sArticle.sRelatedArticles && !$sArticle.crossbundlelook) || $sArticle.sSimilarArticles}
                <div class="related-slider--tabs" data-tab-content="true">
    
                    {block name="frontend_detail_index_related_similiar_tabs_navigation"}
                        <ul class="tab--navigation panel--tab-nav">
    
                            {* Tab navigation - Related products *}
                            {block name="frontend_detail_tabs_entry_related"}
                                {if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
                                    <li class="navigation--entry">
                                        <a href="#content--related-products" class="navigation--link">
                                            {s namespace="frontend/detail/tabs" name='DetailTabsAccessories'}Zubehör{/s}
                                            ({$sArticle.sRelatedArticles|@count})
                                        </a>
                                    </li>
                                {/if}
                            {/block}
    
                            {* Similar products *}
                            {block name="frontend_detail_index_recommendation_tabs_entry_similar_products"}
                                {if $sArticle.sSimilarArticles}
                                    <li class="navigation--entry entry--similar-products">
                                        <a href="#content--similar-products" class="navigation--link">
                                            {s name="DetailRecommendationSimilarLabel"}Ähnliche Artikel{/s}
                                        </a>
                                    </li>
                                {/if}
                            {/block}
                        </ul>
                    {/block}
    
                    {block name="frontend_detail_index_related_similiar_tab_content_container"}
                        <div class="tab--content panel--body has--border is--rounded">
    
                            {* Related articles *}
                            {block name="frontend_detail_index_tabs_related"}
                                <div class="content--related-products">
                                    {include file="frontend/detail/tabs/related.tpl"}
                                </div>
                            {/block}
    
                            {* Similar products slider *}
                            {block name="frontend_detail_index_similar_slider"}
                                <div class="content--similar-products">
                                    {include file='frontend/detail/similar.tpl'}
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}
    
        {* Recommendation tab panel *}
        {block name="frontend_detail_index_recommendation_tabs"}
            {if {config name=alsoBoughtShow} || {config name=similarViewedShow}}
                <div class="recommendation-slider--tabs" data-tab-content="true">
    
                    {* Tab navigation *}
                    {block name="frontend_detail_index_recommendation_tabs_navigation"}
                        <ul class="tab--navigation panel--tab-nav">
    
                            {* Customer also bought *}
                            {block name="frontend_detail_index_recommendation_tabs_entry_also_bought"}
                                {if {config name=alsoBoughtShow}}
                                    <li class="navigation--entry entry--also-bought">
                                        <a class="navigation--link" href="#content--also-bought">
                                            {s name="DetailRecommendationAlsoBoughtLabel"}Kunden kauften auch{/s}
                                        </a>
                                    </li>
                                {/if}
                            {/block}
    
                            {* Customer also viewed *}
                            {block name="frontend_detail_index_recommendation_tabs_entry_also_viewed"}
                                {if {config name=similarViewedShow}}
                                    <li class="navigation--entry entry--customer-viewed">
                                        <a class="navigation--link" href="#content--customer-viewed">
                                            {s name="DetailRecommendationAlsoViewedLabel"}Kunden haben sich ebenfalls angesehen{/s}
                                        </a>
                                    </li>
                                {/if}
                            {/block}
                        </ul>
                    {/block}
    
                    {* Tab content container *}
                    {block name="frontend_detail_index_recommendation_tab_content_container"}
                        <div class="tab--content panel--body has--border is--rounded">
    
                            {* "Customers bought also" slider *}
                            {block name="frontend_detail_index_also_bought_slider"}
                                {if {config name=alsoBoughtShow}}
                                    <div class="content--also-bought">
                                        {action module=widgets controller=recommendation action=bought articleId=$sArticle.articleID}
                                    </div>
                                {/if}
                            {/block}
    
                            {* "Customers similar viewed" slider *}
                            {block name="frontend_detail_index_similar_viewed_slider"}
                                {if {config name=similarViewedShow}}
                                    <div class="content--customer-viewed">
                                        {action module=widgets controller=recommendation action=viewed articleId=$sArticle.articleID}
                                    </div>
                                {/if}
                            {/block}
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}
    </div>
{/block}