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
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink;

class SecurityContext extends SubContext
{
    public static $configPath = __DIR__ . '/../../../../config.php';

    public static $testConfig = [];

    /**
     * @BeforeScenario @csrf
     */
    public static function setupFeature()
    {
        $config = require self::$configPath;

        if (!array_key_exists('csrfProtection', $config)) {
            $config['csrfProtection'] = ['frontend' => false, 'backend' => false];
        }

        self::$testConfig = $config;

        self::setCsrfStatus(true, true);
    }

    /**
     * @AfterScenario @csrf
     */
    public static function teardownFeature()
    {
        self::setCsrfStatus(self::$testConfig['csrfProtection']['frontend'], self::$testConfig['csrfProtection']['backend']);
    }

    /**
     * @param bool $frontend
     * @param bool $backend
     */
    public static function setCsrfStatus($frontend = true, $backend = true)
    {
        $config = array_merge(self::$testConfig, ['csrfProtection' => ['frontend' => $frontend, 'backend' => $backend]]);

        $configContent = '<?php return ' . var_export($config, true) . ';';
        file_put_contents(self::$configPath, $configContent);
    }

    /**
     * @Then /^The http response code should be "([^"]*)"$/
     *
     * @param int $expectedCode
     */
    public function theHttpResponseCodeShouldBe($expectedCode)
    {
        $code = (int) $this->getSession()->getStatusCode();
        if ($expectedCode == $this->getSession()->getStatusCode()) {
            return;
        }

        Helper::throwException(sprintf('Expected http response code %d, got %d instead.', $expectedCode, $code));
    }
}
