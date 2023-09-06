<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\MediaBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_MediaCrawler' => 'runCronjob',
        ];
    }

    /**
     * Runs the garbage collector
     *
     * @throws Exception
     *
     * @return bool
     */
    public function runCronjob()
    {
        $garbageCollector = $this->container->get('shopware_media.garbage_collector');
        $garbageCollector->run();

        return true;
    }
}
