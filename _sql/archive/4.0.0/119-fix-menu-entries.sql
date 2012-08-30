UPDATE s_core_menu SET controller='Detail' WHERE name='Anlegen';
UPDATE s_core_menu SET class='sprite-edit-shade' WHERE name = 'Textbausteine';
UPDATE s_core_menu SET class='sprite-balloons-box' WHERE name = 'Zum Forum';
UPDATE s_core_menu SET class='sprite-funnel--exclamation' WHERE name = 'Riskmanagement*';
-- //@UNDO

UPDATE s_core_menu SET controller='Article' WHERE name='Anlegen';
UPDATE s_core_menu SET class='edit-shade' WHERE name = 'Textbausteine';
UPDATE s_core_menu SET class='balloons-box' WHERE name = 'Zum Forum';
UPDATE s_core_menu SET class='funnel--exclamation' WHERE name = 'Riskmanagement*';