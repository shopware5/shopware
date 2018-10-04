{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Branche wählen{/block}

{block name="benchmark_index_head_scripts"}
    {$smarty.block.parent}
    <script src="{link file='backend/benchmark/template/local/js/industry_select.js'}"></script>
{/block}

{block name="benchmark_index_body"}
    <body id="swag-select">
        {include file="backend/benchmark/template/local/include/loading_indicator.tpl"}
        {include file="backend/benchmark/template/local/include/language_switch.tpl"}
        <div class="wrapper">
            <div class="industry-select--ct" data-industry-select="true">
                {include file="backend/benchmark/template/local/include/config_overlay.tpl"}
                <div class="swag-container">
                    <div id="select-wrapper">
                        {if {acl_is_allowed resource=benchmark privilege=manage}}
                            <h1>[[ $t('industrySelectionHeadline') ]]</h1>

                            <div class="industry--shop-list">
                                <div class="shop-list--wrapper">
                                    {foreach $shops as $shop}
                                        <div class="shop-list--entry">
                                            <input class="entry-shop-id" type="hidden" name="shopId" value="{$shop['id']}" />
                                            <span class="shop-list--name">{$shop['name']}</span>
                                            <span class="shop-list--info">
                                                <span class="shop-list--info-text">[[ $t('emptyShopConfigText') ]]</span>
                                                <span class="shop-list--button"></span>
                                            </span>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>

                        <div class="industry--save-button" style="display: none;"
                            data-success-url="{url controller=BenchmarkOverview action=index lang='placeholder'}"
                            data-save-url="{url controller=BenchmarkOverview action=saveIndustry}">
                            <span class="save-button--text">[[ $t('saveIndustryText') ]]</span>
                            <span class="save-button--shop-counter">0</span>
                        </div>
                    {else}
                        <h1>[[ $t('noPermissionsTitle') ]]</h1>
                        <span class="industry--no-permissions">[[ $t('noPermissionsMessage') ]]</span>
                    {/if}
                </div>

                <div class="wild-graph"></div>
                <div class="bubble-two"></div>
            </div>
        </div>
    </body>
{/block}

