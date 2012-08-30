-- //

INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/detail/comment', 1, 1, 'InquiryTextArticle', 'Ich habe folgende Fragen zum Artikel', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- //@UNDO

DELETE FROM `s_core_snippets` WHERE namespace = 'frontend/detail/comment' AND `name` = 'InquiryTextArticle';

-- //
