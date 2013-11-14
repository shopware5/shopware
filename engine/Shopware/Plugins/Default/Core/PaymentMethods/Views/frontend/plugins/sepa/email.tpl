<div style="padding: 50px; width: 490px; margin-left: 40px;">
    <p><strong>{$config.sepaHeaderText}</strong></p>

    <p>{s name=SepaEmailCreditorNumber}Gläubiger-Identifikationsnummer{/s} <strong>{$config.sepaSellerId}</strong></p>

    <p>{s name=SepaEmailMandateReference}Mandatsreferenz <strong>{$data->getId()}</strong>{/s}</p>

    <h1>{s name=SepaEmailDirectDebitMandate}SEPA-Lastschriftmandat{/s}</h1>

    <p>{s name=SepaEmailBody}Ich ermächtige den {$config.sepaCompany}, Zahlungen von meinem Konto
        mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an,
        die von dem {$config.sepaCompany} auf mein Konto gezogenen Lastschriften einzulösen.</p>

    <p>
        Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten
        Betrages verlangen.
        Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.{/s}</p>

    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{if $data->getAccountHolder()}{$data->getAccountHolder()}{else}&nbsp;{/if}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailName}Vorname und Name (Kontoinhaber){/s}</p>
    </div>
    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{if $data->getAddress()}{$data->getAddress()}{else}&nbsp;{/if}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailAddress}Straße und Hausnummer{/s}</p>
    </div>
    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{if $data->getZipCode() && $data->getCity()}{$data->getZipCode()} {$data->getCity()}{else}&nbsp;{/if}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailZip}Postleitzahl und Ort{/s}</p>
    </div>
    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{if {config name=sepaShowBankName} && $data->getBankName()}{$data->getBankName()}{else}&nbsp;{/if}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailBankName}Kreditinstitut{/s}</p>
    </div>
    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{if {config name=sepaShowBic} && $data->getBic()}{$data->getBic()}{else}&nbsp;{/if}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailBic}BIC{/s}</p>
    </div>
    <div style="margin-top: 20px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0; font-weight: 700; font-size: 20px;">{$data->getIban()}</p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailIban}IBAN{/s}</p>
    </div>
    <div style="margin-top: 30px; margin-left: 30px;">
        <p style="width: 300px; border-bottom: solid 1px #000000; margin: 0;"></p>

        <p style="width: 300px; margin: 0 0 0 30px;">{s name=SepaEmailSignature}Datum, Ort und Unterschrift{/s}</p>
    </div>
</div>