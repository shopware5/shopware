
{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16" id="center">
	<div class="newsletter_detail">
		{block name='frontend_newsletter_detail'}
			{if $sContentItem}
				
				{* Newsletter title *}
				{block name='frontend_newsletter_listing_title'}
					<h2 class="headingbox">{if $sContentItem.date}{$sContentItem.date|date:"DATE_SHORT"} - {/if}{$sContentItem.description}</h2>
				{/block}
			    
			    {* Actual newsletter *}
			    {block name='frontend_newsletter_listing_iframe'}
			    <div class="newsletter_content">
			    	<iframe src="{$sContentItem.link}"></iframe>
				</div>
				{/block}
			{else}
			
				{* Error message *}
				{block name='frontend_newsletter_listing_error_message'}
				<div class="notice">
			    	{s name='NewsletterDetailInfoEmpty'}{/s}
			    </div>
			    {/block}
			{/if}
			
			<a href="{$sBackLink}" class="button-left large"><span>{se name='NewsletterDetailLinkBack'}{/se}</span></a>
			<a href="{$sContentItem.link}" class="button-right large right" target="_blank"><span>{se name='NewsletterDetailLinkNewWindow'}{/se}</span></a>
			<div class="clear">&nbsp;</div>
		{/block}
	</div>
</div>
<div class="clear">&nbsp;</div>
{/block}
