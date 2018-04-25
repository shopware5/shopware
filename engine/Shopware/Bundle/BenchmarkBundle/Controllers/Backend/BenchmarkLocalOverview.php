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

        if ($template === 'statistics') {
            $this->View()->assign('benchmarkData', $this->get('shopware.benchmark_bundle.local_collector')->get());
        }
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
        return FlySystem\Util::normalizeRelativePath($templateParam);
    }

    /**
     * @return string
     */
    private function getStartingTemplate()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $benchmarkConfig = $benchmarkRepository->getMainConfig();

        if ($benchmarkConfig->getIndustry() === null) {
            return 'start';
        }

        return 'statistics';
    }
}
