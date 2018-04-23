{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shop Benchmark - Fragen und Antworten{/block}

{block name="benchmark_index_body"}
    <body id="swag-info">
    <div class="wrapper">
        <div class="swag-onbording">
            <div class="tob-bar">
                <ul class="top-benefits">
                    <li>Kostenlos</li>
                    <li>Anonym</li>
                    <li>Unverbindlich</li>
                </ul>
            </div>

            <div class="wrapper-info">
                <div class="info--text">
                    <h1>Fragen & Antworten</h1>

                    <div class="row">
                        <div class="col-66">
                            <h2>Was passiert mit meinen Daten? </h2>
                            <p>Wir behandeln Deine Daten immer streng anonym. Der Export mit Deiner Auswertung wird mit einer anonymisierten ID versehen und macht den Rückschluss auf Dich und Deinen Shop unmöglich.</p>

                            <h2>Wofür werden meine Daten verwendet/benutzt?</h2>
                            <p>Wir verwenden die Daten ausschließlich für Shop-Auswertungen und Branchenvergleiche. Je mehr Daten wir zu jeder Branche erhalten, desto informativer und effektiver wird die Benchmark zusammengestellt.</p>

                            <h2>Warum ist das für mich nützlich?</h2>
                            <p>Bisher siehst Du nur Deine eigenen Statistiken und kannst nur selten bis gar nicht den Vergleichswert von anderen Onlineshops Deiner Branche bekommen. Der Branchenvergleich von Shopware macht es Dir möglich, anhand von Zahlen und Fakten Defizite zu erkennen und gezielt Maßnahmen zu ergreifen. <br><strong>Lerne Deinen Shop noch besser kenn.</strong></p>
                        </div>

                        <div class="col-33">
                            <h2><span class="check-color">Die Fakten</span></h2>
                            <ul class="top-benefits">
                                <li>Auswertung Deiner Shop-Daten</li>
                                <li>Vergleich mit Branchendaten</li>
                                <li>Einfache Usabilty</li>
                                <li>Auto-Aktualisierungen</li>
                                <li>Kostenlos</li>
                                <li>Anonym</li>
                                <li>Unverbindlich</li>
                            </ul>
                        </div>
                    </div>


                    <div class="accept-abg">
                        <a href="{url controller="BenchmarkLocalOverview" action="render" template="terms"}">
                            <div class="btn primary">Weiter zu den AGBs</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
{/block}
