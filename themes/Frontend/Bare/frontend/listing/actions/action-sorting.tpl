{* Sorting filter which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

<form class="action--sort action--content block" method="get" data-action-form="true">

    {* Necessary to reset the page to the first one *}
    <input type="hidden" name="{$shortParameters.sPage}" value="1">

    {* Sorting label *}
    {block name='frontend_listing_actions_sort_label'}
        <label class="sort--label action--label">{s name='ListingLabelSort'}{/s}</label>
    {/block}

    {* Sorting field *}
    {block name='frontend_listing_actions_sort_field'}
		<select name="{$shortParameters.sSort}" class="sort--field action--field" data-auto-submit="true" data-class="sort--select">
			{block name="frontend_listing_actions_sort_field_release"}
				<option value="1"{if $sSort eq 1} selected="selected"{/if}>{s name='ListingSortRelease'}{/s}</option>
			{/block}

			{block name="frontend_listing_actions_sort_field_rating"}
				<option value="2"{if $sSort eq 2} selected="selected"{/if}>{s name='ListingSortRating'}{/s}</option>
			{/block}

			{block name="frontend_listing_actions_sort_field_price_asc"}
				<option value="3"{if $sSort eq 3} selected="selected"{/if}>{s name='ListingSortPriceLowest'}{/s}</option>
			{/block}

			{block name="frontend_listing_actions_sort_field_price_desc"}
				<option value="4"{if $sSort eq 4} selected="selected"{/if}>{s name='ListingSortPriceHighest'}{/s}</option>
			{/block}

			{block name="frontend_listing_actions_sort_field_name"}
				<option value="5"{if $sSort eq 5} selected="selected"{/if}>{s name='ListingSortName'}{/s}</option>
			{/block}

			{block name='frontend_listing_actions_sort_values'}{/block}
		</select>
    {/block}
</form>
