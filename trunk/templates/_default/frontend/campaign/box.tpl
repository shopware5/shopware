{block name='frontend_campaign_box'}
{if $sCategoryCampaigns}

	{foreach from=$sCategoryCampaigns item=sCampaign}
		{if $sCampaign.image}
		
			{* Campaign link with image *}
			{if $sCampaign.link}
				{block name='frontend_campaign_box_image_link'}
					<a href="{if $sCampaign.link}{$sCampaign.link}{else}#{/if}" class="campaign_box" title="{$sCampaign.description}" target="{$sCampaign.linktarget}">
						<img src="{$sCampaign.image}" width="150" alt="{$sCampaign.description}" />
					</a>
				{/block}
				
			{* Campaign image *}
			{else}
				{block name='frontend_campaign_box_image'}
					<img src="{$sCampaign.image}" title="{$sCampaign.description}" width="150" alt="{$sCampaign.description}" />
				{/block}
			{/if}
			
		{* Campaign link *}
		{else}
			{block name='frontend_campaign_box_link'}
			<a href="{if $sCampaign.link}{$sCampaign.link}{else}#{/if}" class="campaign_box" title="{$sCampaign.description}" target="{$sCampaign.linktarget}">
				{$sCampaign.description}
			</a>
			{/block}
		{/if}
	{/foreach}
	
{/if}
{/block}