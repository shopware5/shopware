<?php

namespace Shopware\Bundle\ControllerBundle\Finder;

use Shopware\Bundle\ControllerBundle\Struct\ControllerStruct;

class ControllerFinder
{
    const MODULES = ['Backend', 'Frontend', 'Widgets'];

    /**
     * @param string $path
     *
     * @return ControllerStruct[]
     */
    public function getControllers($path)
    {
        $controllers = [];

        foreach (self::MODULES as $module) {
            $files = glob(sprintf('%s/%s/*.php', rtrim($path, '/'), $module));

            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                $controllers[] = new ControllerStruct($module, rtrim(basename($file), '.php'), $file);
            }
        }

        return $controllers;
    }
}
