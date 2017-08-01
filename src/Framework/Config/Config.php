<?php
declare(strict_types=1);

namespace Shopware\Framework\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

class Config extends ParameterBag implements \ArrayAccess
{
    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __call($name, $args = null)
    {
        return $this->get($name);
    }

    public function setShop($shop)
    {
    }

    public function formatName($name)
    {
        if (strpos($name, 's') === 0 && preg_match('#^s[A-Z]#', $name)) {
            $name = substr($name, 1);
        }

        return str_replace('_', '', strtolower($name));
    }

    public function getByNamespace($namespace, $name, $default = null)
    {
        // TODO: Implement getByNamespace() method.
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetUnset($name): void
    {
        $this->remove($name);
    }

    public function offsetExists($name): bool
    {
        return $this->has($name);
    }

    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }
}