{block name="backend/base/header/javascript" append}
{if $product}
<script type="text/javascript">
    Ext.onReady(function() {
        Ext.getBody().addCls('shopware-{$product|lower}');
    });
</script>
{/if}
{/block}