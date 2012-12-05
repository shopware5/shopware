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

use \Shopware\Components\Jira\API\Model\IssueType;
use \Shopware\Components\Jira\API\Model\ValueObject;
use \Shopware\Components\Jira\Core\Service\Model\Issue;

/**
 * Mapper class for {@link Issue} domain objects.
 */
class IssueMapper extends Mapper
{
    /**
     * Takes the given <b>$data</b> array and creates a domain object from this
     * value.
     *
     * @param array $data
     *
     * @return \Shopware\Components\Jira\API\Model\ValueObject
     */
    public function toObject(array $data)
    {
        return new Issue(
            $this->context,
            array(
                'id'           => (int) $data['id'],
                'key'          => $data['key'],
                'name'         => $data['name'],
                'description'  => $data['description'],
                'issueType'    => new IssueType(array('id' => (int) $data['type'])),
                'priority'     => $data['priority'],
                'reporter'     => $data['reporter'],
                'assignee'     => $data['assignee'],
                'votes'        => (int) $data['votes'],
                'status'       => $data['status'],
                'createdAt'    => new \DateTime($data['createdAt']),
                'modifiedAt'   => new \DateTime($data['modifiedAt']),
            )
        );
    }
}