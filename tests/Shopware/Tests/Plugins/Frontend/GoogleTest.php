<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Plugins_Frontend_GoogleTest extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * @var Shopware_Plugins_Frontend_Google_Bootstrap
     */
    protected $plugin;

    /**
     * Test set up method
     */
    public function setUp()
    {
        parent::setUp();

        $this->plugin = Shopware()->Plugins()->Frontend()->Google();
    }

    /**
     * Retrieve plugin instance
     *
     * @return Shopware_Plugins_Frontend_Statistics_Bootstrap
     */
    public function Plugin()
    {
        return $this->plugin;
    }

    /**
     * Test case method
     *
     * @ticket 5268
     */
    public function testPostDispatch()
    {
        $request = $this->Request()
            ->setModuleName('frontend')
            ->setDispatched(true);

        $response = $this->Response();

        $this->Plugin()->Config()
            ->setAllowModifications()
            ->set('tracking_code', 'TEST1234')
            ->set('anonymize_ip', true);

        $view = new Enlight_View_Default(
            Shopware()->Template()
        );
        $view->loadTemplate('frontend/index/index.tpl');

        $action = $this->getMock('Enlight_Controller_Action',
            null,
            array($request, $response)
        );

        $action->setView($view);

        $eventArgs = $this->createEventArgs()
            ->setSubject($action);

        $e = null;
           try {
            $this->Plugin()->onPostDispatch($eventArgs);
           } catch (Exception $e) { }

           $this->assertNull($e);
           $this->assertEquals('TEST1234', $view->GoogleTrackingID);
        $this->assertTrue($view->GoogleAnonymizeIp);

        $this->assertContains(
            'frontend/plugins/google/index.tpl',
            $view->Template()->getTemplateResource()
        );
    }
}
