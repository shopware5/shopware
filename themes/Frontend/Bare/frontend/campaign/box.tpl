{block name='frontend_campaign_box'}
	{if $campaignsData && !$sCategoryCampaigns}
		{foreach $campaignsData as $campaign}
				{block name='frontend_campaign_box_image_link'}
					<div class="campaign--box">
						<a href="{url controller=campaign emotionId=$campaign.id sCategory=$campaign.categoryId}"
						   class="campaign--banner"
						   title="{$campaign.name|escape}">
							<img src="{$campaign.landingPageTeaser}" alt="{$campaign.name|escape}" />
						</a>
					</div>
				{/block}
		{/foreach}
	{/if}
{/block}