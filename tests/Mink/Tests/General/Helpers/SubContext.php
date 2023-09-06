<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Tests\General\Helpers;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareContext;
use Shopware\Kernel;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SubContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
{
    private Mink $mink;

    private array $minkParameters;

    private Kernel $kernel;

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink): void
    {
        $this->mink = $mink;
    }

    public function getMink(): Mink
    {
        return $this->mink;
    }

    /**
     * Sets parameters provided for Mink.
     */
    public function setMinkParameters(array $parameters): void
    {
        $this->minkParameters = $parameters;
    }

    /**
     * Returns specific mink parameter.
     */
    public function getMinkParameter(string $name): ?string
    {
        return $this->minkParameters[$name] ?? null;
    }

    public function getSession(): Session
    {
        return $this->mink->getSession();
    }

    public function getDriver(): DriverInterface
    {
        return $this->getSession()->getDriver();
    }

    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns HttpKernel service container.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $id
     *
     * @return TService
     */
    protected function getService(string $id): object
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @template TElement of MultipleElement
     *
     * @param Page                   $page        Parent page
     * @param class-string<TElement> $elementName Name of the element
     * @param int                    $instance    Instance of the element
     *
     * @return TElement
     */
    protected function getMultipleElement(Page $page, string $elementName, int $instance = 1): MultipleElement
    {
        $element = $this->getElement($elementName);
        if (!$element instanceof $elementName) {
            Helper::throwException(sprintf('Element expected to be a %s', $elementName));
        }

        $element->setParent($page);

        if ($instance > 1) {
            $element = $element->setInstance($instance);
        }

        return $element;
    }
}
