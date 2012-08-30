INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/internalMessages', 1, 1, 'LoginFailureLocked', 'Zu viele fehlgeschlagene Versuche. Ihr Account wurde vor?bergehend deaktivert - bitte probieren Sie es in einigen Minuten erneut!', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'LoginFailureActive', 'Ihr Kundenkonto wurde gesperrt. Bitte nehmen Sie Kontakt mit uns auf.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'LoginFailure', 'Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/internalMessages', 1, 1, 'ErrorFillIn', 'Bitte füllen Sie alle rot markierten Felder aus', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureNotFound', 'Diese eMail-Adresse wurde nicht gefunden', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterMailDeleted', 'Ihre eMail-Adresse wurde gelöscht', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterSuccess', 'Vielen Dank. Wir haben Ihre Adresse eingetragen.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureAlreadyRegistered', 'Sie erhalten unseren Newsletter bereits', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'UnknownError', 'Ein unbekannter Fehler ist aufgetreten', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureInvalid', 'Bitte geben Sie eine gültige eMail-Adresse ein', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureMail', 'Bitte geben sie eine eMail-Adresse an', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureDate', 'Die eingegebene USt-IdNr. ist ungültig. Sie ist erst ab dem %s gültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureUnknownError', 'Es ist ein unerwarteter Fehler bei der Überprüfung der USt-IdNr. aufgetreten. Bitte kontaktieren Sie den Shopbetreiber. Fehlercode: %d', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureErrorField', 'Das Feld %s passt nicht zur USt-IdNr.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureErrorFields', 'Firma,Ort,PLZ,Straße,Land', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureInvalid', 'Die eingegebene USt-IdNr. ist ungültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'VatFailureEmpty', 'Bitte geben Sie eine USt-IdNr. an.', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/internalMessages', 1, 1, 'MailFailureNotEqual', 'Die eMail-Adressen stimmen nicht überein.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'MailFailure', 'Bitte geben Sie eine gültige eMail-Adresse ein', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/account/internalMessages', 1, 1, 'MailFailureAlreadyRegistered', 'Diese eMail-Adresse ist bereits registriert', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- //@UNDO