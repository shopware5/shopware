{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shop Benchmark - Start{/block}

{block name="benchmark_index_body"}
    <body id="swag-start">

    <div class="wrapper">

        <div class="swag-onbording">

            <div class="tob-bar">
                <ul class="top-benefits">
                    <li>Kostenlos</li>
                    <li>Anonym</li>
                    <li>Unverbindlich</li>
                </ul>
            </div>

            <div class="preview-screen"></div>

            <div class="teaser-text">
                <div class="funny-graph"></div>
                <div class="row">
                    <div class="col-100">
                        <h1>Benchmark für Deinen Onlineshop</h1>
                        <p>Erhalte Deine Shop-Auswertung und vergleiche sie mit Unternehmen Deiner Branche.</p>
                    </div>

                    <div class="col-100">
                        <a href="{url controller="BenchmarkLocalOverview" action="render" template="info"}">
                            <div class="btn primary">Ja, ich will mehr erfahren</div>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    </body>
{/block}
