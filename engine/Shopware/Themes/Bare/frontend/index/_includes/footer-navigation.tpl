{namespace name="frontend/index/menu_footer"}

{* Service hotline *}
{block name="frontend_index_footer_column_service_hotline"}
    <div class="footer--column column--hotline is--first block">
        <h4 class="column--headline" data-slide-panel="true">{s name="sFooterServiceHotlineHead"}Service Hotline{/s}</h4>

		{block name="frontend_index_footer_column_service_hotline_content"}
			<div class="column--content">
				<p class="column--desc">{s name="sFooterServiceHotline"}Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr{/s}</p>
			</div>
		{/block}
    </div>
{/block}

{block name="frontend_index_footer_column_service_menu"}
<div class="footer--column column--menu block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterShopNavi1"}Shop Service{/s}</h4>

	{block name="frontend_index_footer_column_service_menu_content"}
		<nav class="column--navigation column--content">
			<ul class="navigation--list" role="menu">
				{block name="frontend_index_footer_column_service_menu_before"}{/block}
				{foreach $sMenu.gBottom as $item}
					{block name="frontend_index_footer_column_service_menu_entry"}
						<li class="navigation--entry" role="menuitem">
							<a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
								{$item.description}
							</a>
						</li>
					{/block}
				{/foreach}
				{block name="frontend_index_footer_column_service_menu_after"}{/block}
			</ul>
		</nav>
	{/block}
</div>
{/block}

{block name="frontend_index_footer_column_information_menu"}
<div class="footer--column column--menu block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterShopNavi2"}Informationen{/s}</h4>

	{block name="frontend_index_footer_column_information_menu_content"}
    <nav class="column--navigation column--content">
        <ul class="navigation--list" role="menu">
			{block name="frontend_index_footer_column_information_menu_before"}{/block}
            {foreach $sMenu.gBottom2 as $item}
				{block name="frontend_index_footer_column_information_menu_entry"}
					<li class="navigation--entry" role="menuitem">
						<a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
							{$item.description}
						</a>
					</li>
				{/block}
            {/foreach}
			{block name="frontend_index_footer_column_information_menu_after"}{/block}
        </ul>
    </nav>
	{/block}
</div>
{/block}

{block name="frontend_index_footer_column_newsletter"}
<div class="footer--column column--newsletter is--last block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterNewsletterHead"}Newsletter{/s}</h4>

	{block name="frontend_index_footer_column_newsletter_content"}
		<div class="column--content">
			<p class="column--desc">
				{s name="sFooterNewsletter"}Abonnieren Sie den kostenlosen DemoShop Newsletter und verpassen Sie keine Neuigkeit oder Aktion mehr aus dem DemoShop.{/s}
			</p>

			{block name="frontend_index_footer_column_newsletter_form"}
				<form class="newsletter--form" action="{url controller='newsletter'}" method="post">
					<input type="hidden" value="1" name="subscribeToNewsletter" />

					{block name="frontend_index_footer_column_newsletter_form_field"}
						<input type="email" name="newsletter" class="newsletter--field" placeholder="{s name="IndexFooterNewsletterValue"}Ihre E-Mail Adresse{/s}" />
					{/block}

					{block name="frontend_index_footer_column_newsletter_form_submit"}
						<button type="submit" class="newsletter--button">
							<i class="icon--search"></i> <span class="button--text">{s name='IndexFooterNewsletterSubmit'}Newsletter abonnieren{/s}</span>
						</button>
					{/block}
				</form>
			{/block}
		</div>
	{/block}
</div>
{/block}