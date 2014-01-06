<?php
class Migrations_Migration209 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
        ALTER TABLE  `s_core_payment_data`
            ADD  `account_number` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `iban` ,
            ADD  `bank_code` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `account_number` ,
            ADD  `account_holder` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `bank_code` ;

        UPDATE s_core_snippets
        SET namespace = 'frontend/plugins/payment/sepa'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa';

        UPDATE s_core_snippets
        SET namespace = 'frontend/plugins/payment/debit'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit';

        UPDATE s_core_snippets
        SET namespace = 'frontend/plugins/payment/sepaemail'
        WHERE namespace = 'engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/sepa/email';
EOD;
        $this->addSql($sql);
    }
}