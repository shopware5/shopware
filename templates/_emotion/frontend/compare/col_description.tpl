
{* Compare Description *}
<div class="grid_3 compare_desc first">
	
	<div class="picture">
		{block name='frontend_article_picture'}
			{se name="CompareColumnPicture"}{/se}
		{/block}
	</div>
	
	<div class="name">
		{block name='frontend_compare_article_name'}
			{se name="CompareColumnName"}{/se}
		{/block}
	</div>
	
	<div class="votes">
		{block name='frontend_compare_votings'}
			{se name="CompareColumnRating"}{/se}
		{/block}
	</div>
	
	<div class="desc">
		{block name='frontend_compare_description'}
			{se name="CompareColumnDescription"}{/se}
		{/block}
	</div>
	
	<div class="price">
		{block name='frontend_compare_price'}
			{se name="CompareColumnPrice"}{/se}
		{/block}
	</div>
	
	{foreach from=$sComparisonsList.properties item=property}
		{block name='frontend_compare_properties'}
		<div class="property">
			{if $property}{$property}:{/if}
		</div>
		{/block}
	{/foreach}
	
</div>
