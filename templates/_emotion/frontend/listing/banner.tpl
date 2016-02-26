
{block name="frontend_listing_banner"}
{if $sBanner}
	{if $sBanner.extension=="swf"}
		
		{* Flash banner *}
		{block name='frontend_listing_swf_banner'}
		<object classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" 
	      	codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0">
		    <param name="movie" value="{$sBanner.img}">
		    <param name="quality" value="high">
		    <param name="scale" value="exactfit">
		    <param name="menu" value="true">
		    <param name="bgcolor" value="#000040">
		    <embed src="{$sBanner.img}" quality="high" scale="exactfit" menu="false" width="653" height="170"
		           bgcolor="#000000"  swLiveConnect="false"
		           type="application/x-shockwave-flash"
		           pluginspage="http://www.macromedia.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">
		    </embed>
		</object>
		{/block}
	{elseif $sBanner.liveshoppingData}
		{include file="frontend/listing/box_liveshopping.tpl" liveArt=$sBanner.liveshoppingData}
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
