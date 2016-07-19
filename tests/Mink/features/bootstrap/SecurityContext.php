<?php

namespace Shopware\Tests\Mink;

class SecurityContext extends SubContext
{
    public static $configPath = __DIR__ . '/../../../../config.php';
    public static $testConfig = [];

    /**
     * @BeforeFeature
     */
    public static function setupFeature()
    {
        self::$testConfig = require self::$configPath;
        self::setCsrfStatus(true, true);
    }

    /**
     * @AfterFeature
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
}
