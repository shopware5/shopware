{extends file="documents/index.tpl"}
{block name="document_index_head_bottom"}
	<h1>{s name="DocumentIndexCreditNumber"}Gutschrift Nr.{/s} {$Document.id}</h1>
	{s name="DocumentIndexPageCounter"}Seite {$page+1} von {$Pages|@count}{/s}
{/block}
{block name="document_index_head_right" append}
{if $Document.bid}{s name="DocumentIndexInvoiceID"}Zur Rechnung:{/s} {$Document.bid}<br />{/if}
{/block}