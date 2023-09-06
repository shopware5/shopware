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

use Shopware\Bundle\ControllerBundle\Exceptions\ResourceNotFoundException;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Frontend_Custom extends Enlight_Controller_Action
{
    /**
     * Index action method
     *
     * @throws ResourceNotFoundException
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->Request()->getParam('isXHR')) {
            $this->View()->loadTemplate('frontend/custom/ajax.tpl');
        }

        $shopId = $this->container->get(ContextServiceInterface::class)->getShopContext()->getShop()->getId();

        $staticPage = Shopware()->Modules()->Cms()->sGetStaticPage($this->Request()->get('sCustom'), $shopId);

        if (!\is_array($staticPage)) {
            throw new ResourceNotFoundException('Custom page not found', $this->Request());
        }

        if (!empty($staticPage['link'])) {
            $link = Shopware()->Modules()->Core()->sRewriteLink($staticPage['link'], $staticPage['description']);

            $this->redirect($link, ['code' => Response::HTTP_MOVED_PERMANENTLY]);

            return;
        }

        if (!empty($staticPage['html'])) {
            $this->View()->assign('sContent', $staticPage['html']);
        }

        $this->View()->assign('sCustomPage', $staticPage);

        for ($i = 1; $i <= 3; ++$i) {
            if (empty($staticPage['tpl' . $i . 'variable']) || empty($staticPage['tpl' . $i . 'path'])) {
                continue;
            }
            if (!$this->View()->templateExists($staticPage['tpl' . $i . 'path'])) {
                continue;
            }
            $this->View()->assign(
                $staticPage['tpl' . $i . 'variable'],
                $this->View()->fetch($staticPage['tpl' . $i . 'path'])
            );
        }
    }
}
