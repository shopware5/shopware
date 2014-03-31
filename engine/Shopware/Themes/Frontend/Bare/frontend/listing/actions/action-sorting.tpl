{* Sorting filter which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

<form class="action--sort action--content block" method="get" action="{url controller=cat sCategory=$sCategoryContent.id}">
    {foreach $categoryParams as $value}
        {if $value@key == 'sSort'}
            {continue}
        {/if}
        <input type="hidden" name="{$value@key}" value="{$value}">
    {/foreach}

    {* Necessary to reset the page to the first one *}
    <input type="hidden" name="sPage" value="1">

    {* Sorting label *}
    {block name='frontend_listing_actions_sort_label'}
        <label class="sort--label action--label">{s name='ListingLabelSort'}{/s}</label>
    {/block}

    {* Sorting field *}
    {block name='frontend_listing_actions_sort_field'}
		<div class="sort--select field--select">
			<span class="arrow"></span>
			<select name="sSort" class="sort--field action--field">
				<option value="1"{if $sSort eq 1} selected="selected"{/if}>{s name='ListingSortRelease'}{/s}</option>
				<option value="2"{if $sSort eq 2} selected="selected"{/if}>{s name='ListingSortRating'}{/s}</option>
				<option value="3"{if $sSort eq 3} selected="selected"{/if}>{s name='ListingSortPriceLowest'}{/s}</option>
				<option value="4"{if $sSort eq 4} selected="selected"{/if}>{s name='ListingSortPriceHighest'}{/s}</option>
				<option value="5"{if $sSort eq 5} selected="selected"{/if}>{s name='ListingSortName'}{/s}</option>
				{block name='frontend_listing_actions_sort_values'}{/block}
			</select>
		</div>
    {/block}
</form>