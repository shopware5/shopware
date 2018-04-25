{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shop Benchmark{/block}

{block name="benchmark_index_body"}
    <body id="swag-benchmark">
        <div class="wrapper">
            <div class="opener">
                <div class="swag-container">
                    <div class="confetti"></div>
                    <h1>Hey!</h1>
                    <h2>Die Auswertung Deiner Shopdaten ist fertig.</h2>
                    <div class="bulb">drop this down</div>
                </div>
            </div>

            <div class="special-note">
                <div class="swag-container">
                    <h1>Information</h1>
                    <h3>Die <span class="branch-color">Branchendaten</span> werden noch ausgewertet, daher enthält die
                        Auswertung vorerst nur <span class="shop-color">Deine Shopdaten</span>.</h3>
                    <div class="nice-graphic"></div>
                </div>
            </div>

            <div class="swag-text-break industry-data">
                <div class="wave-left"></div>

                <section id="container-industry-data">
                    <div id="slider-container">
                        <ul class="images-container">
                            <li>
                                <div class="slide slide--shop">
                                    <h1>Dein Shop</h1>
                                    <h3>hat im <span class="shop-color">Durchschnitt</span> am Tag <span class="shop-color">999 Bestellungen</span>
                                        mit einem <span class="shop-color">Bestellwert von 500€.</span> Am meisten wurde in
                                        der <span class="shop-color">Zeit von 18 bis 20 Uhr</span> bestellt.</h3>
                                    <div class="nice-graphic"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class='bullets-container'></div>
                </section>

                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Umsatz</h1>
                    <div id="swag-chart--sales" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='turnOver'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Besucher</h1>
                    <div id="swag-chart--visitor" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='visitors'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Bestellungen</h1>
                    <div id="swag-chart--order" class="diagram">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='totalOrders'}
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Conversion</h1>
                    <div id="swag-chart--conversion" class="column-graph">
                        {include file="backend/benchmark/template/local/include/graph-data.tpl" dataKey='conversions' chartType='bar'}
                    </div>
                </div>
            </div>

            <!-- start user-data -->
            <div class="swag-text-break user-data">
                <div class="wave-left"></div>

                <div class="swag-container">
                    <div class="slide no-slide ">
                        <div class="benchmark-text">

                            <div class="slide--shop">
                                <h1>Die Kunden</h1>
                                <h3>sind im Durchschitt <span class="shop-color">23 Jahre</span> und <span
                                        class="shop-color">60% weiblich.</span> <span class="shop-color">Achtung: 30%</span>
                                    der Kunden haben <span class="shop-color">kein Geburtsdatum</span> angegeben.</h3>
                                <div class="nice-graphic"></div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Zielgruppe</h1>
                    <div id="swag-chart--targetgroup">

                        <div class="col-50 targetgroup--branch">
                            <h4>Branche</h4>
                            <div class="like-branch-ice"></div>
                            <div class="data">
                                <span class="data--headline high-contrast">Frauen</span><br>
                                <span class="data--headline branch-color">70%</span><br>
                                <span class="data--txt">7.000</span><br>
                                <span class="data--txt">Ø 30 Jahre</span>
                                <br><br>
                                <span class="data--headline high-contrast">Männer</span><br>
                                <span class="data--headline branch-color">80%</span><br>
                                <span class="data--txt">5.000</span><br>
                                <span class="data--txt">Ø 25 Jahre</span>
                            </div>

                            <div class="data-wrapper">
                                <span class="data--headline high-contrast">Alter</span><br>

                                <div class="row">
                                    <div class="col-33 data--txt branch-color">50 +</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:15%;"><span
                                                class="data--txt high-contrast">15%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt branch-color">30 bis 50</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:30%;"><span
                                                class="data--txt high-contrast">30%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt branch-color">15 bis 30</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:55%;"><span
                                                class="data--txt high-contrast">55%</span></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-50 targetgroup--shop">
                            <h4>Shop</h4>
                            <div class="like-shop-ice"></div>
                            <div class="data">
                                <span class="data--headline high-contrast">Frauen</span><br>
                                <span class="data--headline shop-color">80%</span><br>
                                <span class="data--txt">5.000</span><br>
                                <span class="data--txt">Ø 25 Jahre</span>
                                <br><br>
                                <span class="data--headline high-contrast">Männer</span><br>
                                <span class="data--headline shop-color">70%</span><br>
                                <span class="data--txt">7.000</span><br>
                                <span class="data--txt">Ø 30 Jahre</span><br>
                            </div>

                            <div class="data-wrapper">
                                <span class="data--headline high-contrast">Alter</span><br>

                                <div class="row">
                                    <div class="col-33 data--txt shop-color">50 +</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:25%;"><span
                                                class="data--txt high-contrast">25%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt shop-color">30 bis 50</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:30%;"><span
                                                class="data--txt high-contrast">30%</span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-33 data--txt shop-color">15 bis 30</div>
                                    <div class="col-66">
                                        <div class="graph-gradient" style="width:45%;"><span
                                                class="data--txt high-contrast">45%</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- @todo: add circular gauge chart like iphone activity design -->
            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Endgerät</h1>

                    <div id="swag-chart--device"></div>
                </div>
            </div>


            <div class="swag-text-break payment-shipping-data">
                <div class="wave-left"></div>
                <div class="swag-container">
                    <div class="slide no-slide ">
                        <div class="benchmark-text">
                            <div class="slide--shop">
                                <h1>Zahlen & Fakten</h1>
                                <h3>es werden<span class="shop-color">3 Zahlarten</span> angeboten.<span class="shop-color">Versandkosten</span>
                                    liegen bei<span class="shop-color"> ca. 3,50 €</span> pro Bestellung. <span
                                        class="shop-color">90%</span> der Artikel sind <span class="shop-color">vorrätig und versandfertig.</span>
                                </h3>
                                <div class="nice-graphic"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wave-right"></div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Top 5 Zahlarten</h1>
                    <div id="swag-chart--payment">
                        <div class="col-50 payment">
                            <h4>Branche</h4>
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
                            <h4>Shop</h4>
                            <div class="wrapper">
                                <div class="graph"
                                     style="height: 0px; width: 0px; line-height: 0px; rgba(52, 220, 221, 0.6);"><span
                                        class="data--txt high-contrast"></span></div>
                                <div class="graph"
                                     style="height: 180px; width: 180px; line-height: 180px; background-color: rgba(52, 220, 221, 1.0);">
                                    <span class="data--txt high-contrast is-bigger">100%</span></div>
                                <div class="graph"
                                     style="height: 0px; width: 0px; line-height: 0px; background-color: rgba(52, 220, 221, 0.8);">
                                    <span class="data--txt high-contrast"></span></div>
                                <div class="graph"
                                     style="height: 0px; width: 0px; line-height: 0px; background-color: rgba(52, 220, 221, 0.4);">
                                    <span class="data--txt high-contrast"></span></div>
                                <div class="graph"
                                     style="height: 0px; width: 0px; line-height: 0px; background-color: rgba(52, 220, 221, 0.2);">
                                    <span class="data--txt high-contrast"></span></div>
                            </div>

                            <div class="row">
                                <div class="col-80 data--txt">
                                    <div class="col-66 shop-color">DHL</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 shop-color">Hermes</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 shop-color">DPD</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 shop-color">UPS</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 shop-color">-</div>
                                    <div class="col-33 high-contrast"></div>
                                </div>
                            </div>
                        </div>
                        <!-- @todo: integrate overlay with payment list -->
                        <div class="btn">weitere anzeigen</div>
                    </div>
                </div>
            </div>

            <div class="swag-chart">
                <div class="swag-container">
                    <h1>Top 5 Versandarten</h1>
                    <div id="swag-chart--shipping">
                        <div class="col-50 shipping">
                            <h4>Branche</h4>
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
                            <h4>Shop</h4>
                            <div class="wrapper">
                                <div class="graph" style="padding:0px; background-color: rgba(52, 220, 221, 0.6);"><span
                                        class="data--txt high-contrast"></span></div>
                                <div class="graph" style="padding:50px; background-color: rgba(52, 220, 221, 1.0);"><span
                                        class="data--txt high-contrast is-bigger">100%</span></div>
                                <div class="graph" style="padding:0px; background-color: rgba(52, 220, 221, 0.8);"><span
                                        class="data--txt high-contrast"></span></div>
                                <div class="graph" style="padding:0px; background-color: rgba(52, 220, 221, 0.4);"><span
                                        class="data--txt high-contrast"></span></div>
                                <div class="graph" style="padding:0px; background-color: rgba(52, 220, 221, 0.2);"><span
                                        class="data--txt high-contrast"></span></div>
                            </div>

                            <div class="row">
                                <div class="col-80 data--txt">
                                    <div class="col-66 shop-color">DHL</div>
                                    <div class="col-33 high-contrast">35%</div>
                                    <div class="col-66 shop-color">-</div>
                                    <div class="col-33 high-contrast"></div>
                                    <div class="col-66 shop-color">-</div>
                                    <div class="col-33 high-contrast"></div>
                                    <div class="col-66 shop-color">-</div>
                                    <div class="col-33 high-contrast"></div>
                                    <div class="col-66 shop-color">-</div>
                                    <div class="col-33 high-contrast"></div>
                                </div>
                            </div>
                        </div>
                        <!-- @todo: integrate overlay with shipping list -->
                        <div class="btn">weitere anzeigen</div>
                    </div>
                </div>
            </div>

            <!-- Footer text-break-three -->
            <footer class="swag-the-end">
                <div class="swag-container">
                    <div class="benchmark-text">
                        <div class="confetti"></div>
                        <h1>THE END</h1>
                        <h3><span class="shop-color">Let's rock the future.</span></h3>
                        <br>
                        <h4>Möchtest Du etwas loswerden? Hat Dir was gefehlt?</h4>

                        <div class="nice-message">
                            <textarea placeholder="Danke f&uuml;r die Auswertung. Gr&uuml;ße von Anonym "></textarea>
                            <div class="btn secondary">Nachricht senden</div>
                        </div>

                        <p>Eine Abmeldung ist jederzeit möglich im Hauptmenü unter: Marketing - Benchmark - Einstellungen. </p>

                    </div>
                </div>
            </footer>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="{link file='backend/benchmark/template/local/js/slider.js'}"></script>
        <script src="{link file='backend/benchmark/template/local/js/benchmark_charts.js'}"></script>
        <script src="{link file='backend/benchmark/template/local/js/time_switcher.js'}"></script>
        <script src="{link file='backend/benchmark/template/local/js/switch_button.js'}"></script>
        <script src="{link file='backend/benchmark/template/local/vendor/js/chart.js'}"></script>
        <script type="text/javascript">
            window.benchmarkData = JSON.parse('{$benchmarkData}');
        </script>
    </body>
{/block}
