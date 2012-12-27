-- Fixes website-field name and removes unused subject-field reference. //

UPDATE `s_cms_support` SET `email_template` = 'Partneranfrage - {$sShopname}
{sVars.firma} moechte Partner Ihres Shops werden!

Firma: {sVars.firma}
Ansprechpartner: {sVars.ansprechpartner}
Straße/Hausnr.: {sVars.strasse}
PLZ / Ort: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Telefon: {sVars.tel}
Fax: {sVars.fax}
Webseite: {sVars.website}

Kommentar:
{sVars.kommentar}

Profil:
{sVars.profil}' WHERE name = 'Partnerformular' AND MD5(s_cms_support.email_template) = 'b24502c9de57c8777a638190d52c18d5';

UPDATE `s_cms_support` SET `email_template` = 'Partner inquiry - {$sShopname}
{sVars.firma} want to become your partner!

Company: {sVars.firma}
Contact person: {sVars.ansprechpartner}
Street / No.: {sVars.strasse}
Postal Code / City: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Phone: {sVars.tel}
Fax: {sVars.fax}
Website: {sVars.website}

Comment:
{sVars.kommentar}

Profile:
{sVars.profil}' WHERE name = 'Partner form' AND MD5(s_cms_support.email_template) = 'a179ec3e50b3135baab41f9badbd259a';

-- //@UNDO


-- //
