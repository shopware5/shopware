
{* Promotion image *}
{block name="frontend_listing_promotion_image"}
<div class="artbox_shoppingworld">
	<a href="{if $sArticle.link}{$sArticle.link}{else}#{/if}" {if $sArticle.linkTarget}target="{$sArticle.linkTarget}"{/if}>
		<img src="{$sArticle.img}" alt="{$sArticle.description}" title="{$sArticle.description}"/>
	</a>
</div>
{/block}
