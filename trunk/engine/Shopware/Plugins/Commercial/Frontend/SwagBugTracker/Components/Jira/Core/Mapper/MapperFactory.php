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

use \Shopware\Components\Jira\API\Context;

class MapperFactory implements \Shopware\Components\Jira\SPI\Mapper\MapperFactory
{
    /**
     * @var \Shopware\Components\Jira\API\Context
     */
    private $context;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\ProjectMapper
     */
    private $projectMapper;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\IssueMapper
     */
    private $issueMapper;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\VersionMapper
     */
    private $versionMapper;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\KeywordMapper
     */
    private $keywordMapper;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\ComponentMapper
     */
    private $componentMapper;

    /**
     * @var \Shopware\Components\Jira\Core\Mapper\CommentMapper
     */
    private $commentMapper;

    /**
     * @param \Shopware\Components\Jira\API\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Returns a mapper that can transform projects into arrays and simple arrays
     * into projects.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createProjectMapper()
    {
        if (null === $this->projectMapper) {
            $this->projectMapper = new ProjectMapper($this->context);
        }
        return $this->projectMapper;
    }

    /**
     * Returns a mapper that can transform issues into arrays and simple arrays
     * into issues.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createIssueMapper()
    {
        if (null === $this->issueMapper) {
            $this->issueMapper = new IssueMapper($this->context);
        }
        return $this->issueMapper;
    }

    /**
     * Returns a mapper that can transform versions into arrays and simple arrays
     * into versions.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createVersionMapper()
    {
        if (null === $this->versionMapper) {
            $this->versionMapper = new VersionMapper($this->context);
        }
        return $this->versionMapper;
    }

    /**
     * @return \Shopware\Components\Jira\Core\Mapper\KeywordMapper
     */
    public function createKeywordMapper()
    {
        if (null === $this->keywordMapper) {
            $this->keywordMapper = new KeywordMapper($this->context);
        }
        return $this->keywordMapper;
    }

    /**
     * @return \Shopware\Components\Jira\Core\Mapper\ComponentMapper
     */
    public function createComponentMapper()
    {
        if (null === $this->componentMapper) {
            $this->componentMapper = new ComponentMapper($this->context);
        }
        return $this->componentMapper;
    }

    /**
     * @return \Shopware\Components\Jira\Core\Mapper\CommentMapper
     */
    public function createCommentMapper()
    {
        if (null === $this->commentMapper) {
            $this->commentMapper = new CommentMapper($this->context);
        }
        return $this->commentMapper;
    }
}