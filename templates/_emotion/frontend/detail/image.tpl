
{block name="frontend_detail_image"}
	{if $sArticle.image.src.4}
		{* Main picture *}
		{block name='frontend_detail_image_main'}
			{if $sArticle.image.res.relations}
				<div id="img{$sArticle.image.res.relations}" style="display:none">
					<a href="{$sArticle.image.src.5}"
						title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" 
						{if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
						rel="lightbox">
						
			    		<img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
			    	</a>
				</div>
			{/if}

            <a id="zoom1" href="{$sArticle.image.src.5}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" {if {config name=sUSEZOOMPLUS}}class="cloud-zoom"{/if} rel="lightbox[{$sArticle.ordernumber}]">
				<img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
			</a>
	    {/block}
	
	{* No picture available *}
	{else}
		{block name='frontend_detail_image_empty'}
			<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sArticle.articleName}" />
		{/block}
	{/if}
	
	{block name='frontend_detail_image_thumbs'}
		{include file="frontend/detail/images.tpl"}
	{/block}
{/block}
