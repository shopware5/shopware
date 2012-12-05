-- //

INSERT INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(80, 'Checkout', 'Bestellabschluss', NULL, 0, 1, NULL);

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Checkout');
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'revocationNotice', 'b:1;', 'Zeige Widerrufsbelehrung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'newsletter', 'b:0;', 'Zeige Newsletter-Registrierung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'bankConnection', 'b:0;', 'Zeige Bankverbindungshinweis an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'additionalFreeText', 'b:0;', 'Zeige weiteren Hinweis an', 'Snippet: ConfirmTextOrderDefault', 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'commentVoucherArticle', 'b:0;', 'Zeige weitere Optionen an', '(Artikel hinzufügen, Gutschein hinzufügen, Kommentarfunktion)', 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'bonusSystem', 'b:0;', 'Zeige Bonus-System an (falls installiert)', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'premiumArticles', 'b:0;', 'Zeige Prämienartikel an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'countryNotice', 'b:1;', 'Zeige Länder-Beschreibung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'nettoNotice', 'b:0;', 'Zeige Hinweis für Netto-Bestellungen an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'basketHeaderColor', 's:7:"#dd4800";', 'Warenkorbkopf Hintergrundfarbe', '(Hex-Code)', 'text', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'basketHeaderFontColor', 's:4:"#fff";', 'Warenkorbkopf Textfarbe', '(Hex-Code)', 'text', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'basketTableColor', 's:7:"#ebebeb";', 'Warenkorbtabelle Hintergrundfarbe', '(Hex-Code)', 'text', 0, 0, 1, NULL, NULL, 'a:0:{}'),
(@parent, 'mainFeatures', 's:290:"{if $sBasketItem.additional_details.properties}\n    {$sBasketItem.additional_details.properties}\n{elseif $sBasketItem.additional_details.description}\n    {$sBasketItem.additional_details.description}\n{else}\n    {$sBasketItem.additional_details.description_long|strip_tags|truncate:50}\n{/if}";', 'Template für die wesentliche Merkmale', 'Die Smarty-Variable $sBasketItem ist der jeweilige Artikel.', 'textarea', 0, 0, 1, NULL, NULL, 'a:0:{}');

-- //@UNDO

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Checkout');
DELETE FROM s_core_config_elements WHERE form_id = @parent;
DELETE FROM s_core_config_forms WHERE id = @parent;

--