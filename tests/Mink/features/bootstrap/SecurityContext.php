<?php

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
