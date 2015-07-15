
{extends file='parent:frontend/listing/box_article.tpl'}
{* Disable the "buy now" button *}
{block name='frontend_listing_box_article_actions_buy_now'}{/block}

{* Article picture *}
{block name='frontend_listing_box_article_picture'}


	{* 3 spalter bilder *}
	{if $sTemplate eq 'listing-3col'}
		{* 1/3 *}
		{if $colWidth eq 3}
			{assign var=image value=$sArticle.image.src.4}
		{* 2/3 *}
		{elseif $colWidth eq 2}
			{assign var=image value=$sArticle.image.src.4}
		{* 3/3 *}
		{else}
			{assign var=image value=$sArticle.image.src.3}
		{/if}

	{* 4 spalter *}
	{else}
		{* 1/4 *}
		{if $colWidth eq 4}
			{assign var=image value=$sArticle.image.src.4}
		{* 2/4 *}
		{elseif $colWidth eq 3}
			{assign var=image value=$sArticle.image.src.4}
		{* 3/4 *}
		{elseif $colWidth eq 2}
			{assign var=image value=$sArticle.image.src.4}
		{* 4/4 *}
		{else}
			{assign var=image value=$sArticle.image.src.3}
		{/if}
	{/if}

	<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="artbox_thumb" {if isset($sArticle.image.src)}
		style="background: url({$image}) no-repeat center center"{/if}>
	{if !isset($sArticle.image.src)}<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />{/if}</a>
{/block}

{* Increase the size of the description text *}
{block name='frontend_listing_box_article_description'}

	{* emotions auf der starteite *}
	{if $Controller == 'index'}
		{* 3 spalter *}
		{if $sTemplate eq 'listing-3col'}
			{if $colWidth eq 3}
				{assign var=size value=1250}
			{elseif $colWidth eq 2}
				{assign var=size value=700}
			{else}
				{assign var=size value=200}
			{/if}

		{* 4 spalter *}
		{else}
			{if $colWidth eq 4}
				{assign var=size value=1350}
			{elseif $colWidth eq 3}
				{assign var=size value=800}
			{elseif $colWidth eq 2}
				{assign var=size value=0}
			{else}
				{assign var=size value=165}
			{/if}
		{/if}

		{* emotions im listing *}
		{else}

		{* 3 spalter *}
		{if $sTemplate eq 'listing-3col'}
			{if $colWidth eq 3}
				{assign var=size value=850}
			{elseif $colWidth eq 2}
				{assign var=size value=350}
			{else}
				{assign var=size value=180}
			{/if}

		{* 4 spalter *}
		{else}
			{if $colWidth eq 4}
				{assign var=size value=850}
			{elseif $colWidth eq 3}
				{assign var=size value=500}
			{elseif $colWidth eq 2}
				{assign var=size value=0}
			{else}
				{assign var=size value=120}
			{/if}
		{/if}
	{/if}

	<p class="desc">
	    {$Data.description_long|strip_tags|truncate:$size}
	</p>

{/block}

{* Unit price *}
{block name='frontend_listing_box_article_unit'}
{if $sArticle.purchaseunit}
    <div class="{if !$sArticle.has_pseudoprice}article_price_unit{else}article_price_unit_pseudo{/if}">
        {if $sArticle.purchaseunit && $sArticle.purchaseunit != 0}
            <p>
            	<span class="purchaseunit">
                	<strong>{se name="ListingBoxArticleContent" namespace="frontend/listing/box_article"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                </span>
        {/if}
        {if $sArticle.purchaseunit != $sArticle.referenceunit}
                {if $sArticle.referenceunit}
                	<span class="referenceunit">
                     ({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sArticle.referenceunit} {$sArticle.sUnit.description})
                    </span>
                {/if}
            </p>
        {/if}
    </div>
{/if}
{/block}
{block name='frontend_listing_box_article_actions_compare'}{/block}
