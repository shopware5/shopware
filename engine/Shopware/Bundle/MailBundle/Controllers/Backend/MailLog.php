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

use Shopware\Bundle\MailBundle\Service\Filter\MailFilterInterface;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Mail\Log;

class Shopware_Controllers_Backend_MailLog extends Shopware_Controllers_Backend_Application
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

        $mailBuilder = $this->container->get('shopware.mail_bundle.log_entry_mail_builder');

        $mail = $mailBuilder->build($baseEntry);
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

        $config = $this->container->get('config');
        $values = [];

        foreach ($configKeys as $key) {
            $values[$key] = $config->get($key);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $values,
        ]);
    }

    public function saveConfigAction(bool $mailLogActive, array $mailLogActiveFilters, int $mailLogCleanupMaximumAgeInDays): void
    {
        $allFilters = $this->container->getParameter('shopware.mail_bundle.available_filters');
        $cacheManager = $this->container->get('shopware.cache_manager');
        $writer = $this->container->get('config_writer');

        $enabled = array_values(array_intersect($allFilters, $mailLogActiveFilters));

        $writer->save(self::CONFIG_KEY_MAILLOG_ACTIVE, $mailLogActive);
        $writer->save(self::CONFIG_KEY_MAILLOG_ACTIVE_FILTERS, $enabled);
        $writer->save(self::CONFIG_KEY_MAILLOG_MAX_AGE, $mailLogCleanupMaximumAgeInDays);

        $cacheManager->clearConfigCache();

        $this->View()->assign([
            'success' => true,
            'data' => null,
        ]);
    }

    public function getFiltersAction(): void
    {
        $filters = [];

        foreach ($this->container->getParameter('shopware.mail_bundle.available_filters') as $serviceId) {
            $filters[] = [
                'label' => $this->getFilterSnippet($serviceId),
                'name' => $serviceId,
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

    protected function getFilterSnippet(string $serviceId)
    {
        /** @var MailFilterInterface $filter */
        $filter = $this->container->get($serviceId);
        $snippetManager = $this->container->get('snippets');

        return $snippetManager->getNamespace(self::SNIPPET_NAMESPACE)->get($filter->getName());
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

    protected function overrideRecipients(Enlight_Components_Mail $mail, array $recipients): Enlight_Components_Mail
    {
        if (count($recipients) > 0) {
            $mail->clearRecipients();
            $mail->addTo(array_column($recipients, 'mailAddress'));
        }

        return $mail;
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
}
