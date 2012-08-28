-- //

UPDATE `s_core_config_elements`
SET `label` = 'Sets the Encoding of the message',
`description` = 'Options for this are: "8bit", "7bit", "binary", "base64" and "quoted-printable".'
WHERE `s_core_config_elements`.`id` =234 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Method to send the mail',
`description` = 'Options for this are: "mail", "smtp" and "file"'
WHERE `s_core_config_elements`.`id` =235 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Hostname to use in the Message-Id',
`description` = 'Will be Received in headers. On default a HELO string. If empty, the value returned from SERVER_NAME is used or "localhost.localdomain".'
WHERE `s_core_config_elements`.`id` =236 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Mail host',
`description` = 'You can also specify a different port by using this format: [hostname:port] (e.g. "smtp1.example.com:25").'
WHERE `s_core_config_elements`.`id` =237 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Default Port',
`description` = 'Sets the default SMTP server port.'
WHERE `s_core_config_elements`.`id` =238 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Sets connection prefix.',
`description` = 'Options are: "", "ssl" or "tls"'
WHERE `s_core_config_elements`.`id` =239 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'SMTP username',
`description` = NULL
WHERE `s_core_config_elements`.`id` =240 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'SMTP password',
`description` = NULL
WHERE `s_core_config_elements`.`id` =241 LIMIT 1;

UPDATE `s_core_config_elements`
SET `label` = 'Connection auth',
`description` = 'Options are: "", "plain",  "login" or "crammd5"'
WHERE `s_core_config_elements`.`id` =242 LIMIT 1;

-- //@UNDO

-- //