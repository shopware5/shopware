<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 * @author    Heiner Lohaus
 */
class Shopware_RegressionTests_Ticket5302Test extends Enlight_Components_Test_Controller_TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $app = TestHelper::Instance();

        $request = $this->Request();
        $request->setPathInfo(null);
        $this->Front()
            ->setRequest($request);

        //$this->Template()->force_compile = true;
        $this->Template()->addTemplateDir(
            $app->TestPath('DataSets'),
            'Ticket5302'
        );
    }

    /**
     * @return array
     */
    public function getLinkTestData()
    {
        $request = $this->Request();
        return array(
            array(
                '/templates/_emotion/frontend/_resources/favicon.ico',
                'string:' .
                    '{link file="frontend/_resources/favicon.ico"}'
            ),
            array(
                '/templates/_emotion/frontend/_resources/favicon.ico',
                'Ticket5302/test1.tpl'
            ),
            array(
                '/templates/_emotion/frontend/_resources/favicon.ico',
                'Ticket5302/test2.tpl'
            ),
            array(
                "{$request->getScheme()}://{$request->getHttpHost()}{$request->getBasePath()}" .
                    '/templates/_emotion/frontend/_resources/favicon.ico',
                'string:' .
                    '{link file="frontend/_resources/favicon.ico" fullPath}'
            ),
            array(
                "{$request->getScheme()}://{$request->getHttpHost()}{$request->getBasePath()}/",
                'string:' .
                    '{link file="" fullPath}'
            ),
        );
    }

    /**
     * Test case method
     * @dataProvider getLinkTestData
     */
    public function testTemplateLinkPlugin($excepted, $template)
    {
        $manager = $this->Template();

        $result = $manager->fetch($template);
        $this->assertEquals($excepted, $result);

        // tests it cached
        $result = $manager->fetch($template);
        $this->assertEquals($excepted, $result);
    }
}
