DELETE FROM s_core_menu WHERE name="ClickPay Bonitätsüberprüfung*";

-- //@UNDO

SET @parent = (SELECT id FROM s_core_menu WHERE name='Kunden');
INSERT INTO s_core_menu ('parent', 'name', 'onclick', 'class', 'position', 'active') VALUES (@parent, 'ClickPay Bonitätsüberprüfung*', 'loadSkeleton(''clickpay_rating'');', 'ico2 date2', 0, 1);