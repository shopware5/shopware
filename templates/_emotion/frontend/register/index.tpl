{extends file='parent:frontend/register/index.tpl'}


{* Sidebar right *}
{block name='frontend_index_content_right'}
	<div id="right" class="grid_5 register last">
		<div class="register_info">
			{s name='RegisterInfoAdvantages'}{/s}
		</div>
		
	    {if {config name=TSID}}
	        {include file='frontend/plugins/trusted_shops/logo.tpl'}
	    {/if}
	</div>
{/block}