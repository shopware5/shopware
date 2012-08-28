UPDATE `s_core_snippets` SET `value` = 'Bitte geben Sie eine g&uuml;ltige eMail-Adresse ein.' WHERE `name` = 'RegisterAjaxEmailNotValid' AND  namespace = 'frontend' AND localeID = '1';
UPDATE `s_core_snippets` SET `value` = 'Bitte w&auml;hlen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.' WHERE `name` = 'RegisterPasswordLength' AND  namespace = 'frontend' AND localeID = '1';
UPDATE `s_core_snippets` SET `value` = 'Die Passw&ouml;rter stimmen nicht &uuml;berein.' WHERE `name` = 'RegisterPasswordNotEqual' AND  namespace = 'frontend' AND localeID = '1';

-- //@UNDO

UPDATE `s_core_snippets` SET `value` = 'Bitte geben Sie eine gültige eMail-Adresse ein.' WHERE `name` = 'RegisterAjaxEmailNotValid' AND  namespace = 'frontend' AND localeID = '1';
UPDATE `s_core_snippets` SET `value` = 'Bitte wählen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.' WHERE `name` = 'RegisterPasswordLength' AND  namespace = 'frontend' AND localeID = '1';
UPDATE `s_core_snippets` SET `value` = 'Die Passwörter stimmen nicht überein.' WHERE `name` = 'RegisterPasswordNotEqual' AND  namespace = 'frontend' AND localeID = '1';