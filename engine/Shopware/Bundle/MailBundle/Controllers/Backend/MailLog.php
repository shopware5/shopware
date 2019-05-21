<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\MailBundle\Controllers\Backend;

use Enlight_Components_Mail;
use Shopware\Bundle\MailBundle\Service\Filter\MailFilterInterface;
use Shopware\Bundle\MailBundle\Service\LogEntryMailBuilder;
use Shopware\Components\CacheManager;
use Shopware\Components\ConfigWriter;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Mail\Log;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;
use Traversable;

class MailLog extends \Shopware_Controllers_Backend_Application
{
    public const CONFIG_KEY_MAILLOG_ACTIVE = 'mailLogActive';
    public const CONFIG_KEY_MAILLOG_ACTIVE_FILTERS = 'mailLogActiveFilters';
    public const CONFIG_KEY_MAILLOG_MAX_AGE = 'mailLogCleanupMaximumAgeInDays';

    public const JOIN_ALIAS_ORDER = 'o';
    public const JOIN_ALIAS_RECIPIENTS = 'r';

    public const SNIPPET_NAMESPACE = 'backend/mail_log/filters';

    /**
     * {@inheritdoc}
     */
    protected $model = Log::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'mailLog';

    /**
     * @var LogEntryMailBuilder
     */
    protected $mailBuilder;

    /**
     * @var Shopware_Components_Config
     */
    protected $config;

    /**
     * @var ConfigWriter
     */
    protected $writer;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var Traversable
     */
    protected $filters;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    public function __construct(
        LogEntryMailBuilder $mailBuilder,
        Shopware_Components_Config $config,
        ConfigWriter $writer,
        CacheManager $cacheManager,
        Shopware_Components_Snippet_Manager $snippetManager,
        Traversable $filters
    ) {
        $this->mailBuilder = $mailBuilder;
        $this->config = $config;
        $this->writer = $writer;
        $this->cacheManager = $cacheManager;
        $this->snippetManager = $snippetManager;
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(): void
    {
        $this->View()->assign([
            'success' => false,
            'data' => [
                'message' => 'Creating log entries via the backend module is disabled.',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAction(): void
    {
        $this->View()->assign([
            'success' => false,
            'data' => [
                'message' => 'Changing log entries via the backend module is disabled.',
            ],
        ]);
    }

    public function resendMailAction(int $id, array $recipients): void
    {
        $view = $this->View();
        $baseEntry = $this->getRepository()->find($id);

        if ($view === null) {
            return;
        }

        if (!($baseEntry instanceof Log)) {
            $view->assign([
                'success' => false,
                'data' => null,
            ]);

            return;
        }

        /** @var Enlight_Components_Mail $mail */
        $mail = $this->mailBuilder->build($baseEntry);
        $mail = $this->overrideRecipients($mail, $recipients);

        $mail->send();

        $view->assign([
            'success' => true,
            'data' => null,
        ]);
    }

    public function getConfigAction(): void
    {
        $configKeys = [
            self::CONFIG_KEY_MAILLOG_ACTIVE,
            self::CONFIG_KEY_MAILLOG_MAX_AGE,
            self::CONFIG_KEY_MAILLOG_ACTIVE_FILTERS,
        ];

        $values = [];

        foreach ($configKeys as $key) {
            $values[$key] = $this->config->get($key);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $values,
        ]);
    }

    public function saveConfigAction(bool $mailLogActive, array $mailLogActiveFilters, int $mailLogCleanupMaximumAgeInDays): void
    {
        $this->writer->save(self::CONFIG_KEY_MAILLOG_ACTIVE, $mailLogActive);
        $this->writer->save(self::CONFIG_KEY_MAILLOG_ACTIVE_FILTERS, $mailLogActiveFilters);
        $this->writer->save(self::CONFIG_KEY_MAILLOG_MAX_AGE, $mailLogCleanupMaximumAgeInDays);

        $this->cacheManager->clearConfigCache();

        $this->View()->assign([
            'success' => true,
            'data' => null,
        ]);
    }

    public function getFiltersAction(): void
    {
        $filters = [];
        $snippets = $this->snippetManager->getNamespace(self::SNIPPET_NAMESPACE);

        /** @var MailFilterInterface $filter */
        foreach ($this->filters as $filter) {
            $filters[] = [
                'label' => $snippets->get($filter->getName()),
                'name' => $filter->getName(),
            ];
        }

        $this->View()->assign([
            'success' => true,
            'data' => $filters,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function initAcl()
    {
        $this->addAclPermission('index', 'read', 'Insufficient permissions');
        $this->addAclPermission('load', 'read', 'Insufficient permissions');
        $this->addAclPermission('list', 'read', 'Insufficient permissions');
        $this->addAclPermission('detail', 'read', 'Insufficient permissions');
        $this->addAclPermission('create', 'manage', 'Insufficient permissions');
        $this->addAclPermission('update', 'manage', 'Insufficient permissions');
        $this->addAclPermission('delete', 'manage', 'Insufficient permissions');
        $this->addAclPermission('resendMail', 'resend', 'Insufficient permissions');
        $this->addAclPermission('getConfig', 'manage', 'Insufficient permissions');
        $this->addAclPermission('getFilters', 'manage', 'Insufficient permissions');
        $this->addAclPermission('saveConfig', 'manage', 'Insufficient permissions');
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterConditions($filters, $model, $alias, $whiteList = [])
    {
        $conditions = parent::getFilterConditions($filters, $model, $alias, $whiteList);

        // Simple check to see if there were any filter conditions which couldn't be handled by the parent method
        if (count($conditions) >= count($filters)) {
            return $conditions;
        }

        $fields = $this->getModelFields($this->model);

        foreach ($filters as $filter) {
            $property = $filter['property'];
            $operator = $filter['operator'];
            $expression = $filter['expression'];
            $value = $filter['value'];

            // The property can already be filtered correctly if it is available via getModelFields
            if (array_key_exists($property, $fields)) {
                continue;
            }

            if ($property === 'recipients') {
                $property = self::JOIN_ALIAS_RECIPIENTS . '.id';
            } elseif ($property === 'order') {
                $property = self::JOIN_ALIAS_ORDER . '.id';
            }

            $conditions[] = [
                'property' => $property,
                'operator' => $operator,
                'expression' => $expression,
                'value' => $value,
            ];
        }

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListQuery(): QueryBuilder
    {
        $builder = parent::getListQuery();

        $builder->leftJoin('mailLog.order', self::JOIN_ALIAS_ORDER)
            ->leftJoin('mailLog.recipients', self::JOIN_ALIAS_RECIPIENTS)
            ->addSelect([self::JOIN_ALIAS_ORDER, self::JOIN_ALIAS_RECIPIENTS]);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDetailQuery($id): QueryBuilder
    {
        $builder = parent::getDetailQuery($id);

        $builder->leftJoin('mailLog.order', self::JOIN_ALIAS_ORDER)
            ->leftJoin('mailLog.recipients', self::JOIN_ALIAS_RECIPIENTS)
            ->addSelect([self::JOIN_ALIAS_ORDER, self::JOIN_ALIAS_RECIPIENTS]);

        return $builder;
    }

    private function overrideRecipients(Enlight_Components_Mail $mail, array $recipients): Enlight_Components_Mail
    {
        if (count($recipients) > 0) {
            $mail->clearRecipients();
            $mail->addTo(array_column($recipients, 'mailAddress'));
        }

        return $mail;
    }
}
