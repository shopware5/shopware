{block name='frontend_campaign_box'}
	{if $campaignsData && !$sCategoryCampaigns}
		{foreach from=$campaignsData item=campaign}
				{block name='frontend_campaign_box_image_link'}
					<div class="campaing-outer-container">
						<a href="{url controller=campaign emotionId=$campaign.id sCategory=$campaign.categoryId}" class="campaign_box" title="{$campaign.name}">
							<img src="{$campaign.landingPageTeaser}" width="179" alt="{$campaign.name}" />
						</a>
					</div>
					<div class="space"></div>
				{/block}
		{/foreach}
	{/if}
{/block}