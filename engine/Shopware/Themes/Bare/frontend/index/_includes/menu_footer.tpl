{* Service hotline *}
{block name="frontend_index_footer_column_service_hotline"}
    <div class="footer--column column--hotline is--first block">
        <h4 class="column--headline" data-slide-panel="true">{s name="sFooterServiceHotlineHead"}Service Hotline{/s}</h4>
		<div class="column--content">
        	<p class="column--desc">{s name="sFooterServiceHotline"}Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr{/s}</p>
		</div>
    </div>
{/block}

{block name="frontend_index_footer_column_service_menu"}
<div class="footer--column column--menu block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterShopNavi1"}Shop Service{/s}</h4>

    <nav class="column--navigation column--content">
        <ul class="navigation--list" role="menu">
            {foreach $sMenu.gBottom as $item}
                <li class="navigation--entry" role="menuitem">
                    <a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
                        {$item.description}
                    </a>
                </li>
            {/foreach}
        </ul>
    </nav>
</div>
{/block}

{block name="frontend_index_footer_column_information_menu"}
<div class="footer--column column--menu block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterShopNavi2"}Informationen{/s}</h4>

    <nav class="column--navigation column--content">
        <ul class="navigation--list" role="menu">
            {foreach $sMenu.gBottom2 as $item}
                <li class="navigation--entry" role="menuitem">
                    <a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
                        {$item.description}
                    </a>
                </li>
            {/foreach}
        </ul>
    </nav>
</div>
{/block}


{block name="frontend_index_footer_column_newsletter"}
<div class="footer--column column--newsletter is--last block">
    <h4 class="column--headline" data-slide-panel="true">{s name="sFooterNewsletterHead"}Newsletter{/s}</h4>

	<div class="column--content">
		<p class="column--desc">
			{s name="sFooterNewsletter"}Abonnieren Sie den kostenlosen DemoShop Newsletter und verpassen Sie keine Neuigkeit oder Aktion mehr aus dem DemoShop.{/s}
		</p>

		{block name="frontend_index_footer_column_newsletter_form"}
			<form class="newsletter--from" action="{url controller='newsletter'}" method="post">
				<input type="hidden" value="1" name="subscribeToNewsletter" />

				<input type="text" name="newsletter" class="newsletter--field" value="{s name="IndexFooterNewsletterValue"}Ihre E-Mail Adresse{/s}" />
				<input type="submit" class="newsletter--button" value="{s name='IndexFooterNewsletterSubmit'}Newsletter abonnieren{/s}" />
			</form>
		{/block}
	</div>
</div>
{/block}