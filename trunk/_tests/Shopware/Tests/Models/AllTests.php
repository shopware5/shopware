<?php
class Shopware_Tests_Models_AllTests
{
   public static function suite()
    {
        $suite = new Enlight_Components_Test_TestSuite('Shopware Models');

        $suite->addTestFiles(glob(dirname(__FILE__) . '/*Test.php'));

		return $suite;
    }
}