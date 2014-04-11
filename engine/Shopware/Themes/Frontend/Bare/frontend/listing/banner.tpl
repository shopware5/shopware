{block name="frontend_listing_banner"}
{if $sBanner}

	<div class="banner--container">

	{if $sBanner.extension=="swf"}
		
		{* @deprecated Flash banner *}
		{block name='frontend_listing_swf_banner'}{/block}
	{elseif $sBanner.img}
	    {if $sBanner.link == "#" || $sBanner.link == ""}
	    	
	    	{* Image only banner *}
	    	{block name='frontend_listing_image_only_banner'}
	    		<img class="banner--img" src="{link file=$sBanner.img}" alt="{$sBanner.description}" title="{$sBanner.description}" />
	    	{/block}
	    {else}
	    	
	    	{* Normal banner *}
	    	{block name='frontend_listing_normal_banner'}
                <a href="{if $sBanner.link}{$sBanner.link}{else}#{/if}" class="banner--link" {if $sBanner.link_target}target="{$sBanner.link_target}"{/if} title="{$sBanner.description}">
                    <img class="banner--img" src="{link file=$sBanner.img}" alt="{$sBanner.description}" title="{$sBanner.description}" />
                </a>
	    	{/block}
	    {/if}
	{/if}

	</div>

{/if}
{/block}