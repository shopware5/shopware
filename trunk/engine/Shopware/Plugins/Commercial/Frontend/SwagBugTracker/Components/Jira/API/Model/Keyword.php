<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Jira
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 */

namespace Shopware\Components\Jira\API\Model;

/**
 * Domain class for JIRA issue keywords.
 */
class Keyword extends ValueObject
{
    /**
     * The internal identifier for this keyword.
     *
     * @var integer
     */
    protected $id;

    /**
     * The name/text representation of this keyword.
     *
     * @var string
     */
    protected $name;

    /**
     * Returns the identifier for this keyword.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the textual representation of this keyword.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}