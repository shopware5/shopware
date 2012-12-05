UPDATE s_core_menu SET onclick='', name='Riskmanagement', controller='RiskManagement' WHERE name='Riskmanagement*';

-- //@UNDO

UPDATE s_core_menu SET onclick='loadSkeleton(''risk'')', name='Riskmanagement*', controller='Riskmanagement' WHERE name='Riskmanagement';

-- //