<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Test suite class for enlight test cases.
 *
 * The Enlight_Components_Test_TestSuite managed all implemented test cases.
 * It groups the test test cases and allows to add new test cases.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Test_TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Adds a test to the suite.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  array                  $groups
     * @return Enlight_Components_Test_TestSuite
     */
    public function addTest(PHPUnit_Framework_Test $test, $groups = array())
    {
        parent::addTest($test, $groups);

        return $this;
    }

    /**
     * Adds the tests from the given class to the suite.
     *
     * @param  mixed $testClass
     * @throws InvalidArgumentException
     * @return Enlight_Components_Test_TestSuite
     */
    public function addTestSuite($testClass)
    {
        if (is_string($testClass) && class_exists($testClass)) {
            $testClass = new ReflectionClass($testClass);
        }
        if ($testClass instanceof ReflectionClass && $testClass->isSubclassOf('PHPUnit_Framework_TestCase')) {
            $this->addTest(new self($testClass));
        } else {
            parent::addTestSuite($testClass);
        }

        return $this;
    }

    /**
     * Returns the test groups of the suite.
     *
     * @return array
     */
    public function getGroups()
    {
        $groups = parent::getGroups();
        if ($this->getName() && !class_exists($this->getName(), false)) {
            $groups[] = $this->getName();
        }
        return $groups;
    }
}
