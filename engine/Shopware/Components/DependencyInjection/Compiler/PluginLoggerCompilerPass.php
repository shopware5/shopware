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

namespace Shopware\Components\DependencyInjection\Compiler;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Shopware\Components\Logger;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PluginLoggerCompilerPass implements CompilerPassInterface
{
    /**
     * @var Plugin[]
     */
    private $plugins;

    /**
     * @param Plugin[] $plugins
     */
    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->plugins as $plugin) {
            $this->processPlugin($container, $plugin->getContainerPrefix());
        }
    }

    protected function processPlugin(ContainerBuilder $container, string $servicePrefix): void
    {
        if (!$container->hasParameter($logLevel = $this->getParameterNameLogLevel($servicePrefix))) {
            $container->setParameter($logLevel, $container->getParameter('shopware.logger.level'));
        }

        if (!$container->hasParameter($logMaxFiles = $this->getParameterNameLoggerMaxFiles($servicePrefix))) {
            $container->setParameter($logMaxFiles, 14);
        }

        $container->setDefinition($this->getServiceIdLoggerHandler($servicePrefix), $this->createLoggerHandler($servicePrefix));
        $container->setDefinition($this->getServiceIdLoggerFormatter($servicePrefix), $this->createLoggerFormatter());
        $container->setDefinition($this->getServiceIdLogger($servicePrefix), $this->createLogger($servicePrefix));
    }

    protected function createLoggerHandler(string $servicePrefix): Definition
    {
        return (new Definition(RotatingFileHandler::class, [
                sprintf('%%kernel.logs_dir%%/%s_%%kernel.environment%%.log', $servicePrefix),
                sprintf('%%%s%%', $this->getParameterNameLoggerMaxFiles($servicePrefix)),
                sprintf('%%%s%%', $this->getParameterNameLogLevel($servicePrefix)),
            ]))
            ->addMethodCall('pushProcessor', [new Reference('monolog.processor.uid')])
            ->setPublic(false)
        ;
    }

    protected function createLoggerFormatter(): Definition
    {
        return (new Definition(PsrLogMessageProcessor::class))
            ->setPublic(false)
        ;
    }

    protected function createLogger(string $servicePrefix): Definition
    {
        return (new Definition(Logger::class, [$servicePrefix]))
            ->addMethodCall('pushHandler', [new Reference($this->getServiceIdLoggerHandler($servicePrefix))])
            ->addMethodCall('pushProcessor', [new Reference($this->getServiceIdLoggerFormatter($servicePrefix))])
        ;
    }

    protected function getServiceIdLogger(string $servicePrefix): string
    {
        return sprintf('%s.logger', $servicePrefix);
    }

    protected function getServiceIdLoggerFormatter(string $servicePrefix): string
    {
        return sprintf('%s.logger_formatter', $servicePrefix);
    }

    protected function getServiceIdLoggerHandler(string $servicePrefix): string
    {
        return sprintf('%s.logger_handler', $servicePrefix);
    }

    protected function getParameterNameLoggerMaxFiles(string $servicePrefix): string
    {
        return sprintf('%s.logger.max_files', $servicePrefix);
    }

    protected function getParameterNameLogLevel(string $servicePrefix): string
    {
        return sprintf('%s.logger.level', $servicePrefix);
    }
}
