INSERT IGNORE INTO `s_core_templates` (`template`, `name`, `description`, `author`, `license`, `version`)
SELECT
  SUBSTRING_INDEX(template, '/', -1) as template,
  CONCAT(UCASE(SUBSTRING(template, 11, 1)),LCASE(SUBSTRING(template, 12))) as name,
  NULL, 'shopware AG', 'New BSD', 1
FROM backup_s_core_multilanguage
GROUP BY template;

TRUNCATE TABLE `s_core_shops`;
REPLACE INTO `s_core_shops` (`id`, `main_id`, `name`, `host`, `hosts`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `default`, `fallback_id`, `active`)
SELECT
	id,
	(
	  SELECT IF(id=m.id, NULL, id) as id FROM backup_s_core_multilanguage
	  WHERE CONCAT('|', switchLanguages, '|') LIKE CONCAT('%|', m.id, '|%')
	  ORDER BY `default` DESC, id LIMIT 1
	) as main_id,
	name,
	IF(`domainaliase`='', NULL, TRIM(SUBSTRING_INDEX(`domainaliase`, '\n', 1))) as host,
	IF(`domainaliase`='', NULL, domainaliase) as hosts,
	IFNULL((SELECT id FROM s_core_templates WHERE m.template LIKE CONCAT('%/', template)), 11) as `template_id`,
    IFNULL((SELECT id FROM s_core_templates WHERE m.doc_template LIKE CONCAT('%/', template)), 11) as `document_template_id`,
	parentID as `category_id`,
	locale as `locale_id`,
	defaultcurrency as `currency_id`,
	(SELECT id FROM s_core_customergroups WHERE groupkey=defaultcustomergroup) as `customer_group_id`,
	m.default,
    (SELECT id FROM backup_s_core_multilanguage WHERE isocode=m.fallback) as `fallback_id`,
    1 as active
FROM backup_s_core_multilanguage m;
UPDATE s_core_shops SET base_path = NULL, secure_base_path = NULL;
UPDATE s_core_shops SET host = NULL, hosts = NULL WHERE main_id IS NOT NULL;

TRUNCATE s_core_shop_currencies;
INSERT INTO s_core_shop_currencies
SELECT m.id, c.id
FROM s_core_multilanguage m
JOIN s_core_currencies c
ON c.id = m.switchCurrencies
OR m.switchCurrencies LIKE CONCAT(c.id, '|%')
OR m.switchCurrencies LIKE CONCAT('%|', c.id)
OR m.switchCurrencies LIKE CONCAT('%|', c.id, '|%');

UPDATE s_core_translations t, backup_s_core_multilanguage m
SET t.objectlanguage=m.id
WHERE t.objectlanguage=m.isocode;

UPDATE s_order o, backup_s_core_multilanguage m
SET o.language=m.id
WHERE o.language=m.isocode;

UPDATE s_user u, backup_s_core_multilanguage m
SET u.language=m.id
WHERE u.language=m.isocode;

UPDATE s_core_multilanguage m
SET m.isocode=m.id;

UPDATE `s_core_shops` SET `default` = IF(`id`=1, 1, 0);
SET @value = (SELECT `value` FROM `backup_s_core_config` WHERE `name` LIKE 'sHOST');
UPDATE `s_core_shops` SET `host` = TRIM(@value) WHERE `default`=1;
SET @value = (SELECT REPLACE(`value`, @value, '') FROM `backup_s_core_config` WHERE `name` LIKE 'sBASEPATH');
UPDATE `s_core_shops` SET `base_path` = TRIM(@value) WHERE `base_path` IS NULL AND `main_id` IS NULL;
UPDATE `s_core_shops` SET `base_path` = NULL WHERE `base_path` = '';
SET @value = (SELECT `value` FROM `backup_s_core_config` WHERE `name` LIKE 'sUSESSL');
UPDATE `s_core_shops` SET `secure` = @value WHERE `default` = 1;

TRUNCATE s_core_multilanguage;
INSERT IGNORE INTO `s_core_multilanguage` (
  `id`, `isocode`, `locale`, `parentID`, `skipbackend`,
  `name`, `defaultcustomergroup`, `template`, `doc_template`,
  `domainaliase`,
  `switchCurrencies`, `switchLanguages`,
  `defaultcurrency`, `default`,
  `fallback`
)
SELECT
  s.id, s.id as isocode, s.locale_id, s.currency_id, s.default as skipbackend, s.name,
  (SELECT groupkey FROM s_core_customergroups WHERE id=s.customer_group_id) as defaultcustomergroup,
  (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id=m.template_id) as template,
  (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id=m.document_template_id) as doc_template,
  CONCAT(s.host, '\n', s.hosts) as hosts,
  GROUP_CONCAT(d.currency_id SEPARATOR '|') as switchCurrencies,
  (SELECT GROUP_CONCAT(id SEPARATOR '|') FROM s_core_shops WHERE id=m.id OR main_id=m.id)  as switchLanguages,
  s.currency_id, s.default, s.fallback_id
FROM `s_core_shops` s
LEFT JOIN `s_core_shops` m
ON m.id=s.main_id
OR (s.main_id IS NULL AND m.id=s.id)
LEFT JOIN `s_core_shop_currencies` d
ON d.shop_id=m.id
WHERE s.active=1
GROUP BY s.id;
