UPDATE s_order SET cleareddate = NULL WHERE cleareddate = '0000-00-00 00:00:00';
UPDATE `s_order_shippingaddress` SET userID = NULL WHERE userID = 0;
UPDATE `s_order_billingaddress` SET userID = NULL WHERE userID = 0;

INSERT INTO `s_order_attributes` (orderID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
SELECT id,
  IF(o_attr1='', NULL, o_attr1),
  IF(o_attr2='', NULL, o_attr2),
  IF(o_attr3='', NULL, o_attr3),
  IF(o_attr4='', NULL, o_attr4),
  IF(o_attr5='', NULL, o_attr5),
  IF(o_attr6='', NULL, o_attr6)
FROM `backup_s_order`;

INSERT INTO s_order_details_attributes (detailID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
SELECT id,
  IF(od_attr1='', NULL, od_attr1),
  IF(od_attr2='', NULL, od_attr2),
  IF(od_attr3='', NULL, od_attr3),
  IF(od_attr4='', NULL, od_attr4),
  IF(od_attr5='', NULL, od_attr5),
  IF(od_attr6='', NULL, od_attr6)
FROM `backup_s_order_details`;

INSERT INTO `s_order_basket_attributes` (basketID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
SELECT id,
  IF(ob_attr1='', NULL, ob_attr1),
  IF(ob_attr2='', NULL, ob_attr2),
  IF(ob_attr3='', NULL, ob_attr3),
  IF(ob_attr4='', NULL, ob_attr4),
  IF(ob_attr5='', NULL, ob_attr5),
  IF(ob_attr6='', NULL, ob_attr6)
FROM `backup_s_order_basket`;

INSERT INTO s_order_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6)
SELECT id,
  IF(text1='', NULL, text1),
  IF(text2='', NULL, text2),
  IF(text3='', NULL, text3),
  IF(text4='', NULL, text4),
  IF(text5='', NULL, text5),
  IF(text6='', NULL, text6)
FROM `backup_s_order_billingaddress`;

INSERT INTO s_order_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6)
SELECT id,
  IF(text1='', NULL, text1),
  IF(text2='', NULL, text2),
  IF(text3='', NULL, text3),
  IF(text4='', NULL, text4),
  IF(text5='', NULL, text5),
  IF(text6='', NULL, text6)
FROM `backup_s_order_shippingaddress`;

UPDATE `s_order_documents` SET `type` = '1' WHERE `type` ='0';
