-- //

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/content_right', 1, 1, 'AccountLinkPartnerStatistic', 'Provisionen', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticHeader', 'Provisions Übersicht', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelFromDate', 'Von:', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelToDate', 'Bis:', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticSubmitFilter', 'Filtern:', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnDate', 'Datum', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnId', 'Bestellnummer', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnNetAmount', 'Netto Umsatz', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnProvision', 'Provision', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic', 1, 1, 'Provisions', 'Provisionen', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/partner_statistic_item', 1, 1, 'PartnerStatisticItemSum', 'Gesamtsumme:', '2012-06-22 12:59:53', '2012-06-25 16:54:02');

-- //@UNDO

DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic';
DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/partner_statistic_item';
DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/account/content_right' AND `name` = 'AccountLinkPartnerStatistic';

-- //
