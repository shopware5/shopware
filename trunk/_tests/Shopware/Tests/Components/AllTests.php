<?php
class Shopware_Tests_Components_AllTests
{
   public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Shopware Components');

        $suite->addTestFiles(glob(dirname(__FILE__) . '/*Test.php'));
        $suite->addTestFiles(glob(dirname(__FILE__) . '/Api/*Test.php'));

        return $suite;
    }
}
