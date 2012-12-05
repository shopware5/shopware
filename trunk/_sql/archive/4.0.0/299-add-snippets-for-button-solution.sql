-- //

UPDATE  `s_core_snippets` SET  `value` =  '<h3 class="underline">Widerrufsbelehrung</h3><p>Bitte beachten Sie bei Ihrer Bestellung auch unsere <a href="{url controller=custom sCustom=8 forceSecure}" data-modal-height="500" data-modal-width="800">Widerrufsbelehrung</a>.</p>' WHERE `name` = 'ConfirmTextRightOfRevocation' AND `localeID` = 1;

UPDATE  `s_core_snippets` SET  `value` =  'Zahlungspflichtig bestellen' WHERE `name` = 'ConfirmDoPayment' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Zahlungspflichtig bestellen' WHERE `name` = 'ConfirmActionSubmit' AND `localeID` = 1;

-- //@UNDO

UPDATE  `s_core_snippets` SET  `value` =  'Informationen zum Widerrufsrecht [Füllen / Textbaustein]' WHERE `name` = 'ConfirmTextRightOfRevocation' AND `localeID` = 1;

UPDATE  `s_core_snippets` SET  `value` =  'Zahlung durchführen' WHERE `name` = 'ConfirmDoPayment' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Zahlung durchführen' WHERE `name` = 'ConfirmActionSubmit' AND `localeID` = 1;

--