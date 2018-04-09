{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shop Benchmark - Fragen und Antworten{/block}

{block name="benchmark_index_body"}
    <body id="swag-info">
    <div class="wrapper">
        <div class="swag-onbording">
            <div class="tob-bar">
                <ul class="top-benefits">
                    <li>Keine Kosten</li>
                    <li>Komplett anonym</li>
                    <li>Unverbindlich</li>
                </ul>
            </div>

            <div class="wrapper-info">
                <div class="info--text">
                    <h1>Fragen & Antworten</h1>

                    <div class="row">
                        <div class="col-m">
                            <h2>Was passiert mit meinen Daten?</h2>
                            <p>Deine Daten werden anonym abgelegt - dies bedeutet, dass dein Export eine ID bekommt die
                                niemand kennt und es auch nicht möglich ist von deinen Shopnamen oder anderen Daten
                                diese ausfindig zu machen.</p>

                            <h2>Wie werden meine Daten genutzt?</h2>
                            <p>Ausschließlich für die Auswertungen und den Branchen-Vergleich. Um so mehr Daten wir in
                                jeder Branche erhalten um so effektiver und informativer werden die Ergebnisse. Die
                                Auswertungen sind ebenfalls bestandteil einer Trainingsphase für einen möglichen
                                Shopware-AI Service.</p>

                            <h2>Weshalb könnte das für mich nützlich sein?</h2>
                            <p>Bisher kannst du dir nur deine Statistiken ansehen oder auf Shopbetreiber-Treffen Infos
                                von anderen erfragen. Mit diesem Branchen-Vergleich wird es dir möglichkeit sein, anhand
                                von Zahlen und Fakten defiziten zu erkennen und gezielt Maßnahmen zu ergreifen. <strong>Lerne
                                    deinen Shop noch besser kenn.</strong></p>
                        </div>

                        <div class="col-s">
                            <h2><span class="check-color">Die Fakten</span></h2>
                            <ul class="top-benefits">
                                <li>Auswertung deiner Shopdaten</li>
                                <li>Vergleich mit Branchendaten</li>
                                <li>Hochwertige Darstellung</li>
                                <li>Auto Aktualisierungen</li>
                                <li>Keine Kosten</li>
                                <li>Komplett anonym</li>
                                <li>Komplett unverbindlich</li>
                                <li>Sofortige Abmeldung möglich</li>
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
