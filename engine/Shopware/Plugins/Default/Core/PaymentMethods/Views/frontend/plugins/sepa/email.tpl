<style type="text/css">
    div, strong, p, b, i, em { margin: 0; padding: 0 }
    p, .topbar {
        margin: 0 0 20px;
    }
    .headline {
        margin: 20px 0 0;
    }
    .container {
        width: 590px;
        margin: 50px 40px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #333;
    }
    .row {
        margin: 20px 30px 0;
    }
    .field {
        border-bottom: 1px solid #c7c7c7;
        font-weight: bold;
        margin: 0;
        width: 425px;
        font-size: 21px;
    }
    .label {
        width: 400px;
        margin: 0 0 0 25px;
        color: #999;
    }
    .space {
        height: 35px;
    }
</style>

<div class="container">

    <div class="row">
        <div class="topbar">
            {$config.sepaHeaderText}
        </div>

        <p>{s namespace='frontend/plugins/sepa/email' name=SepaEmailCreditorNumber}Gläubiger-Identifikationsnummer{/s} <strong>{$config.sepaSellerId}</strong></p>

        <p>{s namespace='frontend/plugins/sepa/email' name=SepaEmailMandateReference}Mandatsreferenz <strong>{$data.orderNumber}</strong>{/s}</p>
    </div>

    <div class="row">
        {* Headline *}
        <h1 class="headline">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailDirectDebitMandate}SEPA-Lastschriftmandat{/s}</h1>
    </div>

    {* Description *}
    <div class="row">
        <p>{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailBody}
                Ich ermächtige den {$config.sepaCompany}, Zahlungen von meinem Konto
                mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an,
                die von dem {$config.sepaCompany} auf mein Konto gezogenen Lastschriften einzulösen.</p><p>
                Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten
                Betrages verlangen.
                Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.
        {/s}</p>
    </div>

    <div class="row">
        <div class="field">{if $data.accountHolder}{$data.accountHolder}{else}&nbsp;{/if}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailName}Vorname und Name (Kontoinhaber){/s}</div>
    </div>

    <div class="row">
        <div class="field">{if $data.address}{$data.address}{else}&nbsp;{/if}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailAddress}Straße und Hausnummer{/s}</div>
    </div>

    <div class="row">
        <div class="field">{if $data.zipCode && $data.city}{$data.zipCode} {$data.city}{else}&nbsp;{/if}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailZip}Postleitzahl und Ort{/s}</div>
    </div>
    
    <div class="row">
        <div class="field">{if {config name=sepaShowBankName} && $data.bankName}{$data.bankName}{else}&nbsp;{/if}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailBankName}Kreditinstitut{/s}</div>
    </div>
        
    <div class="row">
        <div class="field">{if {config name=sepaShowBic} && $data.bic}{$data.bic}{else}&nbsp;{/if}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailBic}BIC{/s}</div>
    </div>
        
    <div class="row">
        <div class="field">{$data.iban}</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailIban}IBAN{/s}</div>
    </div>

    <div class="space">&nbsp;</div>

    <div class="row">
        <div class="field">&nbsp;</div>
        <div class="label">{s namespace='frontend/plugins/payment/sepaemail' name=SepaEmailSignature}Datum, Ort und Unterschrift{/s}</div>
    </div>
</div>

