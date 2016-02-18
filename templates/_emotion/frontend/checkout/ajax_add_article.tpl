<div class="heading">
	<h2>{if !$sBasketInfo}{s name="AjaxAddHeader"}{/s}{else}{s name='AjaxAddHeaderError'}Hinweis:{/s}{/if}</h2>

	{* Close button *}
	<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
		{s name='LoginActionClose'}{/s}
	</a>
</div>

{if $sBasketInfo}
<div class="error_container">
	<p class="text">
		{$sBasketInfo}
	</p>
	<div class="clear">&nbsp;</div>
</div>
{/if}

<div class="ajax_add_article">
    {block name='frontend_checkout_ajax_add_article_middle'}
	<div class="middle">
		{if $sArticle}
		<div class="article_box">

			{* Thumbnail *}
			<div class="thumbnail">
				<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="artbox_thumb" {if $sArticle.image.src}
					style="background: url({$sArticle.image.src.1}) no-repeat center center"{/if}>
					{if !$sArticle.image.src}<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />{/if}
				</a>
			</div>

			{* Title *}
			<strong class="title">{$sArticleName|truncate:37|strip_tags}</strong>

			{* Ordernumber *}
			<span class="ordernumber">{s name="AjaxAddLabelOrdernumber"}{/s}: {$sArticle.ordernumber}</span>

			{* Price *}
			<strong class="price">{$sArticle.price|currency}</strong>

			{* Quantity *}
			<span class="quantity">{s name="AjaxAddLabelQuantity"}{/s}: {$sArticle.quantity}</span>
		</div>
		{/if}

		{* Actions *}
		<div class="actions">
			{block name='frontend_checkout_ajax_add_article_action_buttons'}
				<a title="{s name='AjaxAddLinkBack'}{/s}" class="button-middle large modal_close">
					{se name="AjaxAddLinkBack"}{/se}
				</a>
				<a href="{url action='cart'}" class="button-right large right" title="{s name='AjaxAddLinkCart'}{/s}">
					{se name="AjaxAddLinkCart"}{/se}
				</a>
				<div class="clear">&nbsp;</div>
			{/block}
		</div>
		<div class="space">&nbsp;</div>
	</div>
    {/block}

	<div class="bottom">
		{block name='frontend_checkout_ajax_add_article_cross_selling'}
			{if $sCrossSimilarShown|@count || $sCrossBoughtToo|@count}
				<h2>{se name="AjaxAddHeaderCrossSelling"}{/se}</h2>
				<div class="slider_modal">
			        {$sCrossSellingArticles = $sCrossBoughtToo}
			        {if $sCrossSimilarShown && $sCrossBoughtToo|count < 1}
			            {$sCrossSellingArticles = $sCrossSimilarShown}
			        {/if}

					{* @TODO - Use the new syntax *}
			        {foreach from=$sCrossSellingArticles item=article}
			            {if $article@index % 3 == 0}
			                <div class="slide">
			            {/if}

						{* @TODO - Use the new syntax *}
			            {assign var=image value=$article.image.src.2}
			            <div class="article_box">
			            {if $image}
			                <a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName|escape}" href="{$article.linkDetails}">
			            </a>
			            {else}
			            <a class="artbox_thumb no_picture" title="{$article.articleName|escape}" href="{$article.linkDetails}">
			            </a>
			            {/if}
			            <a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
			            {if $article.purchaseunit}
			                <div class="article_price_unit">
			                    <p>
			                        <strong>{se name="SlideArticleInfoContent" namespace="frontend/plugins/recommendation/slide_articles"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
			                        {if $article.referenceunit}
			                           ({$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$article.referenceunit} {$article.sUnit.description})
			                        {/if}
			                    </p>
			                </div>
			            {/if}

			            <p class="price">
			                <span class="price{if $article.has_pseudoprice} pseudo{/if}">
			                	{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
								{if $article.has_pseudoprice}
									<em>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$article.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</em>
								{/if}
			                </span>
			            </p>
			            </div>

			            {if $article@index % 3 == 2 || $article@last}
			                </div>
			            {/if}
			        {/foreach}
				</div>
			{/if}
		{/block}
	</div>
</div>
