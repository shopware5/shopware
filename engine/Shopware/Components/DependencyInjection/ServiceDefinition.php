<?php

namespace Shopware\Components\DependencyInjection;

class ServiceDefinition {
    /**
     * @var String
     */
    protected $xmlPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @param       $xmlPath
     * @param       $alias
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct($xmlPath, $alias = null, array $config = null)
    {
        if (!empty($config) && empty($alias)) {
            throw new \Exception('The passed service configuration requires a configuration alias.');
        }
        $this->alias = $alias;
        $this->config = $config;
        $this->xmlPath = $xmlPath;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param String $xmlPath
     */
    public function setXmlPath($xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }

    /**
     * @return String
     */
    public function getXmlPath()
    {
        return $this->xmlPath;
    }
}
