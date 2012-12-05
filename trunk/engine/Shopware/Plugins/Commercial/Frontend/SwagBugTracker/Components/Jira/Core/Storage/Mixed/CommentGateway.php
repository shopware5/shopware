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

namespace Shopware\Components\Jira\Core\Storage\Mixed;

use \Shopware\Components\Jira\Core\Rest\Client;

/**
 * Gateway class that reads and writes comment data from/to the JIRA database.
 */
class CommentGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\CommentGateway
{
    /**
     * Token that identifies a comment as public visible.
     *
     * @var string
     */
    private $publicToken = '[extern]';

    /**
     * @var \Shopware\Components\Jira\Core\Rest\Client
     */
    private $client;

    /**
     * Instantiates a new issue gateway for the given database connection and
     * the given http client.
     *
     * @param \PDO $connection
     * @param \Shopware\Components\Jira\Core\Rest\Client $client
     */
    public function __construct(\PDO $connection, Client $client)
    {
        parent::__construct($connection);

        $this->client = $client;
    }

    /**
     * Stores the given data as a new comment and returns the comments id.
     *
     * @param array $data
     *
     * @return integer
     */
    public function store(array $data)
    {
        $response = $this->client->post(
            sprintf('rest/api/{version}/issue/%d/comment', $data['issue']),
            array(
                'body'  =>  sprintf(
                    '%s [author:%s] %s',
                    $this->publicToken,
                    $data['author'],
                    $data['description']
                )
            )
        );
        return (int) $response->id;
    }

    /**
     * Reads a single comment by it's identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id)
    {
        return $this->filterComment(
            $this->fetchSingle(
                $this->connection->prepare(
                    "SELECT `jiraaction`.`ID`         AS `id`,
                            `jiraaction`.`actionbody` AS `description`,
                            `jiraaction`.`CREATED`    AS `createdAt`,
                            `cwd_user`.`display_name` AS `author`
                       FROM `jiraaction`
                 INNER JOIN `cwd_user`
                         ON `cwd_user`.`user_name`    = `jiraaction`.`AUTHOR`
                      WHERE `jiraaction`.`actiontype` = 'comment'
                        AND `jiraaction`.`ID` = ?
                   GROUP BY `jiraaction`.`ID`"
                ),
                array($id)
            )
        );
    }

    /**
     * Returns all comments for the given issue identifier.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchByIssueId($issueId)
    {
        $comments = $this->fetchMultiple(
            $this->connection->prepare(
                "SELECT `jiraaction`.`ID`         AS `id`,
                        `jiraaction`.`actionbody` AS `description`,
                        `jiraaction`.`CREATED`    AS `createdAt`,
                        `cwd_user`.`display_name` AS `author`
                   FROM `jiraaction`
             INNER JOIN `cwd_user`
                     ON `cwd_user`.`user_name`    = `jiraaction`.`AUTHOR`
                  WHERE `jiraaction`.`actiontype` = 'comment'
                    AND `jiraaction`.`issueid`    = ?
               GROUP BY `jiraaction`.`ID`
               ORDER BY `jiraaction`.`CREATED` ASC"
            ),
            array($issueId)
        );

        return $this->filterComments($comments);
    }

    /**
     * Removes all comments that are not flagged as [extern]
     *
     * @param array $comments
     * @return array
     */
    private function filterComments(array $comments)
    {
        foreach ($comments as $i => $comment) {
            $comments[$i] = $this->filterComment($comment);
        }
        return array_values(array_filter($comments));
    }

    /**
     * Filters the given comment and performs some transformations, like author
     * extraction.
     *
     * @param array $comment
     * @return array|null
     */
    private function filterComment(array $comment)
    {
        if (false === strpos($comment['description'], $this->publicToken)) {
            return null;
        } else {
            $replace = array($this->publicToken);

            if (preg_match('(\[author\:([^\]]+)\])', $comment['description'], $match)) {
                $replace[] = $match[0];

                $comment['author'] = $match[1];
            }

            $comment['description'] = trim(
                str_replace(
                    $replace,
                    '',
                    $comment['description']
                )
            );
        }
        return $comment;
    }
}