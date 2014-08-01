{* Per page filter which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{if $sPerPage}
    <form class="action--per-page action--content block" method="get" action="{url controller=cat sCategory=$sCategoryContent.id}">
        {foreach $categoryParams as $key => $value}
            {if $key == 'sPerPage' || $key == $shortParameters.sPerPage}
                {continue}
            {/if}
            <input type="hidden" name="{$key}" value="{$value}">
        {/foreach}

        {* Necessary to reset the page to the first one *}
        <input type="hidden" name="{$shortParameters.sPage}" value="1">

        {* Per page label *}
        {block name='frontend_listing_actions_items_per_page_label'}
            <label class="per-page--label action--label">{s name='ListingLabelItemsPerPage'}{/s}</label>
        {/block}

        {* Per page field *}
        {block name='frontend_listing_actions_items_per_page_field'}
			<select name="{$shortParameters.sPerPage}" class="per-page--field action--field" data-auto-submit="true" data-class="per-page--select">
				{foreach $sPerPage as $perPage}
					<option value="{$perPage.value}" {if $perPage.markup}selected="selected"{/if}>{$perPage.value}</option>
				{/foreach}
				{block name='frontend_listing_actions_per_page_values'}{/block}
			</select>
        {/block}
    </form>
{/if}