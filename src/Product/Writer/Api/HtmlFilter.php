<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;


class HtmlFilter implements Filter
{
    public function filter($value)
    {
        return strip_tags($value);
    }
}