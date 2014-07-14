{extends file="backend/base/index.tpl"}

{block name="backend/base/header"}
    {include file="backend/index/header.tpl"}
{/block}

{block name="backend/base/container" append}
<form id="history-form" class="x-hide-display">
    <input type="hidden" id="x-history-field" />
    <iframe id="x-history-frame"></iframe>
</form>
{/block}