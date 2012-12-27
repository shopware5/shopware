-- //

UPDATE `s_core_config_mails` SET `context` = 'a:4:{s:5:"sName";s:11:"Peter Meyer";s:8:"sArticle";s:10:"Blumenvase";s:5:"sLink";s:31:"http://shop.example.org/test123";s:8:"sComment";s:36:"Hey Peter - das musst du dir ansehen";}'
WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND';

UPDATE `s_core_config_mails` SET 
`context` = 'a:2:{s:12:"sArticleName";s:20:"ESD Download Artikel";s:5:"sMail";s:23:"max.mustermann@mail.com";}'
WHERE `s_core_config_mails`.`name` = 'sNOSERIALS';


UPDATE `s_core_config_mails` SET 
`context`= 'a:2:{s:9:"sPassword";s:7:"xFqr3zp";s:5:"sMail";s:18:"nutzer@example.org";}'
WHERE `s_core_config_mails`.`name` = 'sPASSWORD';


UPDATE `s_core_config_mails`  SET `context` = 'a:30:{s:5:"sShop";s:7:"Deutsch";s:8:"sShopURL";s:27:"http://trunk.qa.shopware.in";s:7:"sConfig";a:0:{}s:5:"sMAIL";s:14:"xy@example.org";s:7:"country";s:1:"2";s:13:"customer_type";s:7:"private";s:10:"salutation";s:4:"Herr";s:9:"firstname";s:8:"Banjimen";s:8:"lastname";s:6:"Ercmer";s:5:"phone";s:8:"55555555";s:3:"fax";N;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;s:11:"sValidation";N;s:9:"birthyear";s:0:"";s:10:"birthmonth";s:0:"";s:8:"birthday";s:0:"";s:11:"dpacheckbox";N;s:7:"company";s:0:"";s:6:"street";s:14:"Musterstreaße";s:12:"streetnumber";s:2:"55";s:7:"zipcode";s:5:"55555";s:4:"city";s:11:"Musterhsuen";s:10:"department";s:0:"";s:15:"shippingAddress";N;s:7:"stateID";N;}'
WHERE `s_core_config_mails`.`name` = 'sREGISTERCONFIRMATION';

UPDATE `s_core_config_mails` SET 
`context`= 'a:2:{s:8:"customer";s:11:"Peter Meyer";s:4:"user";s:11:"Hans Maiser";}'
WHERE `s_core_config_mails`.`name` = 'sVOUCHER';

-- //@UNDO

-- //

