
{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>$sCampaign.description, 'link'=>$sCampaign.link]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">
	<div class="campaign_site">
	{foreach from=$sCampaign.containers item=sCampaignContainer}

	    {if $sCampaignContainer.type == "ctBanner"}

	    	{* Banner *}
	    	{block name='frontend_campaign_index_banner'}
	        {if $sCampaignContainer.data.link}
	            <a href="{$sCampaignContainer.data.link}" target="{$sCampaignContainer.data.linkTarget}">
	            	<img class="banner" src="{$sCampaignContainer.data.image}" />
	            </a>
	        {else}
	            <img class="banner" src="{$sCampaignContainer.data.image}" alt="{$sCampaignContainer.data.description}" />
	        {/if}
	    	{/block}
	    {elseif $sCampaignContainer.type == "ctLinks"}

	    	{* Link *}
	    	{block name='frontend_campaign_index_links'}
	        <div class="links">
	        <h2>{$sCampaignContainer.description}</h2>
	            <ul>
	            {foreach from=$sCampaignContainer.data item=sLink}
	                <li><a href="{$sLink.link}" target="{$sLink.target}" class="ico link">{$sLink.description}</a></li>
	            {/foreach}
	            </ul>
	        </div>
	        {/block}

	    {elseif $sCampaignContainer.type == "ctArticles"}

	    	{* Article*}
	    	{block name='frontend_campaign_index_article'}
	        <div>
	        	<div class="listing" id="listing-3col">
	            <h2>{$sCampaignContainer.description}</h2>
	            {foreach from=$sCampaignContainer.data item=sArticle key=key  name="counter"}
	            {if $sArticle.mode=="gfx"}
	            	{if $sArticle.link}
	            		<a href="{$sArticle.link}" {if $sArticle.linkTarget}target="{$sArticle.linkTarget}"{/if}><img src="{$sArticle.img}" title="{$sArticle.description}" alt="{$sArticle.description}" /></a>
	            	{else}
	            		<img src="{$sArticle.img}" alt="{$sArticle.description}" title="{$sArticle.description}" />
	            	{/if}
	            {else}
	                {include file="frontend/listing/box_article.tpl" sArticle=$sArticle}
	            {/if}
	            {/foreach}
	            </div>
	        </div>
	       	<hr class="clear" />
	       	{/block}

	    {elseif $sCampaignContainer.type == "ctText"}
	 		{* Text *}
	 		{block name='frontend_campaign_index_text'}
	        <div class="cat_text">
	        	<div class="inner_container">
	           	 	<h1>{$sCampaignContainer.description}</h1>
	            	{$sCampaignContainer.data.html}
	            </div>
	        </div>
	        {/block}
	    {/if}

	{/foreach}
	</div>

</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file='frontend/campaign/right.tpl'}
{/block}
