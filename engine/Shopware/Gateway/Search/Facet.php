<?php

namespace Shopware\Gateway\Search;

interface Facet
{
    public function getName();

    public function isFiltered();
}