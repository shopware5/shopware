<?php

namespace Shopware\Bundle\ControllerBundle\Struct;

class ControllerStruct
{
    /**
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $module
     * @param string $name
     * @param string $path
     */
    public function __construct($module, $name, $path)
    {
        $this->module = $module;
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return sprintf(
            'Enlight_Controller_Dispatcher_ControllerPath_%s_%s',
            $this->module,
            $this->name
        );
    }
}
