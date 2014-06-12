<?php

namespace Shopware\Gateway\DBAL\Hydrator;

class Hydrator
{
    public function extractFields($prefix, $data)
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

    protected function convertArrayKeys($data, $keys)
    {
        foreach ($keys as $old => $new) {
            if (!isset($data[$old])) {
                continue;
            }

            $data[$new] = $data[$old];
            unset($data[$old]);
        }

        return $data;
    }
}
