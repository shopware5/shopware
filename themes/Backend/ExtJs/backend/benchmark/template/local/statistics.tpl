{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI{/block}

{block name="benchmark_index_head_scripts"}
    <script src="{link file='backend/benchmark/template/local/js/components.js'}"></script>
    {$smarty.block.parent}
    <script src="{link file='backend/benchmark/template/local/js/slider.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/js/benchmark_charts.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/js/target_group_chart.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/js/time_switcher.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/js/switch_button.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/vendor/js/chart.js'}"></script>
    <script src="{link file='backend/benchmark/template/local/vendor/components/LiquidButton/js/index.js'}"></script>
{/block}

{block name="benchmark_index_body"}
    <body id="swag-benchmark">
        {include file="backend/benchmark/template/local/include/loading_indicator.tpl"}
        {include file="backend/benchmark/template/local/include/language_switch.tpl"}
        <div class="wrapper">
            <div class="preview-disclaimer" v-html="$t('previewDisclaimer')">
            </div>
            <div class="opener">
                <div class="swag-container">
                    <div class="konfetti"></div>
                    <h1>[[ $t('greeting') ]]</h1>
                    <h2>[[ $t('headlineMain') ]]</h2>
                    <a class="btn-liquid" onclick="onClickLiquidButton()">
                        <span class="inner">[[ $t('getStartedBtnText') ]]</span>
                    </a>
                </div>
            </div>

            <div class="special-note">
                <div class="swag-container">
                    <h1>[[ $t('informationHeadline') ]]</h1>
                    <h3 v-html="$t('informationText')"></h3>
                    <div class="nice-graphic"></div>
                    {include file="backend/benchmark/template/local/components/scroll_mouse.tpl"}
                </div>
            </div>

            <div class="swag-text-break industry-data">
                <div class="wave-left"></div>

                <section id="container-industry-data">
                    <div id="slider-container">
                        <ul class="images-container">
                            <li>
                                {include file="backend/benchmark/template/local/components/average_orders.tpl"}
                            </li>
                        </ul>
                    </div>
                    <div class='bullets-container'></div>
                </section>

                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('turnOverTitle') ]]</h1>
                    <div id="swag-chart--sales" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='turnOver'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('visitorsTitle') ]]</h1>
                    <div id="swag-chart--visitor" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='visitors'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('ordersTitle') ]]</h1>
                    <div id="swag-chart--order" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='totalOrders'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('conversionTitle') ]]</h1>
                    <div id="swag-chart--conversion" class="column-graph">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='conversions'}
                    </div>
                </div>
            </div>

            <!-- start user-data -->
            <div class="swag-text-break user-data">
                <div class="wave-left"></div>

                <div class="swag-container">
                    <div class="slide no-slide ">
                        <div class="benchmark-text">
                            {include file="backend/benchmark/template/local/components/average_customers.tpl"}
                        </div>
                    </div>
                </div>
                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('targetGroupsTitle') ]]</h1>
                    <div id="swag-chart--targetgroup">
                        <div class="col-50 targetgroup--branch industry-disabled">
                            <div class="disabled-notice" v-html="$t('disabledText')"></div>
                            <h4>[[ $t('industryTitle') ]]</h4>
                            <div class="like-branch-ice"></div>
                            <div class="data">
                                <span class="data--headline high-contrast">[[ $t('womenTitle') ]]</span><br>
                                <span class="data--headline branch-color">70%</span><br>
                                <span class="data--txt">7.000</span><br>
                                <span class="data--txt">Ø 30 [[ $t('yearsOldTitle') ]]</span>
                                <br><br>
                                <span class="data--headline high-contrast">[[ $t('menTitle') ]]</span><br>
                                <span class="data--headline branch-color">80%</span><br>
                                <span class="data--txt">5.000</span><br>
                                <span class="data--txt">Ø 25 [[ $t('yearsOldTitle') ]]</span>
                            </div>

                            <div class="data-wrapper">
                                <span class="data--headline high-contrast">[[ $t('ageTitle') ]]</span><br>

                                <div class="row">
                                    <div class="col-33 data--txt branch-color">[[ $t('above50Text') ]]</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:15%;"><span
                                                    class="data--txt high-contrast">15%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt branch-color">[[ $t('between30And50Text') ]]</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:30%;"><span
                                                    class="data--txt high-contrast">30%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt branch-color">[[ $t('between15And30Text') ]]</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:55%;"><span
                                                    class="data--txt high-contrast">55%</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {include file="backend/benchmark/template/local/components/target_groups.tpl"}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('devicesTitle') ]]</h1>
                    <div id="swag-chart--device">
                        {include file="backend/benchmark/template/local/components/devices_charts.tpl"}
                    </div>
                </div>
            </div>

            <div class="swag-text-break payment-shipping-data">
                <div class="wave-left"></div>
                <div class="swag-container">
                    <div class="slide no-slide ">
                        <div class="benchmark-text">
                            {include file="backend/benchmark/template/local/components/average_numbers.tpl"}
                        </div>
                    </div>
                </div>
                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('top5PaymentsTitle') ]]</h1>
                    <div id="swag-chart--payment">
                        <div class="col-50 payment industry-disabled">
                            <div class="disabled-notice" v-html="$t('disabledText')"></div>
                            <h4>[[ $t('industryTitle') ]]</h4>
                            <div class="wrapper">
                                <div class="graph"
                                     style="height: 66px; width: 66px; line-height: 66px; background-color: rgba(106, 99, 252, 0.8);">
                                    <span class="data--txt high-contrast">25%</span></div>
                                <div class="graph"
                                     style="height: 60px; width: 60px; line-height: 60px; background-color: rgba(106, 99, 252, 0.6);">
                                    <span class="data--txt high-contrast">20%</span></div>
                                <div class="graph"
                                     style="height: 90px; width: 90px; line-height: 90px; background-color: rgba(106, 99, 252, 1.0);">
                                    <span class="data--txt high-contrast is-bigger">35%</span></div>
                                <div class="graph"
                                     style="height: 50px; width: 50px; line-height: 50px; background-color: rgba(106, 99, 252, 0.4);">
                                    <span class="data--txt high-contrast">15%</span></div>
                                <div class="graph"
                                     style="height: 36px; width: 36px; line-height: 36px; background-color: rgba(106, 99, 252, 0.2);">
                                    <span class="data--txt high-contrast">5%</span></div>
                            </div>
                            <div class="row">
                                <div class="col-80 data--txt">
                                    <div class="col-66 branch-color">DHL</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">UPS</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">Hermes</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">DPD</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">GLS</div>
                                    <div class="col-33 high-contrast">35%</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-50 payment">
                            <h4>[[ $t('shopTitle') ]]</h4>
                            {include file="backend/benchmark/template/local/components/top_payments.tpl"}
                        </div>
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>[[ $t('top5ShipmentsTitle') ]]</h1>
                    <div id="swag-chart--shipping">
                        <div class="col-50 shipping industry-disabled">
                            <div class="disabled-notice" v-html="$t('disabledText')"></div>
                            <h4>[[ $t('industryTitle') ]]</h4>
                            <div class="wrapper">
                                <div class="graph" style="  background-color: rgba(106, 99, 252, 0.8);"><span
                                            class="data--txt high-contrast">25%</span></div>
                                <div class="graph" style="padding:20px; background-color: rgba(106, 99, 252, 0.6);"><span
                                            class="data--txt high-contrast">20%</span></div>
                                <div class="graph" style="padding:35px; background-color: rgba(106, 99, 252, 1.0);"><span
                                            class="data--txt high-contrast is-bigger">35%</span></div>
                                <div class="graph" style="padding:15px; background-color: rgba(106, 99, 252, 0.4);"><span
                                            class="data--txt high-contrast">15%</span></div>
                                <div class="graph" style="padding:5px; background-color: rgba(106, 99, 252, 0.2);"><span
                                            class="data--txt high-contrast">5%</span></div>
                            </div>
                            <div class="row">
                                <div class="col-80 data--txt">
                                    <div class="col-66 branch-color">DHL</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">UPS</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">Hermes</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">DPD</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 branch-color">GLS</div>
                                    <div class="col-33 high-contrast">35%</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-50 shipping">
                            <h4>[[ $t('shopTitle') ]]</h4>
                            {include file="backend/benchmark/template/local/components/top_shipments.tpl"}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer text-break-three -->
            <footer class="swag-the-end">
                <div class="swag-container">
                    <div class="benchmark-text">
                        <div class="konfetti"></div>
                        <h1>[[ $t('endTitle') ]]</h1>
                        <h3><span class="shop-color">[[ $t('endSubTitle') ]]</span></h3>
                        <br>
                        <h4>[[ $t('endText') ]]</h4>

                        <div class="nice-message">
                            <a :href="[[ $t('surveyLink') ]]" target="_blank" class="btn secondary">[[ $t('sendFeedbackBtn')
                                ]]</a>
                        </div>

                        <p>[[ $t('endSignOffText') ]]</p>
                    </div>
                </div>
            </footer>
        </div>

        {literal}
            <template id="paymentGraphBubble">
                <div class="payment-graph-bubble-wrapper">
                    <div v-for="(payment, index) in paymentsList" v-if="payment > 0" @key="index" class="graph"
                         :style="compStyle(payment)">
                        <span class="data--txt high-contrast" style="font-size: 0.25em">{{ payment }}%</span>
                    </div>
                </div>
            </template>
            <template id="paymentGraphList">
                <div class="payment-graph-list-wrapper">
                    <div v-for="(payment, index) in paymentsList" v-if="payment > 0" @key="index">
                        <div class="col-66 shop-color">{{ index }}</div>
                        <div class="col-33 high-contrast">{{ payment }}%</div>
                    </div>

                    <div v-for="(payment, index) in paymentsList" v-if="payment == -1" @key="index">
                        <div class="col-66 high-contrast">-</div>
                    </div>
                </div>
            </template>
            <template id="shipmentGraphSquares">
                <div class="shipment-graph-square-wrapper">
                    <div v-for="(shipment, index) in shipmentsList" v-if="shipment > 0" @key="index" class="graph"
                         :style="compStyle(shipment)">
                        <span style="font-size: 0.25em;" class="data--txt high-contrast">{{ shipment }}%</span>
                    </div>
                </div>
            </template>
            <template id="shipmentGraphList">
                <div class="shipment-graph-list-wrapper">
                    <div v-for="(shipment, index) in shipmentsList" v-if="shipment > 0" @key="index">
                        <div class="col-66 shop-color">{{ index }}</div>
                        <div class="col-33 high-contrast">{{ shipment }}%</div>
                    </div>

                    <div v-for="(shipment, index) in shipmentsList" v-if="shipment == -1" @key="index">
                        <div class="col-66 high-contrast">-</div>
                    </div>
                </div>
            </template>
            <template id="devicesGraphList">
                <div class="devices-graph-list-wrapper">
                    <div class="devices-item" v-for="(device, index) in devicesList" v-if="device > 0" @key="index">
                        <div class="shop-color col-66">{{ index }}</div>
                        <div class="high-contrast col-33">{{ device }}%</div>
                    </div>
                </div>
            </template>
        {/literal}
    </body>
{/block}
