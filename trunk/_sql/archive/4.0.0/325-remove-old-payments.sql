-- //

DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'ipayment';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'sofortueberweisung';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'ClickandBuy';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'Saferpay';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'moneybookers';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'clickpay_elv';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'clickpay_giropay';
DELETE FROM `s_core_paymentmeans` WHERE `s_core_paymentmeans`.`name` = 'clickpay_credit';


-- //@UNDO

--