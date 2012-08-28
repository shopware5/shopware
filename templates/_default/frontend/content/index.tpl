{extends file='frontend/index/index.tpl'}

{* Clearing layout filter *}
{block name="view_filter"}{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div id="center" class="grid_13">
		<div class="contentlisting_box">
		
			{* include paging *}
			{include file="frontend/content/paging.tpl"}
			
			{if $sContent}
				{foreach from=$sContent item=sContentItem key=key name="counter"}
					<div class="content_listing">
						{* Article name *}
						{block name='frontend_content_index_name'}
							<h2>{$sContentItem.date|date:date_long} - {$sContentItem.description}</h2>
						{/block}
						
						{if $sContentItem.img}
							<div class="grid_4 first">
								
								{* Article picture *}
									{block name='frontend_content_index_picture'}
									<a href="{$sContentItem.linkDetails}" title="{$sContentItem.description}" class="thumb_image" style="background: #fff url({$sContentItem.img}) center center no-repeat;">&nbsp;
					        		</a>
					        		{/block}
							</div>
						{/if}
						<div>
							{* Article description *}
							{block name='frontend_content_index_description'}
								<p>
									{$sContentItem.text|truncate:420:"...":true}
								</p>
							{/block}
							
							<div class="clear">&nbsp;</div>
							{* Read more button *}
							{block name='frontend_content_index_more'}
								<a href="{$sContentItem.linkDetails}" class="more_info">{s name="ContentLinkDetails"}{/s}</a>
							{/block}
						</div>
					</div>
					<div class="space">&nbsp;</div>
				{/foreach}
			{else}
				
				{* No entries found *}
				{block name='frontend_content_index_error'}
					<div class="notice">
						{s name="ContentInfoEmpty"}{/s}
					</div>
				{/block}
			{/if}
		</div>
		<hr class="clear" />
		{* include paging *}
		{include file="frontend/content/paging.tpl"}
	</div>
{/block}