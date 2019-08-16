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

use League\Flysystem;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class Shopware_Controllers_Backend_BenchmarkLocalOverview extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
    /**
     * Key to use when saving the date on which the user first tried to access the benchmark results.
     *
     * @var string
     */
    private $waitingSinceConfigKey = 'benchmarkWaitingSince';

    /**
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return ['render'];
    }

    public function renderAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender(false);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $template = $this->getTemplate();

        $this->View()->loadTemplate(sprintf('backend/benchmark/template/local/%s.tpl', $template));

        if ($template === 'waiting') {
            $waitingSinceHours = $this->getWaitingSinceHours();
            $this->View()->assign('waitingSinceHours', $waitingSinceHours);
        }

        if ($template === 'industry_select') {
            $this->View()->assign('shops', $this->getShops());
        }

        $this->View()->assign('benchmarkTranslations', json_encode(
            $this->get('shopware.benchmark_bundle.components.translation')->getAll(),
            JSON_HEX_APOS
        ));

        $this->View()->assign('benchmarkDefaultLanguage', $this->Request()->getParam('lang', 'de'));
    }

    protected function initAcl()
    {
        $this->addAclPermission('render', 'read', 'Insufficient permissions');
    }

    /**
     * @return string
     */
    private function getTemplate()
    {
        $templateParam = $this->Request()->getParam('template');

        if (!$templateParam) {
            $templateParam = $this->getStartingTemplate();
        }

        // Prevents directory traversal
        return FlySystem\Util::normalizeRelativePath($templateParam);
    }

    /**
     * @return string
     */
    private function getStartingTemplate()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

        if ($benchmarkRepository->getConfigsCount() === 0) {
            return 'start';
        }

        return 'waiting';
    }

    /**
     * Returns the amount of hours since first seeing the waiting screen.
     *
     * @return int
     */
    private function getWaitingSinceHours()
    {
        $now = new DateTime('now');
        $diff = $now->diff($this->getWaitingSinceDate());

        return (int) ($diff->h + ($diff->days * 24));
    }

    /**
     * Returns the date on which the user first tried to access the benchmark results.
     * If no date is set, the function initialises the value.
     *
     * @return DateTime
     */
    private function getWaitingSinceDate()
    {
        $identity = $this->getUserIdentity()->id;
        $date = new DateTime('now');
        $db = $this->get('dbal_connection');

        $value = $db->fetchColumn(
            'SELECT `config` FROM `s_core_auth_config` WHERE `user_id` = :id AND `name` = :name',
            [
                ':id' => $identity,
                ':name' => $this->waitingSinceConfigKey,
            ]
        );

        if ($value) {
            return unserialize($value, ['allowed_classes' => false]);
        }

        $db->executeUpdate(
            'INSERT INTO s_core_auth_config (`user_id`, `name`, `config`) VALUES (:id, :name, :config) ON DUPLICATE KEY UPDATE `config`= :config',
            [
                ':id' => $identity,
                ':name' => $this->waitingSinceConfigKey,
                ':config' => serialize($date),
            ]
        );

        return $date;
    }

    /**
     * Tries to fetch the current users identity
     *
     * @return Shopware_Components_Auth|null
     */
    private function getUserIdentity()
    {
        /** @var Shopware_Plugins_Backend_Auth_Bootstrap $plugin */
        $plugin = $this->get('plugins')->get('Backend')->get('Auth');

        try {
            return $plugin->checkAuth()->getIdentity();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    private function getShops()
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('shop.id, shop.name')
            ->from('s_core_shops', 'shop')
            ->where('shop.main_id IS NULL')
            ->execute()
            ->fetchAll();
    }
}
