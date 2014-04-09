<?php

namespace Shopware\Components\LegacyRequestWrapper;

class GetWrapper implements \ArrayAccess
{
    private $request;

    public function __construct(
        \Enlight_Controller_Request_RequestHttp $request = null
    )
    {
        $this->request = $request ? : Shopware()->Front()->Request();
        $this->request = $this->request ? : new \Enlight_Controller_Request_RequestHttp();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        if (!$this->request) {
            return false;
        }
        $getData = $this->request->getQuery();
        return array_key_exists($offset, $getData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!$this->request) {
            return null;
        }
        return $this->request->getQuery($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->request) {
            return null;
        }
        if (is_array($value)) {
            array_walk_recursive($value, function (&$value) {
                if ($value === null) {
                    $value = '';
                }
            });
        } elseif (null === $value) {
            $value = '';
        }
        $this->request->setQuery($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!$this->request) {
            return null;
        }
        $this->request->setQuery($offset, null);
    }

    public function setAll($values)
    {
        if (!$this->request) {
            return null;
        }
        $this->request->setQuery($values);
    }

    public function toArray()
    {
        return $this->request->getQuery();
    }
}