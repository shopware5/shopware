-- //

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticSubmitFilter', 'Filtern', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticInfoEmpty', 'Keine Auswertung vorhanden', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelTimeUnit', 'KW', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelNetTurnover', 'Netto-Umsatz', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

-- //@UNDO

DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic' AND `name` = 'PartnerStatisticSubmitFilter';
DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic' AND `name` = 'PartnerStatisticInfoEmpty';
DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic' AND `name` = 'PartnerStatisticLabelTimeUnit';
DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic' AND `name` = 'PartnerStatisticLabelNetTurnover';

-- //
