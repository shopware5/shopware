<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

interface DefaultUpdateField extends DefaultCreateField
{
    public function getValue();
}