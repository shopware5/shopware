
UPDATE `s_core_snippets`
SET `value` = "zzgl. {$key}% MwSt:"
WHERE `name` LIKE 'DocumentIndexTax'
AND `value` LIKE "zzgl. {$key} MwSt:";

UPDATE `s_core_snippets`
SET `value` = "Plus {$key}% VAT:"
WHERE `name` LIKE 'DocumentIndexTax'
AND `value` LIKE "Plus {$key} VAT:";

-- //@UNDO

-- //

