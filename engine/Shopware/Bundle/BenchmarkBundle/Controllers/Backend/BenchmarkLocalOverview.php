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
use Shopware\Bundle\BenchmarkBundle\Repository\ConfigRepositoryInterface;

class Shopware_Controllers_Backend_BenchmarkLocalOverview extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
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

        $this->View()->loadTemplate($this->getTemplate());
    }

    /**
     * @throws \LogicException in case of directory traversal
     *
     * @return string
     */
    private function getTemplate()
    {
        $templateParam = $this->Request()->getParam('template');

        if (!$templateParam) {
            $templateParam = $this->getStartingTemplate();
        }

        // Prevents directory traversal
        $templateParam = FlySystem\Util::normalizeRelativePath($templateParam);

        return sprintf('backend/benchmark/template/local/%s.tpl', $templateParam);
    }

    /**
     * @return string
     */
    private function getStartingTemplate()
    {
        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $configRepository->loadSettings();

        if ((int) $config['termsAccepted'] === 0) {
            return 'start';
        }

        if ($config['business'] === null) {
            return 'branch_select';
        }

        return 'statistics';
    }
}
