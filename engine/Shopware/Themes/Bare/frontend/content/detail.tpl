{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
	<div class="grid_16" id="center">
	{if $sContentItem}
	    
	    <div class="contentdetail">
	    
	    {* Article name *}
		{block name='frontend_content_detail_name'}
	    	<h2>{$sContentItem.datum} - {$sContentItem.description}</h2>
	    {/block}
	    
	    {* Article Picture *}
	    {if $sContentItem.img}
	    	{block name='frontend_content_detail_picture'}
				<a href="{$sContentItem.imgBig}" rel="lightbox[photos]" title="{s name="ContentInfoPicture"}{/s} {$sContentItem.description}" class="main_image">
	                <img src="{$sContentItem.img}" alt="{$sContentItem.description}" border="0" title="{$sContentItem.description}" />
				</a>
	        {/block}
	    {/if}
	    
	    {* Article Description *}
	    {block name='frontend_content_detail_description'}
	    	{$sContentItem.text}
	    {/block}
	    
	    
	    {if $sContentItem.link}
	    	{* Read more *}
	    	{block name='frontend_content_detail_more'}
		        <h2>{s name="ContentHeaderInformation"}{/s}</h2>
		        <a href="{$sContentItem.link}">{$sContentItem.link}</a>
	        {/block}
	    {/if}
		    
	    {* Downloads *}
	    {if $sContentItem.attachment}
	    	{block name='frontend_content_detail_downloads'}
	       		<h2>{s name="ContentHeaderDownloads"}{/s}</h2>
	        	<a href="{$sContentItem.attachment}" target="_blank">{s name="ContentLinkDownload"}{/s}</a>
	        {/block}
	    {/if}
	    
	    <div class="space">&nbsp;</div>
	    	
	    {* Back button *}
		<a href="javascript:history.back();" class="button-left large">
			{s name="ContentLinkBack"}{/s}
		</a>
	{else}
	
		{* Article couldn't be found *}
		{block name='frontend_content_detail_error'}
		    <div class="notice">
		    	{s name="ContentInfoNotFound"}{/s}
		    </div>
	    {/block}
	{/if}
		</div>
	<hr class="clear" />
	<hr class="clear" />
	</div>
{/block}