{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shop Benchmark - Start{/block}

{block name="benchmark_index_body"}
    <body id="swag-start">

    <div class="wrapper">

        <div class="swag-onbording">

            <div class="tob-bar">
                <ul class="top-benefits">
                    <li>Keine Kosten</li>
                    <li>Komplett anonym</li>
                    <li>Unverbindlich</li>
                </ul>
            </div>

            <div class="preview-screen"></div>
            <div class="funny-graph"></div>

            <div class="teaser-text">
                <div class="row">

                    <div class="col">
                        <h1>Shop Benchmark</h1>
                        <p>Erhalte Deine Shop-Auswertung und vergleiche diese mit deiner Branche.</p>
                    </div>

                    <div class="col">
                        <a href="{url controller="BenchmarkLocalOverview" action="render" template="info"}">
                            <div class="btn primary">Ja, ich möchte mehr erfahren</div>
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>

    </body>
{/block}
