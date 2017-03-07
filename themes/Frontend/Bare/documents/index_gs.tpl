{extends file="documents/index.tpl"}
{block name="document_index_head_bottom"}
    <h1>{s name="DocumentIndexCreditNumber"}{/s} {$Document.id}</h1>
    {s name="DocumentIndexPageCounter"}Seite {$page+1} von {$Pages|@count}{/s}
{/block}
{block name="document_index_head_right"}
    {$smarty.block.parent}
    {if $Document.bid}{s name="DocumentIndexInvoiceID"}{/s} {$Document.bid}<br />{/if}
{/block}
