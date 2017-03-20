<?php

interface Shopware_Components_Config
{
    public function __isset($name);
    public function __get($name);
    public function __set($name, $value);
    public function __call($name, $args = null);
    public function setShop($shop);
    public function formatName($name);
    public function getByNamespace($namespace, $name, $default = null);
    public function get($name, $default = null);
}