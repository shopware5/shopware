{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ['name'=>"{s name='MyDownloadsTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content block account--content">

		{* Error message *}
		{block name="frontend_account_downloads_error_messages"}
			{if $sErrorCode}
				{$errorText="{s name='DownloadsInfoNotFound'}{/s}"}
				{if $sErrorCode == 1}
					{$errorText="{s name='DownloadsInfoAccessDenied'}{/s}"}
				{/if}

				{include file="frontend/_includes/messages.tpl" type="warning" content=$errorText}
			{/if}
		{/block}

		{* Welcome text *}
		{block name="frontend_account_downloads_welcome"}
			<div class="account--welcome panel">
				{block name="frontend_account_downloads_welcome_headline"}
					<h1 class="panel--title">{s name="DownloadsHeader"}{/s}</h1>
				{/block}

				{block name="frontend_account_downloads_welcome_content"}
					<div class="panel--body is--wide">
						<p>{s name='DownloadsInfoText'}{/s}</p>
					</div>
				{/block}
			</div>
		{/block}

		{* Missing ESD articles *}
		{if !$sDownloads}
			{block name='frontend_account_downloads_info_empty'}
				{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='DownloadsInfoEmpty'}{/s}"}
			{/block}
	    {else}
	    	<div class="account--downloads panel--table">

				{block name="frontend_account_downloads_table_head"}
					<div class="panel--tr">
						<div class="panel--th column--date">{s name="DownloadsColumnDate"}{/s}</div>
						<div class="panel--th column--info">{s name="DownloadsColumnName"}{/s}</div>
						<div class="panel--th column--actions">{s name="DownloadsColumnLink"}{/s}</div>
					</div>
				{/block}

				{foreach from=$sDownloads item=offerPosition}
					{foreach name=offerdetails from=$offerPosition.details item=article}
						{if $article.esdarticle}

							{block name="frontend_account_downloads_table_row"}
								<div class="panel--tr">

									{block name="frontend_account_downloads_date"}
										<div class="panel--td column--date">
											{$offerPosition.datum|date}
										</div>
									{/block}

									{block name='frontend_account_downloads_info'}
										<div class="panel--td column--info">
											{block name='frontend_account_downloads_name'}
												<span class="is--bold">{$article.name}</span>
											{/block}

											{block name='frontend_account_downloads_serial'}
												{if $article.serial && $offerPosition.cleared|in_array:$sDownloadAvailablePaymentStatus}
													<p>{s name="DownloadsSerialnumber"}{/s} <span class="is--bold">{$article.serial}</span></p>
												{/if}
											{/block}
										</div>
									{/block}

									{block name='frontend_account_downloads_link'}
										<div class="panel--td column--actions">
											{if $article.esdarticle && $offerPosition.cleared|in_array:$sDownloadAvailablePaymentStatus}
												<a href="{$article.esdLink}" title="{s name="DownloadsLink"}{/s} {$article.name}" class="btn btn--primary is--small">
													{s name="DownloadsLink"}{/s}
												</a>
											{/if}
										</div>
									{/block}

								</div>
							{/block}
						{/if}
					{/foreach}
				{/foreach}

				{block name='frontend_account_downloads_actions_paging'}
					{if $sPages.numbers|@count > 1}
						<div class="panel--paging">

							<label>{s name="ListingPaging"}Blättern:{/s}</label>

							{if $sPages.previous}
								<a href="{$sPages.previous}">
									{s name="ListingTextPrevious"}&lt;{/s}
								</a>
							{/if}

							{foreach from=$sPages.numbers item=page}
								{if $page.markup}
									<a href="#">{$page.value}</a>
								{else}
									<a href="{$page.link}">{$page.value}</a>
								{/if}
							{/foreach}

							{if $sPages.next}
								<a href="{$sPages.next}">{s name="ListingTextNext"}&gt;{/s}</a>
							{/if}
							<div class="pagination--display">
								{s name="ListingTextSite"}Seite{/s} <strong>{if $sPage}{$sPage}{else}1{/if}</strong> {s name="ListingTextFrom"}von{/s} <strong>{$sNumberPages}</strong>
							</div>

						</div>
					{/if}
				{/block}
		    </div>

	    {/if}
	</div>
{/block}