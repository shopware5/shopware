--  //

UPDATE `s_core_snippets`
SET `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten. Eventuell wurde Ihre eMail-Adresse bereits validiert."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.";


UPDATE `s_core_snippets`
SET `value` = "An error has occurred while validating your e-mail address. Possibly your email address has already been validated."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "An error has occured while validating your e-mail address.";

-- //@UNDO

-- //

