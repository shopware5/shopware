
{block name='frontend_frontend_checkout_added_info_teaser'}
{if $sArticleName}
	<div class="success bold center">
		{se name="CheckoutAddArticleInfoAdded"}"{$sArticleName}" wurde in den Warenkorb gelegt!{/se}
	</div>
	
	<div class="space">&nbsp;</div>
{else}
	&nbsp;
{/if}
{/block}
