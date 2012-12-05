INSERT INTO s_core_snippets (namespace, shopID, localeID, name, value) VALUES
('frontend/detail/description', 1, 1, 'DetailDescriptionSupplier', 'Hersteller-Beschreibung'),
('frontend/detail/description', 1, 2, 'DetailDescriptionSupplier', 'Supplier description');

-- //@UNDO

DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/detail/description' AND name = 'DetailDescriptionSupplier';