<?php

use Shopware\Components\Plugin\PluginContext;

class PluginContextTest extends \PHPUnit_Framework_TestCase
{
    public function testFrontendCaches()
    {
        $entity = new \Shopware\Models\Plugin\Plugin();
        $context = new PluginContext($entity, \Shopware::VERSION);
        $plugin = new MyPlugin(true);
        $plugin->activate($context);

        $this->assertArrayHasKey('cache', $context->getScheduled());
        $this->assertNotEmpty($context->getScheduled()['cache']);
    }

    public function testMessage()
    {
        $entity = new \Shopware\Models\Plugin\Plugin();
        $context = new PluginContext($entity, \Shopware::VERSION);
        $plugin = new MyPlugin(true);

        $plugin->deactivate($context);
        $this->assertArrayHasKey('message', $context->getScheduled());
        $this->assertEquals($context->getScheduled()['message'], 'Clear the caches');
    }

    public function testCacheCombination()
    {
        $entity = new \Shopware\Models\Plugin\Plugin();
        $context = new PluginContext($entity, \Shopware::VERSION);
        $plugin = new MyPlugin(true);

        $plugin->install($context);
        $this->assertArrayHasKey('cache', $context->getScheduled());
        $this->assertNotEmpty($context->getScheduled()['cache']);
        $this->assertCount(count(PluginContext::CACHE_LIST_ALL), $context->getScheduled()['cache']);
    }

    public function testDefault()
    {
        $entity = new \Shopware\Models\Plugin\Plugin();
        $context = new PluginContext($entity, \Shopware::VERSION);
        $plugin = new MyPlugin(true);

        $plugin->uninstall($context);
        $this->assertArrayHasKey('cache', $context->getScheduled());
        $this->assertEquals(PluginContext::CACHE_LIST_DEFAULT, $context->getScheduled()['cache']);
    }
}

class MyPlugin extends \Shopware\Components\Plugin
{
    public function activate(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_FRONTEND);
    }

    public function deactivate(PluginContext $context)
    {
        $context->scheduleMessage('Clear the caches');
    }

    public function install(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_FRONTEND);
        $context->scheduleClearCache(PluginContext::CACHE_LIST_DEFAULT);
    }
}
