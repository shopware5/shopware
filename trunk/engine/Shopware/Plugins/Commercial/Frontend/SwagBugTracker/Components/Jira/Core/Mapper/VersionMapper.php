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

use \Shopware\Components\Jira\API\Model\Version;
use \Shopware\Components\Jira\API\Model\ValueObject;

/**
 * Mapper class that takes a raw version data array and creates a version object
 * from this data.
 */
class VersionMapper extends Mapper
{
    /**
     * Takes the given <b>$data</b> array and creates a version from it.
     *
     * @param array $data
     *
     * @return \Shopware\Components\Jira\API\Model\Version
     */
    public function toObject(array $data)
    {
        return new Version(
            array(
                'id'          => (int)$data['id'],
                'name'        => $data['name'],
                'description' => $data['description'],
                'released'    => ($data['released'] === 'true'),
                'releaseDate' => $data['releaseDate'] ?
                    new \DateTime($data['releaseDate']) : null
            )
        );
    }
}