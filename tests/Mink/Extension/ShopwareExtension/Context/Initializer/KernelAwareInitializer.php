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

namespace Shopware\Behat\ShopwareExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareContext;
use Shopware\Kernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class KernelAwareInitializer implements ContextInitializer, EventSubscriberInterface
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * Initializes initializer.
     *
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => ['bootKernel', 15],
            ExampleTested::BEFORE => ['bootKernel', 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof KernelAwareContext) {
            return;
        }

        $context->setKernel($this->kernel);
    }

    /**
     * Boots HttpKernel before each scenario.
     */
    public function bootKernel()
    {
        $this->kernel->boot();
    }
}
