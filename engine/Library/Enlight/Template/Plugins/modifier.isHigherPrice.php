<?php

function smarty_modifier_isHigherPrice($value, $price)
{
    $value = floatval(preg_replace('(\.|\,)', '', $value));
    $price = floatval(preg_replace('(\.|\,)', '', $price));

    return $price < $value;
}