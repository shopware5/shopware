<?php

namespace Shopware\Bundle\ControllerBundle\Finder;

use Shopware\Bundle\ControllerBundle\Struct\ControllerStruct;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ControllerFinder
{
    const MODULES = ['Backend', 'Frontend', 'Widgets', 'Api'];

    /**
     * @param string $path
     *
     * @return ControllerStruct[]
     */
    public function getControllers($path)
    {
        $controllers = [];
        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->name('*.php');

        foreach (self::MODULES as $module) {
            $finder->path($module);
        }

        foreach ($finder as $file) {
            /** @var $file SplFileInfo */
            $controllers[] = new ControllerStruct(
                $file->getPathInfo()->getBasename(),
                $file->getBasename('.php'),
                $file->getPathname()
            );
        }

        return $controllers;
    }
}
