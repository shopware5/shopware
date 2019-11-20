DELETE FROM s_campaigns_mailings;
DELETE FROM s_campaigns_mailaddresses;

INSERT INTO s_campaigns_mailings (id, datum, `groups`, subject, sendermail, sendername, plaintext, templateID, languageID, status, locked, recipients, `read`, clicked, customergroup, publish) VALUES('1', '0000-00-00', 'a:2:{i:0;a:2:{s:2:\"EK\";s:1:\"2\";s:1:\"H\";s:1:\"0\";}i:1;a:2:{i:1;s:1:\"3\";i:2;s:1:\"0\";}}', 'Shopware 3.5.0 Demoshop', 'info@example.com', 'Newsletter Absender', '0', '1', '1', '1', '2010-10-16 15:23:23', '5', '0', '0', 'EK', '1');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('35', '0', '1', 'test0@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('71', '1', '0', 'test1@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('72', '1', '0', 'test2@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('70', '1', '0', 'test3@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('69', '1', '0', 'test4@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('65', '1', '0', 'test5@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('66', '1', '0', 'test6@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('67', '0', '1', 'test7@example.com', '0', '0');
INSERT INTO s_campaigns_mailaddresses (id, customer, groupID, email, lastmailing, lastread) VALUES('68', '0', '1', 'test8@example.com', '0', '0');
