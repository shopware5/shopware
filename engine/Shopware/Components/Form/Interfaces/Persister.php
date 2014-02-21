<?php

namespace Shopware\Components\Form\Interfaces;

use Shopware\Components\Form\Container;

interface Persister
{
    /**
     * Saves the given container to the database, files or wherever.
     *
     * @param Container $container
     */
    public function save(Container $container);
}