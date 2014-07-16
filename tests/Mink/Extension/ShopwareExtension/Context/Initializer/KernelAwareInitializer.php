<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\OutlineEvent;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareInterface;

class KernelAwareInitializer implements InitializerInterface, EventSubscriberInterface
{
    private $kernel;

    /**
     * Initializes initializer.
     *
     * @param HttpKernelInterface $kernel
     */
    public function __construct(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'beforeScenario'       => array('bootKernel', 15),
            'beforeOutlineExample' => array('bootKernel', 15),
            'afterScenario'        => array('shutdownKernel', -15),
            'afterOutlineExample'  => array('shutdownKernel', -15)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ContextInterface $context)
    {
        if ($context instanceof KernelAwareInterface) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ContextInterface $context)
    {
        $context->setKernel($this->kernel);
    }

    /**
     * Boots HttpKernel before each scenario.
     *
     * @param ScenarioEvent|OutlineEvent $event
     */
    public function bootKernel($event)
    {
        $this->kernel->boot();
    }

    /**
     * Stops HttpKernel after each scenario.
     *
     * @param ScenarioEvent|OutlineEvent $event
     */
    public function shutdownKernel($event)
    {
        if (method_exists($this->kernel, 'shutdown')) {
            $this->kernel->shutdown();
        }
    }
}
