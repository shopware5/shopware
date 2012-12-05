<div class="price-separation-popup">
    <div class="arrow"></div>
    <div class="price-separation-inner-popup">
        <h3 class="price-separation-popup-headline">Preisstaffelung für Menge 1</h3>

        <table>
            <thead>
                <tr>
                    <th>Laufzeit</th>
                    <th>Rabatt (in {'0'|currency|substr:-6})</th>
                </tr>
            </thead>
            <tbody>
                {foreach $aboCommerce.prices as $price}
                <tr>
                    <td>ab {$price.duration} {$aboCommerce.durationUnit}</td>
                    <td><strong>{$price.discountAbsolute|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</strong></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>