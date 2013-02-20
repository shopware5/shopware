-- //

UPDATE `s_cms_support` SET `email_template` = 'Return - Shopware Demoshop

Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Comment:
{sVars.info}'
WHERE `name` LIKE "Return"
AND `email_template` LIKE "INSERT INTO s_user_service%";



UPDATE `s_cms_support` SET `email_template` = 'Defective product - Shopware Demoshop

Company: {sVars.firma}
Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Description of failure:
--------------------------------
{sVars.fehler}

Type: {sVars.rechner}
System {sVars.system}
How does the problem occur:
{sVars.wie}'
WHERE `name` LIKE "Defective product"
AND `email_template` LIKE "INSERT INTO s_user_service%";

-- //@UNDO

-- //
