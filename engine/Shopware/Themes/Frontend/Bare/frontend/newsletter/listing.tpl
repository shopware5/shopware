{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}

	{* Newsletter listing *}
	{block name='frontend_newsletter_listing'}
		<div class="newsletter-listing--content content block">

			{if $sContent}
				{block name="frontend_newsletter_listing_headline"}
					<div class="newsletter-listing-headline panel--body is--wide">
						<h1 class="newsletter--title">{s name="NewsletterListingHeadline"}Newsletter-Archiv{/s}</h1>
					</div>
				{/block}

				{* Newsletter listing table *}
				{block name="frontend_newsletter_listing_table"}
					<div class="newsletter-listing--table panel has--border">

						{* Newsletter table header *}
						{block name="frontend_newsletter_listing_table_headline"}
							<div class="newsletter-listing--table-headline panel--title is--underline">

								{block name="frontend_newsletter_listing_header_name"}
									<div class="newsletter-listing--headline-name">
										{s name="NewsletterListingHeaderName"}Name{/s}
									</div>
								{/block}

								{block name="frontend_newsletter_listing_header_button"}
									<div class="newsletter-listing--headline-button">
										&nbsp;
									</div>
								{/block}
							</div>
						{/block}

						{block name="frontend_newsletter_listing_entry_list"}
							<div class="newsletter-listing--entry-list panel--body is--wide">
								{foreach $sContent as $sKey => $sContentItem}

									{* Newsletter entry *}
									{block name='frontend_newsletter_listing_entry'}
										<div class="newsletter-listing--entry">

											{* Newsletter entry description *}
											{block name="frontend_newsletter_listing_entry_description"}
												<div class="newsletter-listing--entry-description">
													{if $sContentItem.date}{$sContentItem.date|date:"DATE_SHORT"} - {/if}{$sContentItem.description}
												</div>
											{/block}

											{* Newsletter entry button *}
											{block name="frontend_newsletter_listing_entry_button"}
												<div class="newsletter-listing--entry-button">
													<a href="{$sContentItem.link}" class="btn btn--secondary is--small">{s name='NewsletterListingLinkDetails'}{/s}</a>
												</div>
											{/block}
										</div>
									{/block}
								{/foreach}
							</div>
						{/block}
					</div>
				{/block}
			{else}
				{* Error message *}
				{block name='frontend_newsletter_listing_error_message'}
					{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='NewsletterListingInfoEmpty'}{/s}"}
				{/block}
			{/if}

			{* Paging *}
			{block name="frontend_newsletter_listing_paging"}
				{include file="frontend/newsletter/paging.tpl"}
			{/block}
		
		</div>
	{/block}
{/block}