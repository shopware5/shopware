{extends file='parent:frontend/compare/col.tpl'}

{block name='frontend_compare_article_name'}
		<h3><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName|truncate:47}</a></h3>
	
		{* More informations button *}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="button-right small_right">{s name='ListingBoxLinkDetails' namespace="frontend/listing/box_article"}{/s}</a>
{/block}


{block name='frontend_compare_properties'}
	<div class="property" style="background-color:#fff;">
		{if $property.value}{$property.value}{else}-{/if}
	</div>
{/block}
