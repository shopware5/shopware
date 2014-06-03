{extends file="frontend/index/index.tpl"}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
    <script type="text/javascript">
        //<![CDATA[
        if(top!=self){
            top.location=self.location;
        }
        //]]>
    </script>
{/block}

{* Include the necessary stylesheets. We need inline styles here due to the fact that the colors are configuratable. *}
{block name="frontend_index_header_css_screen" append}
    <style type="text/css">
        #confirm .table, #confirm .country-notice {
            background: {config name=baskettablecolor};
        }
        #confirm .table .table_head {
            color: {config name=basketheaderfontcolor};
            background: {config name=basketheadercolor};
        }
    </style>
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<hr class="clear" />{/block}

{block name="frontend_index_content_top"}
    <div class="grid_20 first">

        {* Step box *}
        {include file="frontend/register/steps.tpl" sStepActive="finished"}
    </div>
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div id="confirm" class="grid_16 push_2 first">

    {* Error messages *}
    {block name='frontend_checkout_confirm_error_messages'}
        {include file="frontend/checkout/error_messages.tpl"}
    {/block}

    <div class="outer-confirm-container">

        {* Personal information *}
        <div class="personal-information grid_16 first">
            <div class="inner_container">
                {* Payment method *}
                {include file="frontend/checkout/confirm_payment.tpl" sTargetAction="shippingPayment" sTarget="checkout"}
            </div>
            <div class="inner_container">
                {* Payment method *}
                {include file="frontend/checkout/confirm_dispatch.tpl" hideSubmitButton=true}
            </div>
            <div class="inner_container">
                {* Payment method *}
                {include file="frontend/checkout/cart_footer.tpl"}
            </div>
        </div>

        <a href="{url controller='checkout' action='index'}">{s name='NextButton'}Next{/s}</a>
    </div>
{/block}
