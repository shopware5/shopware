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
class Shopware_RegressionTests_Ticket4747 extends Enlight_Components_Test_Controller_TestCase
{
    public function testIndulgenceWorldsFilters()
    {
        $properties = Shopware()->Modules()->Articles()->sGetCategoryProperties(14);

        $groups = $properties['filterOptions']['grouped'];
        $this->assertArrayKeys($groups, array(
            array('index' => 0, 'value' => 'Edelbrände', 'message' => 'First group key do not match')
        ));

        $options = $groups['Edelbrände']['options'];
        $this->assertCount(5, $options, 'Option count of edelbrände do not match');

        //check position of "Trinktemperatur" value positions
        $this->assertArrayKeys($options, array(
            array('index' => 0, 'value' => 'Trinktemperatur',   'message' => 'Option Trinktemperatur has wrong position'),
            array('index' => 1, 'value' => 'Geschmack',         'message' => 'Option Geschmack has wrong position'),
            array('index' => 2, 'value' => 'Farbe',             'message' => 'Option Farbe has wrong position'),
            array('index' => 3, 'value' => 'Flaschengröße',     'message' => 'Option Flaschengröße has wrong position'),
            array('index' => 4, 'value' => 'Alkoholgehalt',     'message' => 'Option Alkoholgehalt has wrong position')
        ));

        //check position of "Trinktemperatur" value positions
        $this->assertArrayKeys($options['Trinktemperatur'], array(
            array('index' => 0, 'value' => 'Gekühlt',          'message' => 'Option "Trinktemperatur > Gekühlt" has wrong position'),
            array('index' => 1, 'value' => 'Zimmertemperatur', 'message' => 'Option "Trinktemperatur > Zimmertemperatur" has wrong position')
        ));

        //check position of "Geschmack" value positions
        $this->assertArrayKeys($options['Geschmack'], array(
            array('index' => 0, 'value' => 'mild',     'message' => 'Option "Geschmack > mild" has wrong position'),
            array('index' => 1, 'value' => 'herb',     'message' => 'Option "Geschmack > herb" has wrong position'),
            array('index' => 2, 'value' => 'fruchtig', 'message' => 'Option "Geschmack > fruchtig" has wrong position')
        ));

        //check position of "Farbe" value positions
        $this->assertArrayKeys($options['Farbe'], array(
            array('index' => 0, 'value' => 'klar',        'message' => 'Option "Farbe > klar" has wrong position'),
            array('index' => 1, 'value' => 'goldig',      'message' => 'Option "Farbe > goldig" has wrong position'),
            array('index' => 2, 'value' => 'rot',         'message' => 'Option "Farbe > rot" has wrong position'),
            array('index' => 3, 'value' => 'schokobraun', 'message' => 'Option "Farbe > schokobraun" has wrong position')
        ));

        //check position of "Flaschengröße" value positions
        $this->assertArrayKeys($options['Flaschengröße'], array(
            array('index' => 0, 'value' => '0,7 Liter',  'message' => 'Option "Flaschengröße > 0,7 Liter" has wrong position'),
            array('index' => 1, 'value' => '0,5 Liter',  'message' => 'Option "Flaschengröße > 0,5 Liter" has wrong position'),
            array('index' => 2, 'value' => '0,2 Liter',  'message' => 'Option "Flaschengröße > 0,2 Liter" has wrong position'),
            array('index' => 3, 'value' => '1,0 Liter',  'message' => 'Option "Flaschengröße > 1,0 Liter" has wrong position'),
            array('index' => 4, 'value' => '1,5 Liter',  'message' => 'Option "Flaschengröße > 1,5 Liter" has wrong position'),
            array('index' => 5, 'value' => '5,0 Liter',  'message' => 'Option "Flaschengröße > 5,0 Liter" has wrong position'),
        ));

        //check position of "Alkoholgehalt" value positions
        $this->assertArrayKeys($options['Alkoholgehalt'], array(
            array('index' => 0, 'value' => '>30%',  'message' => 'Option "Alkoholgehalt > >30%" has wrong position'),
            array('index' => 1, 'value' => '< 20%',  'message' => 'Option "Alkoholgehalt > < 20%" has wrong position')
        ));

        //check article count per filter value
        $this->assertEquals(5, $options['Trinktemperatur']['Gekühlt']['count']);
        $this->assertEquals(4, $options['Trinktemperatur']['Zimmertemperatur']['count']);

        $this->assertEquals(6, $options['Geschmack']['mild']['count']);
        $this->assertEquals(3, $options['Geschmack']['herb']['count']);
        $this->assertEquals(1, $options['Geschmack']['fruchtig']['count']);

        $this->assertEquals(3, $options['Farbe']['goldig']['count']);
        $this->assertEquals(3, $options['Farbe']['klar']['count']);
        $this->assertEquals(2, $options['Farbe']['rot']['count']);
        $this->assertEquals(1, $options['Farbe']['schokobraun']['count']);

        $this->assertEquals(5, $options['Flaschengröße']['0,7 Liter']['count']);
        $this->assertEquals(4, $options['Flaschengröße']['0,5 Liter']['count']);
        $this->assertEquals(3, $options['Flaschengröße']['0,2 Liter']['count']);
        $this->assertEquals(3, $options['Flaschengröße']['1,0 Liter']['count']);
        $this->assertEquals(3, $options['Flaschengröße']['1,5 Liter']['count']);
        $this->assertEquals(1, $options['Flaschengröße']['5,0 Liter']['count']);

        $this->assertEquals(7, $options['Alkoholgehalt']['>30%']['count']);
        $this->assertEquals(3, $options['Alkoholgehalt']['< 20%']['count']);
    }


    public function testIndulgenceWorldsFiltersWithCooledFilter()
    {
        Shopware()->Modules()->Articles()->sSYSTEM->_GET['sFilterProperties'] = '35';
        $properties = Shopware()->Modules()->Articles()->sGetCategoryProperties(14);

        $groups = $properties['filterOptions']['grouped'];
        $this->assertArrayKeys($groups, array(
            array('index' => 0, 'value' => 'Edelbrände', 'message' => 'First group key do not match')
        ));

        $options = $groups['Edelbrände']['options'];
        $this->assertCount(5, $options, 'Option count of edelbrände do not match');

        //check position of "Trinktemperatur" value positions
        $this->assertArrayKeys($options, array(
            array('index' => 0, 'value' => 'Trinktemperatur',   'message' => 'Option Trinktemperatur has wrong position'),
            array('index' => 1, 'value' => 'Geschmack',         'message' => 'Option Geschmack has wrong position'),
            array('index' => 2, 'value' => 'Farbe',             'message' => 'Option Farbe has wrong position'),
            array('index' => 3, 'value' => 'Flaschengröße',     'message' => 'Option Flaschengröße has wrong position'),
            array('index' => 4, 'value' => 'Alkoholgehalt',     'message' => 'Option Alkoholgehalt has wrong position')
        ));

        //check position of "Trinktemperatur" value positions
        $this->assertArrayKeys($options['Trinktemperatur'], array(
            array('index' => 0, 'value' => 'Gekühlt',          'message' => 'Option "Trinktemperatur > Gekühlt" has wrong position'),
        ));

        //check position of "Geschmack" value positions
        $this->assertArrayKeys($options['Geschmack'], array(
            array('index' => 0, 'value' => 'herb',     'message' => 'Option "Geschmack > herb" has wrong position'),
            array('index' => 1, 'value' => 'mild',     'message' => 'Option "Geschmack > mild" has wrong position'),
            array('index' => 2, 'value' => 'fruchtig', 'message' => 'Option "Geschmack > fruchtig" has wrong position')
        ));

        //check position of "Farbe" value positions
        $this->assertArrayKeys($options['Farbe'], array(
            array('index' => 0, 'value' => 'rot',         'message' => 'Option "Farbe > rot" has wrong position'),
            array('index' => 1, 'value' => 'klar',        'message' => 'Option "Farbe > klar" has wrong position'),
            array('index' => 2, 'value' => 'schokobraun', 'message' => 'Option "Farbe > schokobraun" has wrong position')
        ));

        //check position of "Flaschengröße" value positions
        $this->assertArrayKeys($options['Flaschengröße'], array(
            array('index' => 0, 'value' => '0,7 Liter',  'message' => 'Option "Flaschengröße > 0,7 Liter" has wrong position'),
            array('index' => 1, 'value' => '0,2 Liter',  'message' => 'Option "Flaschengröße > 0,2 Liter" has wrong position'),
            array('index' => 2, 'value' => '1,0 Liter',  'message' => 'Option "Flaschengröße > 1,0 Liter" has wrong position'),
            array('index' => 3, 'value' => '1,5 Liter',  'message' => 'Option "Flaschengröße > 1,5 Liter" has wrong position')
        ));

        //check position of "Alkoholgehalt" value positions
        $this->assertArrayKeys($options['Alkoholgehalt'], array(
            array('index' => 0, 'value' => '< 20%',  'message' => 'Option "Alkoholgehalt > < 20%" has wrong position'),
            array('index' => 1, 'value' => '>30%',  'message' => 'Option "Alkoholgehalt > >30%" has wrong position')
        ));

        //check article count per filter value
        $this->assertEquals(5, $options['Trinktemperatur']['Gekühlt']['count']);

        $this->assertEquals(2, $options['Geschmack']['mild']['count']);
        $this->assertEquals(2, $options['Geschmack']['herb']['count']);
        $this->assertEquals(1, $options['Geschmack']['fruchtig']['count']);

        $this->assertEquals(2, $options['Farbe']['klar']['count']);
        $this->assertEquals(2, $options['Farbe']['rot']['count']);
        $this->assertEquals(1, $options['Farbe']['schokobraun']['count']);

        $this->assertEquals(5, $options['Flaschengröße']['0,7 Liter']['count']);
        $this->assertEquals(3, $options['Flaschengröße']['0,2 Liter']['count']);
        $this->assertEquals(3, $options['Flaschengröße']['1,0 Liter']['count']);
        $this->assertEquals(2, $options['Flaschengröße']['1,5 Liter']['count']);

        $this->assertEquals(2, $options['Alkoholgehalt']['>30%']['count']);
        $this->assertEquals(3, $options['Alkoholgehalt']['< 20%']['count']);

    }




    private function assertArrayKeys($array, $expectedKeys)
    {
        $keys = array_keys($array);
        $this->assertCount(count($expectedKeys), $keys, 'Array count do not match!');

        foreach($expectedKeys as $expectedKey) {
            $this->assertEquals($expectedKey['value'], $keys[$expectedKey['index']], $expectedKey['message']);
        }
    }

}
