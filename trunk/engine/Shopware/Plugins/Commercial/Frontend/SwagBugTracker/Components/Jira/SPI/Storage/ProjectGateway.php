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

namespace Shopware\Components\Jira\SPI\Storage;

/**
 * Base interface for a gateway that provides read/write access to JIRA projects.
 */
interface ProjectGateway
{
    /**
     * Returns a single project for the given identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id);

    /**
     * Returns a single project for the human readable project key.
     *
     * @param string $key
     *
     * @return array
     */
    public function fetchByKey($key);

    /**
     * Fetches the data of all projects that are identified by the given project
     * keys.
     *
     * @param array $keys
     *
     * @return array
     */
    public function fetchByKeys(array $keys);
}