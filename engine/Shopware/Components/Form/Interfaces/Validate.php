<?php

namespace Shopware\Components\Form\Interfaces;

interface Validate
{
    /**
     * Validates the form element
     * and throws an exception if
     * some requirements are not set.
     *
     * @throws \Exception
     */
    public function validate();

}