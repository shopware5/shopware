<?php

namespace Shopware\Gateway;

interface Country
{
    public function getArea($id);

    public function getCountry($id);

    public function getState($id);
}