{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Branche wählen{/block}

{block name="benchmark_index_body"}
    <body id="swag-select">
        <div class="wrapper">
            <div class="branch-select">
                <div class="swag-container">
                    <div id="select-wrapper">
                        <h1>Los geht's</h1>
                        <form action="{url controller="BenchmarkOverview" action="setIndustry"}">
                            <div class="btn primary select-bg">
                                <select name="industry">
                                    <option value="0">Keine</option>
                                    <option value="1">Tiere & Tierbedarf</option>
                                    <option value="2">Bekleidung & Accessoires</option>
                                    <option value="3">Kunst & Unterhaltung</option>
                                    <option value="4">Baby & Kleinkind</option>
                                    <option value="5">Wirtschaft & Industrie</option>
                                    <option value="6">Kameras & Optik</option>
                                    <option value="7">Elektronik</option>
                                    <option value="8">Nahrungsmittel, Getränke & Tabak</option>
                                    <option value="9">Möbel</option>
                                    <option value="10">Heimwerkerbedarf</option>
                                    <option value="11">Gesundheit & Schönheit</option>
                                    <option value="12">Heim & Garten</option>
                                    <option value="13">Taschen & Gepäck</option>
                                    <option value="14">Für Erwachsene</option>
                                    <option value="15">Medien</option>
                                    <option value="16">Bürobedarf</option>
                                    <option value="17">Religion & Feierlichkeiten</option>
                                    <option value="18">Software</option>
                                    <option value="19">Sportartikel</option>
                                    <option value="20">Spielzeuge & Spiele</option>
                                    <option value="21">Fahrzeuge & Teile</option>
                                </select>
                            </div>
                            <p>Wähle Deine Branche aus, damit die Daten <br />verglichen werden können.</p>

                            <button type="submit" class="btn secondary">Jetzt speichern</button>
                        </form>
                    </div>
                </div>

                <div class="wild-graph"></div>
                <div class="bubble-one"></div>
                <div class="bubble-two"></div>
            </div>

        </div>
    </body>
{/block}

