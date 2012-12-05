<div class="heading">
	<h2>{if !$sBasketInfo}{s namespace="frontend/checkout/ajax_add_article" name="AjaxAddHeader"}{/s}{else}{s namespace="frontend/checkout/ajax_add_article" name='AjaxAddHeaderError'}Hinweis:{/s}{/if}</h2>

	{* Close button *}
	<a href="#" class="modal_close" title="{s namespace="frontend/checkout/ajax_add_article" name='LoginActionClose'}{/s}">
		{s namespace="frontend/checkout/ajax_add_article" name='LoginActionClose'}{/s}
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
	<div class="middle">
		{if $sArticle}
		<div class="article_box">

			{* Thumbnail *}
			<div class="thumbnail">
				<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="artbox_thumb" {if $sArticle.image.src}
					style="background: url({$sArticle.image.src.1}) no-repeat center center"{/if}>
					{if !$sArticle.image.src}<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s namespace="frontend/checkout/ajax_add_article" name='ListingBoxNoPicture'}{/s}" />{/if}
				</a>
			</div>

			{* Title *}
			<strong class="title">{$sArticleName|truncate:37|strip_tags}</strong>

			{* Ordernumber *}
			<span class="ordernumber">{s namespace="frontend/checkout/ajax_add_article" name="AjaxAddLabelOrdernumber"}{/s}: {$sArticle.ordernumber}</span>

			{* Price *}
			<strong class="price">{$sSum} {s namespace="frontend/bonus_system/points" name="Points"}Punkte{/s}</strong>

			{* Quantity *}
			<span class="quantity">{s namespace="frontend/checkout/ajax_add_article" name="AjaxAddLabelQuantity"}{/s}: {$sQuantity}</span>
		</div>
		{/if}

		{* Actions *}
		<div class="actions">
			{block name='frontend_checkout_ajax_add_article_action_buttons'}
			<a title="{s namespace="frontend/checkout/ajax_add_article" name='AjaxAddLinkBack'}{/s}" class="button-middle large modal_close">
				{se namespace="frontend/checkout/ajax_add_article" name="AjaxAddLinkBack"}{/se}
			</a>
			<a href="{url controller='checkout' action='cart'}" class="button-middle" title="{s namespace="frontend/checkout/ajax_add_article" name='AjaxAddLinkCart'}{/s}">
				{se namespace="frontend/checkout/ajax_add_article" name="AjaxAddLinkCart"}{/se}
			</a>
			<a href="{url controller='checkout' action='confirm'}" class="button-right large right checkout" title="{s namespace="frontend/checkout/ajax_add_article" name='AjaxAddLinkConfirm'}{/s}">
				{se namespace="frontend/checkout/ajax_add_article" name="AjaxAddLinkConfirm"}{/se}
			</a>
			<div class="clear">&nbsp;</div>
			{/block}
		</div>
		<div class="space">&nbsp;</div>
	</div>

	<div class="bottom">
		{block name='frontend_checkout_ajax_add_article_cross_selling'}
		{if $sCrossSimilarShown|@count || $sCrossBoughtToo|@count}
			<h2>{se namespace="frontend/checkout/ajax_add_article" name="AjaxAddHeaderCrossSelling"}{/se}</h2>
			<div class="slider_modal">
				{* Similar articles *}
				{if $sCrossSimilarShown}
					{assign var=count value=0}
					{foreach from=$sCrossSimilarShown item=article}
						{if $count == 0}
							<div class="slide">
						{/if}

						{assign var=image value=$article.image.src.2}
						<div class="article_box">
						<!-- article 1 -->
						{if $image}
						<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}">
						</a>
						{else}
						<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}">
						</a>
						{/if}
						<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
						{if $article.purchaseunit}
				            <div class="article_price_unit">
				                <p>
				                    <strong>{se namespace="frontend/checkout/ajax_add_article" name="SlideArticleInfoContent"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
				                </p>
				                {if $article.purchaseunit != $article.referenceunit}
				                    <p>
				                        {if $article.referenceunit}
				                            <strong class="baseprice">{se namespace="frontend/checkout/ajax_add_article" name="SlideArticleInfoBaseprice"}{/se}:</strong> {$article.referenceunit} {$article.sUnit.description} = {$article.referenceprice|currency} {s namespace="frontend/checkout/ajax_add_article" name="Star" namespace="frontend/listing/box_article"}{/s}
				                        {/if}
				                    </p>
				                {/if}
				            </div>
				        {/if}

						<p class="price">
							<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s namespace="frontend/checkout/ajax_add_article" name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
						</p>
						</div>

						{assign var=count value=$count + 1}
						{if $count == 4}
							</div>
							{assign var=count value=0}
						{/if}
					{/foreach}
				{* Bought too articles *}
				{elseif !$sCrossSimilarShown && $sCrossBoughtToo}
					{assign var=count value=0}
					{foreach from=$sCrossSimilarShown item=article}
						{if $count == 0}
							<div class="slide">
						{/if}

						{assign var=image value=$article.image.src.2}
						<div class="article_box">
						<!-- article 1 -->
						{if $image}
						<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}">
						</a>
						{else}
						<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}">
						</a>
						{/if}
						<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
						{if $article.purchaseunit}
				            <div class="article_price_unit">
				                <p>
				                    <strong>{se namespace="frontend/checkout/ajax_add_article" name="SlideArticleInfoContent"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
				                </p>
				                {if $article.purchaseunit != $article.referenceunit}
				                    <p>
				                        {if $article.referenceunit}
				                            <strong class="baseprice">{se namespace="frontend/checkout/ajax_add_article" name="SlideArticleInfoBaseprice"}{/se}:</strong> {$article.referenceunit} {$article.sUnit.description} = {$article.referenceprice|currency} {s namespace="frontend/checkout/ajax_add_article" name="Star" namespace="frontend/listing/box_article"}{/s}
				                        {/if}
				                    </p>
				                {/if}
				            </div>
				        {/if}
						<p class="price">
							<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s namespace="frontend/checkout/ajax_add_article" name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
						</p>
						</div>

						{assign var=count value=$count + 1}
						{if $count == 4}
							</div>
							{assign var=count value=0}
						{/if}
					{/foreach}
				{/if}
			</div>
		{/if}
		{/block}
	</div>
</div>