<?php

namespace Shopware\Tests\Mink;

use Behat\Mink\Driver\DriverInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Shopware\Tests\Mink\Element\MultipleElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class SubContext extends PageObjectContext implements KernelAwareInterface
{
    /**
     * @var \Shopware\Kernel
     */
    private $kernel;

    /**
     * @return \Behat\Mink\Session
     */
    public function getSession()
    {
        return $this->getMainContext()->getSession();
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @param HttpKernelInterface $kernel
     */
    public function setKernel(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Shopware\Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
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
     * Returns a multiple element
     *
     * @param Page $page            Parent page
     * @param string $elementName   Name of the element
     * @param int $instance         Instance of the element
     * @return MultipleElement
     */
    protected function getMultipleElement(Page $page, $elementName, $instance = 1)
    {
        /** @var MultipleElement $element */
        $element = $this->getElement($elementName);
        $element->setParent($page);

        if($instance > 1) {
            $element = $element->setInstance($instance);
        }

        return $element;
    }
}
