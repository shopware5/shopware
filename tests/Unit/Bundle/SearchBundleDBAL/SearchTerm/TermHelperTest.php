<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * 'Shopware' is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Bundle\SearchBundleDBAL\SearchTerm;

use Shopware\Bundle\SearchBundleDBAL\SearchTerm\TermHelper;

class TermHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TermHelper $termHelper
     */
    private $termHelper;

    /**
     * Sets up the {@link $termHelper} fixture
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $configStub = $this->getMockBuilder('Shopware_Components_Config')->disableOriginalConstructor()->getMock();

        $this->termHelper = new TermHelper($configStub);
    }

    /**
     * tests if the provided string gets divided by words and if all these words are lower case
     *
     * @dataProvider splitTermProvider
     * @param string $testString
     * @param string $expectedResult
     */
    public function testSplitTerm($testString, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->termHelper->splitTerm($testString)
        );
    }

    /**
     * provides different test cases for {@link testSplitTerm()}
     *
     * @return array
     */
    public function splitTermProvider()
    {
        return [
            ['ABC DEF 123', ['abc', 'def', 123]],
            ['Ä ÖÜ 456', ['ae', 'oeue', 456]],
            ['ӔЁ Љ 789', ['ӕё', 'љ', 789]],
            ['THIS-is/a?tÉsT:123', ['this', 'is', 'a', 'tést', 123]]
        ];
    }
}
