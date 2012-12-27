UPDATE s_order_documents SET `type` = `type` + 1;
-- //@UNDO
UPDATE s_order_documents SET `type` = `type` - 1;