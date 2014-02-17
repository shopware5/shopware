
{extends file='frontend/account/index.tpl'}


{block name="frontend_index_header_javascript_inline" append}
    {* Configuration of the Partner Chart *}
    jQuery.partnerChart =  {ldelim}
    'timeUnit': '{s name='PartnerStatisticLabelTimeUnit'}{/s}',
    'netAmountLabel': '{s name='PartnerStatisticLabelNetTurnover'}{/s}'
    {rdelim};
{/block}


{block name="frontend_index_header_javascript" append}
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/plugins/raphael/raphael.js'}"></script>
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/plugins/raphael/popup.js'}"></script>
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/plugins/raphael/analytics.js'}"></script>
{/block}


{* Breadcrumb *}
{block name='frontend_index_start' append}
    {$sBreadcrumb[] = ['name'=>"{s name='Provisions'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}


{* Partner Provision overview *}
<div class="grid_16 partner_statistic" id="center">
    <h1>{se name="PartnerStatisticHeader"}{/se}</h1>
    <div class="listing_actions normal">
        {block name='frontend_account_partner_statistic_listing_actions_top'}
            <div class="top">
                {block name="frontend_account_partner_statistic_listing_date"}
                    <form method="post" action="{url controller='account' action='partnerStatistic'}">
                        <div class="date-filter">
                            <label>{s name='PartnerStatisticLabelFromDate'}{/s}</label>
                            <input id="datePickerFrom" class="datePicker" name="fromDate" type="text" value="{$partnerStatisticFromDate}" class="text" />
                        </div>
                        <div class="date-filter">
                            <label>{s name='PartnerStatisticLabelToDate'}{/s}</label>
                            <input id="datePickerTo" class="datePicker" name="toDate" type="text" value="{$partnerStatisticToDate}" class="text" />
                        </div>
                        <input type="submit" class="button-right small_right partner_statistic"  value="{s name="PartnerStatisticSubmitFilter"}{/s}" />

                    </form>
                {/block}
            </div>
        {/block}
    </div>
    <div class="clear"></div>
    <div>
        <table id="data">
            <tbody>
                <tr>
                    {foreach from=$sPartnerOrderChartData item=chartItem}
                        <td>{$chartItem.netTurnOver}</td>
                    {/foreach}
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    {foreach from=$sPartnerOrderChartData item=chartItem}
                    <th>{$chartItem.timeScale}</th>
                    {/foreach}
                </tr>
            </tfoot>
        </table>
        <div id="holder" style="width: 780px"></div>
    </div>
    {if !$sPartnerOrders}
        {block name="frontend_account_partner_statistic_info_empty"}
            <fieldset>
                <div class="notice center bold">
                    {se name="PartnerStatisticInfoEmpty"}{/se}
                </div>
            </fieldset>
        {/block}
        {else}

        <div class="partner_statistic_overview_active">

            <div class="table grid_16">
                {block name="frontend_account_partner_statistic_table_head"}
                    <div class="table_head">

                        <div class="grid_4">
                            {se name="PartnerStatisticColumnDate"}{/se}
                        </div>

                        <div class="grid_4">
                            {se name="PartnerStatisticColumnId"}{/se}
                        </div>

                        <div class="grid_4">
                            {se name="PartnerStatisticColumnNetAmount"}{/se}
                        </div>

                        <div class="grid_3">
                            {se name="PartnerStatisticColumnProvision"}{/se}
                        </div>

                    </div>
                {/block}
                {foreach name=partnerStatisticList from=$sPartnerOrders item=partnerOrder}
                    {if $smarty.foreach.partnerStatisticList.last}
                        {assign var=lastitem value=1}
                        {else}
                        {assign var=lastitem value=0}
                    {/if}
                {include file="frontend/account/partner_statistic_item.tpl" lastitem=$lastitem}
                {/foreach}
            </div>
        </div>
        <div class="space">&nbsp;</div>
    {/if}
</div>
{/block}
