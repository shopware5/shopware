<?php

namespace Shopware\Gateway\DBAL\Hydrator;

class Hydrator
{
    protected function extractFields($prefix, $data)
    {
        $result = array();
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $key = str_replace($prefix, '', $field);
                $result[$key] = $value;
            }
        }
        return $result;
    }

    protected function getFields($prefix, $data)
    {
        $result = array();
        foreach ($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $result[$field] = $value;
            }
        }
        return $result;
    }
}