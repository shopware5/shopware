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

class Shopware_Controllers_Widgets_Campaign extends Shopware_Controllers_Widgets_Emotion
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     *
     * The getEmotion method for the emotion landing page
     *
     * @param \Shopware\Models\Emotion\Repository $repository
     *
     * @return array
     */
    public function getEmotion($repository)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        /** @var \Shopware\Models\Emotion\Repository $repository */
        $emotionId = (int) $this->Request()->getParam('emotionId');
        $query = $repository->getEmotionById($emotionId);

        return $query->getQuery()->getArrayResult();
    }

    public function indexAction()
    {
        $this->View()->loadTemplate('widgets/emotion/index.tpl');
        parent::indexAction();
    }
}
