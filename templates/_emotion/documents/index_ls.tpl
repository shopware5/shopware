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
	<h1>{s name="DocumentIndexShippingNumber"}Lieferschein Nr.{/s} {$Document.id}</h1>
	{s name="DocumentIndexPageCounter"}Seite {$page+1} von {$Pages|@count}{/s}
{/block}
{block name="document_index_selectAdress"}
	{assign var="address" value="shipping"}
{/block}
{block name="document_index_table_each"}{if $position.modus == 0 || $position.modus == 1}{$smarty.block.parent}{/if}{/block}
{block name="document_index_head_right" append}
{if $Document.bid}{s name="DocumentIndexInvoiceID"}Zur Rechnung:{/s} {$Document.bid}<br />{/if}
{/block}