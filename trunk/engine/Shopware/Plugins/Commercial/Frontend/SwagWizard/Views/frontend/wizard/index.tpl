{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>$Wizard.name, 'link'=>{url wizardID=$Wizard.id}]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">
	<div class="campaign_site">
		<form name="filter_form" method="post" action="{url action=filter wizardID=$Wizard.id}">
			<input type="hidden" name="page" value="1" />
		    <div class="cat_text">
		    	<div class="inner_container">
		       	 	<h1>{$Wizard.name}</h1>
		        	{$Wizard.description}
		        </div>
		    </div> 
		    <hr class="clear" />
			
			<div class="actions">
				<input type="submit" value="Start" class="button-right large right">
				<hr class="clear space">
			</div>
	   	</form>
	   	<div class="slider"></div>
	</div>
	
</div>

{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file='frontend/campaign/right.tpl'}
{/block}