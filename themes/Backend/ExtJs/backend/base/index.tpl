<!DOCTYPE html>
<html>

{block name="backend/base/header"}
    {include file="backend/base/header.tpl"}
{/block}

  <body {if $product}class="shopware-{$product|lower}"{/if}>
    {block name="backend/base/container"}
    <div class="container">

    {block name="backend/base/container_inner"}{/block}

    </div>
    {/block}
  </body>
</html>
