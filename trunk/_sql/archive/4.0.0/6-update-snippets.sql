-- //

/**
 * @author Heiner Lohaus
 * @since 4.0.0 - 2012/01/30
 */
UPDATE `s_core_snippets`
SET value=REPLACE(value, '\')', '')
WHERE value LIKE '%$this%';
UPDATE `s_core_snippets`
SET value=REPLACE(value, '$this->config(\'', 'config name=')
WHERE value LIKE '%$this%';
UPDATE `s_core_snippets`
SET value=REPLACE(value, 'config name=sARTICLESOUTPUTNETTO == true', '{config name=articlesOutputNetto}')
WHERE value LIKE '%sARTICLESOUTPUTNETTO%';

-- //@UNDO


-- //