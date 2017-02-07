<?php

if (class_exists('PHPUnit\DbUnit\TestCase')) {
    return;
}

class_alias('PHPUnit_Extensions_Database_TestCase', 'PHPUnit\DbUnit\TestCase');
class_alias('PHPUnit_Extensions_Database_DataSet_ReplacementDataSet', 'PHPUnit\DbUnit\DataSet\ReplacementDataSet');
class_alias('PHPUnit_Extensions_Database_AbstractTester', 'PHPUnit\DbUnit\AbstractTester');
class_alias('PHPUnit_Extensions_Database_DefaultTester', 'PHPUnit\DbUnit\DefaultTester');
class_alias('PHPUnit_Extensions_Database_DataSet_XmlDataSet', 'PHPUnit\DbUnit\DataSet\XmlDataSet');
class_alias('PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection', 'PHPUnit\DbUnit\Database\DefaultConnection');
