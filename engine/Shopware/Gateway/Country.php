<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:45
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Country
{
    /**
     * The \Shopware\Struct\Country\State requires the following data:
     * - Country area base data
     *
     * @param int $id
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\Area
     */
    public function getArea($id, Struct\Context $context);

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Country::getState()
     *
     * @param array $ids
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\State[]
     */
    public function getStates(array $ids, Struct\Context $context);

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Country::getCountry()
     *
     * @param array $ids
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country[]
     */
    public function getCountries(array $ids, Struct\Context $context);

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Country::getArea()
     *
     * @param array $ids
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\Area[]
     */
    public function getAreas(array $ids, Struct\Context $context);

    /**
     * The \Shopware\Struct\Country requires the following data:
     * - Country base data
     * - Core attribute
     *
     * Required translation in the provided context language:
     * - Country base data
     *
     * @param int $id
     * @param \Shopware\Struct\Context $context
     * @return \Shopware\Struct\Country
     */
    public function getCountry($id, Struct\Context $context);

    /**
     * The \Shopware\Struct\Country\State requires the following data:
     * - Country state base data
     * - Core attribute
     *
     * Required translation in the provided context language:
     * - Country state base data
     *
     * @param $id
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\State
     */
    public function getState($id, Struct\Context $context);
}