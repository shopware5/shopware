<?php

namespace Shopware\Tests\Mink;

use Behat\Mink\Driver\DriverInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareContext;
use Shopware\Tests\Mink\Element\MultipleElement;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubContext extends PageObjectContext implements KernelAwareContext
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
        return $this->getPageObjectFactory()->getSession();
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @param \Shopware\Kernel $kernel
     */
    public function setKernel(\Shopware\Kernel $kernel)
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
     * Returns the template name (Emotion / Responsive)
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->getPageObjectFactory()->getClassNameResolver()->getTemplateName();
    }

    /**
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
