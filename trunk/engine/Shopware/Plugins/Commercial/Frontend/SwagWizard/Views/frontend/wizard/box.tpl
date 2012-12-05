{if $Wizards[$WizardBlock]}
{foreach from=$Wizards[$WizardBlock] item=Wizard}
{if $Wizard.image}
	{block name='frontend_wizard_box_image_link'}
			<a href="{url controller='wizard' wizardID=$Wizard.id}" class="campaign_box" title="{$Wizard.name}">
				<img src="{link file=$Wizard.image}" width="150" alt="{$Wizard.name}" />
			</a>
	{/block}
{else}
	{block name='frontend_wizard_box_link'}
	<a href="{url controller='wizard' wizardID=$Wizard.id}" class="campaign_box" title="{$Wizard.name}">
		{$Wizard.name}
	</a>
	{/block}
{/if}
{/foreach}
{/if}