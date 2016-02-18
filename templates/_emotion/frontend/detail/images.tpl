{* Main picture *}
<div id='img_1_{$sArticle.ordernumber}' class="displaynone">
	{if $sArticle.image.src.1}
		<a title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticle.image.src.1});cursor:pointer"></a>
	{else}
		<a title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../resource/images/no_picture.jpg);cursor:pointer"></a>
	{/if}
	
	{foreach from=$sArticle.images item=sArticleImage}
    	{if $sArticleImage.relations} 
            <div id="img_1_{$sArticleImage.relations}" class="displaynone"> 
            
            {if $sArticleImage.src.1} 
                    <a title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticleImage.src.1});cursor:pointer"></a>
            {else} 
					<a title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../resource/images/no_picture.jpg);cursor:pointer"></a>
            {/if} 
            </div> 
    	{/if} 
	{/foreach} 
</div>

{* Thumbnails *}
{if $sArticle.images}
	<div class="space">&nbsp;</div>
	<div class="thumb_box">
        {if $sArticle.image.src.4}
            <a href="{$sArticle.image.src.5}"
            title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}"
            style="background-repeat: no-repeat; background-position: center center; background-color:#fff; background-image: url({$sArticle.image.src.1});"
            {if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
            rev="{$sArticle.image.src.4}">
        </a>
        {/if}
		{foreach from=$sArticle.images item=sArticleImage}
			{if $sArticleImage.relations}
		
			    {* Main picture variant *}
			    <div id="img{$sArticleImage.relations}" class="displaynone">
			    	<a rel="lightbox[{$sArticleImage.relations}]" 
			    	   {if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
			    	   href="{$sArticleImage.src.5}" 
			    	   title="{if $sArticleImage.res.description}{$sArticleImage.res.description}{else}{$sArticle.articleName}{/if}">
			    	   <img src="{$sArticleImage.src.4}" title="{if $sArticleImage.res.description}{$sArticleImage.res.description}{else}{$sArticle.articleName}{/if}" />
			    	</a>
		   		</div>
		    
			    {* Thumbnail variant *}
			    <a id="thumb{$sArticleImage.relations}" 
			       href="{$sArticleImage.src.5}" 
			       title="{if $sArticleImage.res.description}{$sArticleImage.res.description}{else}{$sArticle.articleName}{/if}" 
			       rev="{$sArticleImage.src.4}" 
			       {if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
			       style="background-repeat: no-repeat; background-position: center center; background-color:#fff; background-image: url({$sArticleImage.src.1});">
			    </a>
		    {else}
			     <a href="{$sArticleImage.src.5}" 
			        title="{if $sArticleImage.res.description}{$sArticleImage.res.description}{else}{$sArticle.articleName}{/if}"
			        rev="{$sArticleImage.src.4}" 
			        {if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
			        style="background-repeat: no-repeat; background-position: center center; background-color:#fff; background-image: url({$sArticleImage.src.1});">
			     </a>
		    {/if}
		{/foreach}
		<div class="clear">&nbsp;</div>
	</div>
	<div class="clear">&nbsp;</div>
{/if}