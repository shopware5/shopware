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

use \Shopware\Components\Jira\API\Model\CommentCreate;
use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\SPI\Mapper\Mapper;
use \Shopware\Components\Jira\SPI\Storage\CommentGateway;

/**
 * Default implementation of the keyword service.
 *
 * This implementation utilizes a simple data gateway and mapper object to
 * handle keywords.
 */
class CommentService implements \Shopware\Components\Jira\API\CommentService
{
    /**
     * @var \Shopware\Components\Jira\SPI\Storage\CommentGateway
     */
    private $gateway;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    private $mapper;

    /**
     * @param \Shopware\Components\Jira\SPI\Storage\CommentGateway $gateway
     * @param \Shopware\Components\Jira\SPI\Mapper\Mapper $mapper
     */
    public function __construct(CommentGateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper  = $mapper;
    }

    /**
     * Returns all comments associated with the given <b>$issue</b>
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Comment[]
     */
    public function loadByIssue(Issue $issue)
    {
        $comments = array();
        foreach ($this->gateway->fetchByIssueId($issue->getId()) as $value) {
            $comments[] = $this->mapper->toObject($value);
        }
        return $comments;
    }

    /**
     * Creates a new comment.
     *
     * @param \Shopware\Components\Jira\API\Model\CommentCreate $commentCreate
     *
     * @return \Shopware\Components\Jira\API\Model\Comment
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function create(CommentCreate $commentCreate)
    {
        $id = $this->gateway->store(
            array(
                'issue'        =>  $commentCreate->getIssue()->getId(),
                'author'       =>  $commentCreate->getAuthor(),
                'description'  =>  $commentCreate->getBody()
            )
        );

        // We expected latencies here due to replication
        for ($i = 0; $i < 5; ++$i) {
            try {
                return $this->mapper->toObject($this->gateway->fetchById($id));
            } catch (\Exception $e) {
                sleep(1);
            }
        }

        return $this->mapper->toObject($this->gateway->fetchById($id));
    }

    /**
     * Factory method that creates an implementation specific create struct.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\CommentCreate
     */
    public function newCommentCreate(Issue $issue)
    {
        return new CommentCreate(
            array(
                'issue' => $issue
            )
        );
    }
}