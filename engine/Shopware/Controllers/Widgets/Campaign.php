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

/**
 */
class Shopware_Controllers_Widgets_Campaign extends Shopware_Controllers_Widgets_Emotion
{
    /**
     * The getEmotion method for the emotion landing page
     *
     * @param \Shopware\Models\Emotion\Repository $repository
     * @return array
     */
    public function getEmotion($repository)
    {
        /**@var $repository \Shopware\Models\Emotion\Repository */
        $emotionId = (int) $this->Request()->getParam('emotionId');
        $query = $repository->getEmotionById($emotionId);
        $emotion = $query->getQuery()->getArrayResult();
        $emotion['rows'] = $emotion['grid']['rows'];
        $emotion['cols'] = $emotion['grid']['cols'];
        $emotion['cellHeight'] = $emotion['grid']['cellHeight'];
        $emotion['articleHeight'] = $emotion['grid']['articleHeight'];
        $emotion['gutter'] = $emotion['grid']['gutter'];
        return $emotion;
    }

    public function indexAction()
    {
        $this->View()->loadTemplate("widgets/emotion/index.tpl");
        parent::indexAction();
    }
}
