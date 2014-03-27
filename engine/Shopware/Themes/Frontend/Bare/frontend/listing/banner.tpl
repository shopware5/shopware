{block name="frontend_listing_banner"}
{if $sBanner}
	{if $sBanner.extension=="swf"}
		
		{* @deprecated Flash banner *}
		{block name='frontend_listing_swf_banner'}{/block}
	{elseif $sBanner.img}
	    {if $sBanner.link == "#" || $sBanner.link == ""}
	    	
	    	{* Image only banner *}
	    	{block name='frontend_listing_image_only_banner'}
	    	<div class="banner">
	    		<img src="{link file=$sBanner.img}" alt="{$sBanner.description}" title="{$sBanner.description}" />
	    	</div>
	    	{/block}
	    {else}
	    	
	    	{* Normal banner *}
	    	{block name='frontend_listing_normal_banner'}
	    	<a href="{if $sBanner.link}{$sBanner.link}{else}#{/if}" class="banner" {if $sBanner.link_target}target="{$sBanner.link_target}"{/if} title="{$sBanner.description}">
	    		<img src="{link file=$sBanner.img}" alt="{$sBanner.description}" title="{$sBanner.description}" />
	    	</a>
	    	{/block}
	    {/if}
	{/if}
{/if}
{/block}