<?php

namespace Shopware\Bundle\ControllerBundle\Listener;

use Enlight_Event_EventArgs;

class ControllerPathListener
{
    /**
     * @var string[]
     */
    private $controllers = [];

    /**
     * @param string $event
     * @param string $path
     *
     * @return void
     */
    public function addController($event, $path)
    {
        $this->controllers[$event] = $path;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     *
     * @return string
     */
    public function getControllerPath(Enlight_Event_EventArgs $args)
    {
        if (isset($this->controllers[$args->getName()])) {
            return $this->controllers[$args->getName()];
        }

        return null;
    }
}
