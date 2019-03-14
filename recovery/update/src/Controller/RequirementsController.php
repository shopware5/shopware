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

namespace Shopware\Recovery\Update\Controller;

use Shopware\Recovery\Common\DependencyInjection\Container;
use Shopware\Recovery\Common\Utils as CommonUtils;
use Shopware\Recovery\Update\Utils;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Slim;

class RequirementsController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Slim
     */
    private $app;

    public function __construct(Request $request, Response $response, Container $container, Slim $app)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
        $this->app = $app;
    }

    public function checkRequirements()
    {
        $paths = Utils::getPaths(SW_PATH . '/engine/Shopware/Components/Check/Data/Path.xml');

        clearstatcache();
        $systemCheckPathResults = Utils::checkPaths($paths, SW_PATH);

        foreach ($systemCheckPathResults as $value) {
            if (!$value['result']) {
                $fileName = SW_PATH . '/' . $value['name'];
                @mkdir($fileName, 0777, true);
                @chmod($fileName, 0777);
            }
        }

        clearstatcache();
        $systemCheckPathResults = Utils::checkPaths($paths, SW_PATH);

        $hasErrors = false;
        foreach ($systemCheckPathResults as $value) {
            if (!$value['result']) {
                $hasErrors = true;
            }
        }

        $directoriesToDelete = [
            'engine/Library/Mpdf/tmp' => false,
            'engine/Library/Mpdf/ttfontdata' => false,
        ];

        CommonUtils::clearOpcodeCache();

        $results = [];
        foreach ($directoriesToDelete as $directory => $deleteDirecory) {
            $result = true;
            $filePath = SW_PATH . '/' . $directory;

            Utils::deleteDir($filePath, $deleteDirecory);
            if ($deleteDirecory && is_dir($filePath)) {
                $result = false;
                $hasErrors = true;
            }

            if ($deleteDirecory) {
                $results[$directory] = $result;
            }
        }

        if (!$hasErrors && $this->app->request()->get('force') !== '1') {
            // No errors, skip page except if force parameter is set
            $this->app->redirect($this->app->urlFor('dbmigration'));
        }

        $isSkippableCheck = $this->app->config('skippable.check');
        if ($isSkippableCheck && $this->app->request()->get('force') !== '1') {
            // No errors, skip page except if force parameter is set
            $this->app->redirect($this->app->urlFor('dbmigration'));
        }

        $this->app->render('checks.php', [
            'systemCheckResultsWritePermissions' => $systemCheckPathResults,
            'filesToDelete' => $results,
            'error' => $hasErrors,
        ]);
    }
}
