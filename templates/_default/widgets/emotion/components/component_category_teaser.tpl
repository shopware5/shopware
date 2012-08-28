{if $Data}
<div class="teaser_box">
    <a href="{url controller=cat action=index sCategory=$Data.category_selection}">
    	{* teaser image *}
    	<div class="teaser_img"{if $Data.image} style="background:url({link file=$Data.image}) no-repeat center center"{/if}>&nbsp;</div>
    	
    	{* teaser headline *}
        <div class="teaser_headline">
        	<h3>{$Data.categoryName}</h3>
        </div>
    </a>
</div>
{/if}