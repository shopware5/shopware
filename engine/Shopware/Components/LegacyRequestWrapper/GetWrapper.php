<?php

namespace Shopware\Components\LegacyRequestWrapper;

class GetWrapper implements \ArrayAccess
{
    private $request;

    public function __construct(\Enlight_Controller_Request_RequestHttp $request)
    {
        $this->request = $request ? : new \Enlight_Controller_Request_RequestHttp();
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
        $this->request->setQuery($offset, null);
    }

    public function setAll($values)
    {
        $this->request->setQuery($values);
    }

    public function toArray()
    {
        return $this->request->getQuery();
    }
}