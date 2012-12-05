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

namespace Shopware\Components\Jira\API;

use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\API\Model\Keyword;

/**
 * Base interface that allows access to JIRA projects.
 */
interface ProjectService
{
    /**
     * Returns the project identifier by the given <b>$id</b>.
     *
     * @param integer $id
     *
     * @return \Shopware\Components\Jira\API\Model\Project
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($id);

    /**
     * Loads one or more projects by their key. You can call this method with
     * a variable number of parameters, but at least with one project key.
     *
     * @param string $key
     *
     * @return \Shopware\Components\Jira\API\Model\Project[]
     */
    public function loadByKeys($key);
}