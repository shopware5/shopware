-- //

UPDATE  `s_core_snippets` SET  `value` =  'Kostenlos' WHERE  name='CartItemInfoFree' AND namespace='frontend/checkout/finish_item';

-- //@UNDO

UPDATE  `s_core_snippets` SET  `value` =  '' WHERE  name='CartItemInfoFree' AND namespace='frontend/checkout/finish_item';

-- //