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

namespace Shopware\Bundle\ContentTypeBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\ContentTypeBundle\Services\DatabaseContentTypeSynchronizer;
use Shopware\Bundle\PluginInstallerBundle\Events\PluginEvent;
use Shopware\Components\CacheManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SyncSubscriber implements SubscriberInterface
{
    /**
     * @var DatabaseContentTypeSynchronizer
     */
    private $synchronizerService;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(DatabaseContentTypeSynchronizer $synchronizerService, ContainerInterface $container, CacheManager $cacheManager)
    {
        $this->synchronizerService = $synchronizerService;
        $this->container = $container;
        $this->cacheManager = $cacheManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvent::POST_INSTALL => 'onChange',
            PluginEvent::POST_UNINSTALL => 'onChange',
        ];
    }

    public function onChange(\Enlight_Event_EventArgs $eventArgs): void
    {
        // Plugin does not have a content type. Skip sync
        if (!file_exists($eventArgs->getPlugin()->getPath() . '/Resources/contenttypes.xml')) {
            return;
        }

        $installedPlugins = array_keys($this->container->getParameter('active_plugins'));

        if ($eventArgs->getName() === PluginEvent::POST_INSTALL) {
            $installedPlugins[] = $eventArgs->getPlugin()->getName();
        } else {
            $index = array_search($eventArgs->getPlugin()->getName(), $installedPlugins, true);

            if ($index) {
                unset($installedPlugins[$index]);
            }
        }

        $this->synchronizerService->sync($installedPlugins);
        $this->cacheManager->clearConfigCache();
        $this->cacheManager->clearProxyCache();
    }
}
