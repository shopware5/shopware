UPDATE s_user_shippingaddress SET countryID = NULL WHERE countryID = 0;
UPDATE `s_user_billingaddress` SET `stateID` = NULL WHERE `stateID` = 0;
UPDATE `s_user_shippingaddress` SET `stateID` = NULL WHERE `stateID` = 0;

INSERT INTO s_user_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6)
SELECT id,
  IF(text1='', NULL, text1),
  IF(text2='', NULL, text2),
  IF(text3='', NULL, text3),
  IF(text4='', NULL, text4),
  IF(text5='', NULL, text5),
  IF(text6='', NULL, text6)
FROM `backup_s_user_billingaddress`;

INSERT INTO s_user_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6)
SELECT id,
  IF(text1='', NULL, text1),
  IF(text2='', NULL, text2),
  IF(text3='', NULL, text3),
  IF(text4='', NULL, text4),
  IF(text5='', NULL, text5),
  IF(text6='', NULL, text6)
FROM `backup_s_user_shippingaddress`;

UPDATE s_user SET lockeduntil = NULL WHERE lockeduntil = '0000-00-00 00:00:00';
UPDATE s_user_billingaddress SET birthday = NULL WHERE birthday = '0000-00-00 00:00:00';

