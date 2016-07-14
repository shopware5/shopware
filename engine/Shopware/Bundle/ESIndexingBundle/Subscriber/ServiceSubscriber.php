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

namespace Shopware\Bundle\ESIndexingBundle\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\ESIndexingBundle\Commands\AnalyzeCommand;
use Shopware\Bundle\ESIndexingBundle\Commands\BacklogClearCommand;
use Shopware\Bundle\ESIndexingBundle\Commands\BacklogSyncCommand;
use Shopware\Bundle\ESIndexingBundle\Commands\IndexCleanupCommand;
use Shopware\Bundle\ESIndexingBundle\Commands\IndexPopulateCommand;
use Shopware\Bundle\ESIndexingBundle\Commands\SwitchAliasCommand;

class ServiceSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Console_Add_Command' => ['addCommands']
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function addCommands()
    {
        return new ArrayCollection([
            new IndexPopulateCommand(),
            new IndexCleanupCommand(),
            new BacklogClearCommand(),
            new BacklogSyncCommand(),
            new SwitchAliasCommand(),
            new AnalyzeCommand(),
        ]);
    }
}
