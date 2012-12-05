{* todo@stp - add snippets *}
{if $sArticle.laststock && !$sArticle.sVariants && $sArticle.instock < $sArticle.maxpurchase}
    {assign var=maxQuantity value=$sArticle.instock+1}
{else}
    {assign var=maxQuantity value=$sArticle.maxpurchase+1}
{/if}

<label>{s name="DetailBuyLabelQuantity" namespace="frontend/detail/buy"}{/s}:</label>

{* Normal quantity select box *}
<div class="single-delivery">
    <select class="sQuantity" name="sQuantity">
        {section name="i" start=$sArticle.minpurchase loop=$maxQuantity step=$sArticle.purchasesteps}
            <option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
        {/section}
    </select>
</div>

{* Abo commerce quantity select box *}
<div class="abo-delivery">
    <select class="sQuantity" name="sQuantity"{if !$aboCommerce.isExclusive} disabled="disabled"{/if}>
        {section name="i" start=1 max=$aboCommerce.maxQuantityPerWeek loop=$maxQuantity step=1}
            <option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
        {/section}
    </select>
</div>