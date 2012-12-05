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

namespace Shopware\Components\Jira\Core\Service\Model;

use \Shopware\Components\Jira\API\Context;

/**
 * Implementation of the issue domain model class.
 *
 * This implementation uses lazy-loading to fetch additional data like keywords,
 * components or sub issues.
 */
class Issue extends \Shopware\Components\Jira\API\Model\Issue
{
    /**
     * @var \Shopware\Components\Jira\API\Context
     */
    private $context;

    /**
     * @var \Shopware\Components\Jira\API\Model\Keyword[]
     */
    private $keywords;

    /**
     * @var \Shopware\Components\Jira\API\Model\Component[]
     */
    private $components;

    /**
     * @var \Shopware\Components\Jira\API\Model\Issue[]
     */
    private $subIssues;

    /**
     * @var \Shopware\Components\Jira\API\Model\Version[]
     */
    private $versions;

    /**
     * Instantiates a new issue with the values of the given <b>$data</b> array.
     *
     * @param \Shopware\Components\Jira\API\Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data)
    {
        parent::__construct($data);

        $this->context = $context;
    }

    /**
     * Returns the keywords assigned to this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Keyword[]
     */
    public function getKeywords()
    {
        if (null === $this->keywords) {
            $this->keywords = $this->context->getKeywordService()->loadByIssue($this);
        }
        return $this->keywords;
    }

    /**
     * Returns the components affected by this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Component[]
     */
    public function getComponents()
    {
        if (null === $this->components) {
            $this->components = $this->context->getComponentService()->loadByIssue($this);
        }
        return $this->components;
    }

    /**
     * Returns the sub issues of this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue[]
     */
    public function getSubIssues()
    {
        if (null === $this->subIssues) {
            $this->subIssues = $this->context->getIssueService()->loadSubIssues($this);
        }
        return $this->subIssues;
    }

    /**
     * Returns all versions that are affected by this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function getVersions()
    {
        if (null === $this->versions) {
            $this->versions = $this->context->getVersionService()->loadFixedByIssue($this);
        }
        return $this->versions;
    }
}