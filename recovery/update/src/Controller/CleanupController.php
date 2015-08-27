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

use Shopware\Recovery\Update\CleanupFilesFinder;
use Shopware\Recovery\Update\DummyPluginFinder;
use Shopware\Recovery\Update\Utils;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Slwgetim;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Update\Controller
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CleanupController
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
     * @var Slim
     */
    private $app;

    /**
     * @var DummyPluginFinder
     */
    private $pluginFinder;

    /**
     * @var CleanupFilesFinder
     */
    private $filesFinder;

    /**
     * @param Request $request
     * @param Response $response
     * @param DummyPluginFinder $pluginFinder
     * @param CleanupFilesFinder $filesFinder
     * @param Slim $app
     */
    public function __construct(
        Request $request,
        Response $response,
        DummyPluginFinder $pluginFinder,
        CleanupFilesFinder $filesFinder,
        Slim $app
    ) {
        $this->request      = $request;
        $this->response     = $response;
        $this->app          = $app;
        $this->pluginFinder = $pluginFinder;
        $this->filesFinder  = $filesFinder;
    }

    public function cleanupOldFiles()
    {
        $_SESSION['DB_DONE'] = true;

        $cleanupList = array_merge(
            $this->pluginFinder->getDummyPlugins(),
            $this->filesFinder->getCleanupFiles()
        );

        if (count($cleanupList) == 0) {
            $_SESSION['CLEANUP_DONE'] = true;
            $this->response->redirect($this->app->urlFor('done'));
        }

        if ($this->request->isPost()) {
            $result = [];
            foreach ($cleanupList as $path) {
                $result = array_merge($result, Utils::cleanPath($path));
            }

            if (count($result) == 0) {
                $_SESSION['CLEANUP_DONE'] = true;
                $this->response->redirect($this->app->urlFor('done'));
            } else {
                $result = array_map(
                    function ($path) {
                        return substr($path, strlen(SW_PATH)+1);
                    },
                    $result
                );
                $this->app->render('cleanup.php', ['cleanupList' => $result, 'error' => true]);
            }
        } else {
            $cleanupList = array_map(
                function ($path) {
                    return substr($path, strlen(SW_PATH)+1);
                },
                $cleanupList
            );

            $this->app->render('cleanup.php', ['cleanupList' => $cleanupList, 'error' => false ]);
        }
    }
}
