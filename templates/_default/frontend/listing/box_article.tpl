<div class="artbox{if $lastitem} last{/if}{if $firstitem} first{/if}">
	<div class="inner">

		{* Top *}
		{block name='frontend_listing_box_article_hint'}
			{if $sArticle.highlight}
			<div class="ico_tipp">{se name='ListingBoxTip'}{/se}</div>
			{/if}
		{/block}
		
		{* New *}
		{block name='frontend_listing_box_article_new'}
			{if $sArticle.newArticle}
			<div class="ico_new">{se name='ListingBoxNew'}{/se}</div>
			{/if}
		{/block}
		
		{* ESD article *}
		{block name='frontend_listing_box_article_esd'}
			{if $sArticle.esd}
			<div class="ico_esd">{se name='ListingBoxInstantDownload'}{/se}</div>
			{/if}
		{/block}
		
		{* Article rating *}
        {block name='frontend_listing_box_article_rating'}
        	{if $sArticle.sVoteAverange.averange}
	        <div class="star star{($sArticle.sVoteAverange.averange * 2)|round:0}"></div>
	        {/if}
	    {/block}
        
		{* Article picture *}
		{block name='frontend_listing_box_article_picture'}
		{if $sTemplate eq 'listing-3col' || $sTemplate eq 'listing-2col'}
			{assign var=image value=$sArticle.image.src.3}
		{else}
			{assign var=image value=$sArticle.image.src.2}
		{/if}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="artbox_thumb" {if isset($sArticle.image.src)} 
			style="background: url({$image}) no-repeat center center"{/if}>
		{if !isset($sArticle.image.src)}<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />{/if}</a>
		{/block}
				
		{* Article name *}
		{block name='frontend_listing_box_article_name'}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" class="title" title="{$sArticle.articleName}">{$sArticle.articleName|truncate:47}</a>
		{/block}
		
		{* Description *}
		{block name='frontend_listing_box_article_description'}
		{if $sTemplate eq 'listing-1col'}
			{assign var=size value=270}
		{else}
			{assign var=size value=60}
		{/if}
		<p class="desc">{if $sTemplate != 'listing'}
			{$sArticle.description_long|strip_tags|truncate:$size}
			{/if}
		</p>
		{/block}
		
		{* Unit price *}
		{block name='frontend_listing_box_article_unit'}
        {if $sArticle.purchaseunit}
            <div class="{if !$sArticle.pseudoprice}article_price_unit{else}article_price_unit_pseudo{/if}">
                {if $sArticle.purchaseunit && $sArticle.purchaseunit != 0}
                    <p>
                        <strong>{se name="ListingBoxArticleContent"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                    </p>
                {/if}
                {if $sArticle.purchaseunit != $sArticle.referenceunit}
                    <p>
                        {if $sArticle.referenceunit}
                            <strong class="baseprice">{se name="ListingBoxBaseprice"}{/se}:</strong> {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        {/if}
                    </p>
                {/if}
            </div>
        {/if}
		{/block}    	
		
		{* Article Price *}
		{block name='frontend_listing_box_article_price'}
		<p class="{if $sArticle.pseudoprice}pseudoprice{else}price{/if}">
	        {if $sArticle.pseudoprice}
	        	<span class="pseudo">{s name="reducedPrice"}Statt: {/s}{$sArticle.pseudoprice|currency} {s name="Star"}*{/s}</span>
	        {/if}
	        <span class="price">{if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$sArticle.price|currency} {s name="Star"}*{/s}</span>
        </p>
        {/block}
       	
       	{* Compare and more *}
       	{block name='frontend_listing_box_article_actions'}
       	<div class="actions">
       	
       		{block name='frontend_listing_box_article_actions_buy_now'}
       		{* Buy now button *}
       		{if !$sArticle.priceStartingFrom && !$sArticle.sConfigurator && !$sArticle.variants && !$sArticle.sVariantArticle && !$sArticle.laststock == 1 && !($sArticle.notification == 1 && {config name="deactivatebasketonnotification"} == 1)}
       			<a href="{url controller='checkout' action='addArticle' sAdd=$sArticle.ordernumber}" title="{s name='ListingBoxLinkBuy'}{/s}" class="buynow">{s name='ListingBoxLinkBuy'}{/s}</a>
       		{/if}
       		{/block}
       		
       		{block name='frontend_listing_box_article_actions_inline'}
       			{* More informations button *}
				<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="more">{s name='ListingBoxLinkDetails'}{/s}</a>
       		{/block}
		</div>
		{/block}
	</div>
</div>