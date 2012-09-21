<?php
$backupTables = array(
    's_core_config',
    's_articles',
    's_core_plugin_configs',
    's_core_plugin_elements',
    's_user_billingaddress',
    's_user_shippingaddress',
    's_order',
    's_order_details',
    's_order_billingaddress',
    's_order_shippingaddress',
    's_order_basket',
    's_core_plugins',
    's_core_subscribes',
    's_core_menu',
    's_core_countries',
    's_core_snippets',
    's_filter_values'
);
$mapping = array(
    's_addon_premiums' => array(
        'articleID' => 'ordernumber_export'
    ),
    's_core_log' => array(
        'datum' => 'date'
    ),
    's_core_engine_elements' => array(
        'domvalue' => 'default',
        'domvtype' => 'type',
        'domdescription' => 'label',
        'databasefield' => 'name',
        'domclass' => 'layout',
        'availablebyvariants' => 'variantable',
        'multilanguage' => 'translatable'
    )
);
$app->contentType('Content-type: text/plain; charset=utf-8');
echo "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n";
echo "ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n\n";
$export = new Shopware_Components_DbDiff_Mysql(
    $app->config('source'),
    $app->config('db')
);
$tables = $export->listTables();
foreach($tables as $table) {
    echo $export->getTableUpdate($table, array(
        'backup' => in_array($table, $backupTables),
        'mapping' => isset($mapping[$table]) ? $mapping[$table] : null
    ));
}