-- replace attr6 with ean //

UPDATE
	`s_export`
SET
	`body` = REPLACE(`body`, '$sArticle.attr6', '$sArticle.ean')
WHERE
	`last_export` LIKE '2000%';


-- //@UNDO

-- //
