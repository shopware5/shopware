-- //

UPDATE `s_core_snippets` SET value='Zu viele fehlgeschlagene Versuche. Ihr Account wurde vorübergehend deaktivert - bitte probieren Sie es in einigen Minuten erneut!' WHERE name='LoginFailureLocked' AND localeID = 1;

-- //@UNDO

-- //