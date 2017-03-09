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

namespace Shopware\Tests\Mink;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareContext;
use Shopware\Kernel;
use Shopware\Tests\Mink\Element\MultipleElement;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SubContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
{
    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var array
     */
    private $minkParameters;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @return Mink
     */
    public function getMink()
    {
        return $this->mink;
    }

    /**
     * Sets parameters provided for Mink.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
    }

    /**
     * Returns specific mink parameter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getMinkParameter($name)
    {
        return isset($this->minkParameters[$name]) ? $this->minkParameters[$name] : null;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->mink->getSession();
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @param Kernel $kernel
     */
    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param Page   $page        Parent page
     * @param string $elementName Name of the element
     * @param int    $instance    Instance of the element
     *
     * @return MultipleElement
     */
    protected function getMultipleElement(Page $page, $elementName, $instance = 1)
    {
        /** @var MultipleElement $element */
        $element = $this->getElement($elementName);
        $element->setParent($page);

        if ($instance > 1) {
            $element = $element->setInstance($instance);
        }

        return $element;
    }
}
