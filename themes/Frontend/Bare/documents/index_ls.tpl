{extends file="documents/index.tpl"}

{block name="document_index_table_head_tax"}
{/block}
{block name="document_index_table_head_price"}
{/block}
{block name="document_index_table_tax"}
{/block}
{block name="document_index_table_price"}
{/block}
{block name="document_index_amount"}
{/block}

{block name="document_index_head_bottom"}
    <h1>{s name="DocumentIndexShippingNumber"}{/s} {$Document.id}</h1>
    {s name="DocumentIndexPageCounter"}{/s}
{/block}
{block name="document_index_selectAdress"}
    {assign var="address" value="shipping"}
{/block}
{block name="document_index_table_each"}{if $position.modus == 0 || $position.modus == 1}{$smarty.block.parent}{/if}{/block}
{block name="document_index_head_right"}
    {$smarty.block.parent}
    {if $Document.bid}{s name="DocumentIndexInvoiceID"}{/s} {$Document.bid}<br />{/if}
{/block}
