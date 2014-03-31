<li class="product--box panel{if $lastitem} is--last{/if}{if $firstitem} is--first{/if}">

	<div class="panel--body has--border">

    {* Product box badges - highlight, newcomer, ESD product and discount *}
    {block name='frontend_listing_box_article_rating'}
        {include file="frontend/listing/product-box/badges.tpl"}
    {/block}

	{* Customer rating for the product *}
	{block name='frontend_listing_box_article_rating'}
		{if $sArticle.sVoteAverange.averange}
			<div class="product--rating star{($sArticle.sVoteAverange.averange * 2)|round:0}"></div>
		{/if}
	{/block}

	{* Product image *}
	{block name='frontend_listing_box_article_picture'}
        {include file="frontend/listing/product-box/image.tpl"}
	{/block}

	{* Product name *}
	{block name='frontend_listing_box_article_name'}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" class="product--title"
		   title="{$sArticle.articleName}">{$sArticle.articleName|truncate:47}</a>
	{/block}

	{* Product description *}
	{block name='frontend_listing_box_article_description'}
        {if $sTemplate eq 'listing-1col'}
            {$size=270}
        {else}
            {$size=60}
        {/if}

        {include file="frontend/listing/product-box/description.tpl" size=$size}
	{/block}

	{* Product price - Unit price *}
	{block name='frontend_listing_box_article_unit'}
        {include file="frontend/listing/product-box/unit-price.tpl"}
	{/block}

	{* Product price - Default and discount price *}
	{block name='frontend_listing_box_article_price'}
        {include file="frontend/listing/product-box/price.tpl"}
	{/block}

	{* Product actions - Compare product, more information, buy now *}
	{block name='frontend_listing_box_article_actions'}
        {include file="frontend/listing/product-box/actions.tpl"}
	{/block}

	</div>
</li>