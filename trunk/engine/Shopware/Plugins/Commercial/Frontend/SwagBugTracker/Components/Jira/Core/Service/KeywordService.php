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

namespace Shopware\Components\Jira\Core\Service;

use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\SPI\Mapper\Mapper;
use \Shopware\Components\Jira\SPI\Storage\KeywordGateway;

/**
 * Default implementation of the keyword service.
 *
 * This implementation utilizes a simple data gateway and mapper object to
 * handle keywords.
 */
class KeywordService implements \Shopware\Components\Jira\API\KeywordService
{
    /**
     * @var \Shopware\Components\Jira\SPI\Storage\KeywordGateway
     */
    private $gateway;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    private $mapper;

    /**
     * @param \Shopware\Components\Jira\SPI\Storage\KeywordGateway $gateway
     * @param \Shopware\Components\Jira\SPI\Mapper\Mapper $mapper
     */
    public function __construct(KeywordGateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper  = $mapper;
    }

    /**
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Keyword[]
     */
    public function loadByIssue(Issue $issue)
    {
        $keywords = array();
        foreach ($this->gateway->fetchByIssueId($issue->getId()) as $value) {
            $keywords[] = $this->mapper->toObject($value);
        }
        return $keywords;
    }


}