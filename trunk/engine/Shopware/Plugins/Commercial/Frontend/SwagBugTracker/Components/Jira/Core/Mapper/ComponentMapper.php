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

namespace Shopware\Components\Jira\Core\Mapper;

use \Shopware\Components\Jira\API\Model\Component;
use \Shopware\Components\Jira\API\Model\ValueObject;

/**
 * Maps a value array into a component object.
 */
class ComponentMapper extends Mapper
{
    /**
     * Takes the given <b>$data</b> array and creates a component object from
     * these values.
     *
     * @param array $data
     *
     * @return \Shopware\Components\Jira\API\Model\Component
     */
    public function toObject(array $data)
    {
        return new Component(
            array(
                'id'           => (int)$data['id'],
                'projectId'    => (int)$data['projectId'],
                'name'         => $data['name'],
                'description'  => $data['description']
            )
        );
    }
}