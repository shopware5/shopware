
{extends file='frontend/index/index.tpl'}
{* Main content *}
{block name='frontend_index_content'}
	{block name='frontend_newsletter_listing'}
		<div class="grid_13 newsletter_listing" id="center">
			{if $sContent}
				<div class="table">
					{* Newsletter table header *}
					<div class="table_head">
						<div class="grid_9">
							{s name="NewsletterListingHeaderName"}Name{/s}
						</div>
						<div class="grid_3">
							&nbsp;
						</div>
					</div>
					{foreach from=$sContent item=sContentItem key=key name="counter"}
						{* Newsletter entry *}
						{block name='frontend_newsletter_listing_entry'}
							<div class="table_row">
								<div class="grid_9">
									{if $sContentItem.date}{$sContentItem.date|date:"DATE_SHORT"} - {/if}{$sContentItem.description}
								</div>
								
								<div class="grid_3">
									<a href="{$sContentItem.link}" class="button-right small">{se name='NewsletterListingLinkDetails'}{/se}</a>
								</div>
							</div>
						{/block}
					{/foreach}
					<div class="clear">&nbsp;</div>
				</div>
			{else}
				{* Error message *}
				{block name='frontend_newsletter_listing_error_message'}
					<div class="notice center bold">
						{se name='NewsletterListingInfoEmpty'}{/se}
					</div>
				{/block}
			{/if}
			<div class="dobulespace">&nbsp;</div>
			
			{include file="frontend/newsletter/paging.tpl"}
		
		</div>
	{/block}
{/block}
