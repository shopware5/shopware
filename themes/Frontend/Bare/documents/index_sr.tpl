{extends file="documents/index.tpl"}

{block name="document_index_table_price"}
    {if $Document.netto != true && $Document.nettoPositions != true}
        <td align="right" width="10%">
            {($position.price*-1)|currency}
        </td>
        <td align="right" width="12%">
            {($position.amount*-1)|currency}
        </td>
    {else}
        <td align="right" width="10%">
            {($position.netto*-1)|currency}
        </td>
        <td align="right" width="12%">
            {($position.amount_netto*-1)|currency}
        </td>
    {/if}
{/block}
{block name="document_index_amount"}
<div id="amount">
      <table width="300px" cellpadding="0" cellspacing="0">
      <tbody>
      <tr>
        <td align="right" width="100px" class="head">{s name="DocumentIndexTotalNet"}{/s}</td>
        <td align="right" width="200px" class="head">-{$Order._amountNetto|currency}</td>
      </tr>
      {if $Document.netto == false}
          {foreach from=$Order._tax key=key item=tax}
          <tr>
            <td align="right">{s name="DocumentIndexTax"}{/s}</td>
            <td align="right">-{$tax|currency}</td>
          </tr>
          {/foreach}
      {/if}
      {if $Document.netto == false}
          <tr>
            <td align="right"><b>{s name="DocumentIndexTotal"}{/s}</b></td>
            <td align="right"><b>-{$Order._amount|currency}</b></td>
          </tr>
      {else}
          <tr>
            <td align="right"><b>{s name="DocumentIndexTotal"}{/s}</b></td>
            <td align="right"><b>-{$Order._amountNetto|currency}</b></td>
          </tr>
      {/if}
      </tbody>
      </table>
</div>
{/block}

{block name="document_index_head_bottom"}
    <h1>{s name="DocumentIndexCancelationNumber"}{/s}</h1>
    {s name="DocumentIndexPageCounter"}{/s}
{/block}