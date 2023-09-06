<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Query\QueryException;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Response_ResponseHttp;
use Enlight_Event_EventArgs as EventArgs;
use Exception;
use PDOException;
use ReflectionException;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AttributeSubscriber implements SubscriberInterface
{
    public const redirectCookieString = 'ShopwarePluginsCoreSelfHealingRedirect';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_RouteShutdown' => ['onDispatchEvent', 100],
            'Enlight_Controller_Front_PostDispatch' => ['onDispatchEvent', 100],
            'Enlight_Controller_Front_DispatchLoopShutdown' => ['onDispatchEvent', 100],
        ];
    }

    public function onDispatchEvent(EventArgs $args)
    {
        if (!$args->getResponse()->isException()) {
            return;
        }

        $exception = $args->getResponse()->getException();
        $this->handleException($exception[0]);
    }

    /**
     * @param Exception $exception
     *
     * @throws Exception
     */
    private function handleException($exception)
    {
        $request = new Enlight_Controller_Request_RequestHttp();
        $response = new Enlight_Controller_Response_ResponseHttp();

        if ($this->isModelException($exception)) {
            $generator = $this->container->get(ModelManager::class)->createModelGenerator();
            $result = $generator->generateAttributeModels();
            if ($result['success'] === true) {
                $response->setRedirect(
                    $request->getRequestUri()
                );

                setcookie(self::redirectCookieString, '1', time() + 5);
                $response->sendResponse();
                exit;
            }
            exit(sprintf("Failed to create the attribute models, please check the permissions of the '%s' directory", $generator->getPath()));
        }
    }

    /**
     * Helper function to validate if the thrown exception is an shopware attribute model exception.
     *
     * @return bool
     */
    private function isModelException(Exception $exception)
    {
        if (isset($_COOKIE[self::redirectCookieString])) {
            return false;
        }

        /*
         * This case matches, when a query selects a doctrine association, which isn't defined in the doctrine model
         */
        if ($exception instanceof QueryException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        /*
         * This case matches, when a doctrine attribute model don't exist
         */
        if ($exception instanceof ReflectionException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        /*
         * This case matches, when a doctrine model field defined which not exist in the database
         */
        if ($exception instanceof PDOException && strpos($exception->getFile(), '/Doctrine/DBAL/')) {
            return true;
        }

        /*
         * This case matches, when a parent model selected and the child model loaded the attribute over the lazy loading process.
         */
        if ($exception instanceof MappingException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        return false;
    }
}
