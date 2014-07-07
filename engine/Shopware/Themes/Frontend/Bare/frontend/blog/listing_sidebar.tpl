{* Blog listing sidebar right *}
{block name='frontend_index_content_right'}



	<div class="action--filter-options off-canvas{if $activeFilters} is--collapsed{/if}">

		{block name='frontend_listing_actions_filter_container'}

			<a href="#" class="filter--close-btn">
				{s name="ListingActionsCloseFilter"}Filter schließen{/s} <i class="icon--arrow-right"></i>
			</a>

			<div class="filter--container">

				{block name="frontend_listing_actions_filter_container_inner"}

					<h2 class="filter--headline">{s name='FilterHeadline'}Filtern nach:{/s}</h2>

				{/block}

				<div class="blog--sidebar">

					{* Campaign top *}
					{block name='frontend_blog_index_campaign_top'}
						{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
					{/block}

					{* Blog navigation *}
					{block name="frontend_blog_index_navigation"}
						<div class="blog--navigation block-group">

							{* Subscribe Atom + RSS *}
							{block name='frontend_blog_index_subscribe'}
								<div class="blog--subscribe is--rounded block">

									{block name="frontend_blog_index_subscribe_headline"}
										<h1 class="blog--subscribe-headline panel--title is--underline collapse--header" data-collapse-panel="true">{s name="BlogSubscribe"}Subscribe{/s}<span class="filter--expand-collapse collapse--toggler"></span></h1>
									{/block}

									{block name="frontend_blog_index_subscribe_content"}
										<div class="blog--subscribe-content panel--body is--wide collapse--content">
											<ul class="filter--list list--unstyled">
												{block name="frontend_blog_index_subscribe_entry_rss"}
													<li class="filter--entry"><a class="rss" href="{$sCategoryContent.rssFeed}" title="{$sCategoryContent.description}">{s namespace="frontend/blog/index" name="BlogLinkRSS"}{/s}</a></li>
												{/block}

												{block name="frontend_blog_index_subscribe_entry_atom"}
													<li class="filter--entry last"><a class="atom" href="{$sCategoryContent.atomFeed}" title="{$sCategoryContent.description}">{s namespace="frontend/blog/index" name="BlogLinkAtom"}{/s}</a></li>
												{/block}
											</ul>
										</div>
									{/block}
								</div>
							{/block}

							{* Campaign Middle *}
							{block name='frontend_blog_index_campaign_middle'}
								{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
							{/block}

							{* Blog filter *}
							{block name='frontend_blog_index_filter'}
								{include file="frontend/blog/filter.tpl"}
							{/block}
						</div>
					{/block}

					{* Campaign bottom *}
					{block name='frontend_blog_index_campaign_bottom'}
						{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
					{/block}
				</div>
			</div>

		{/block}
	</div>
{/block}