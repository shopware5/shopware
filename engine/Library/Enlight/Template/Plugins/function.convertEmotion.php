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

use Shopware\Bundle\EmotionBundle\Struct\Element;

/**
 * @param $params
 * @param Smarty_Internal_Template $template
 */
function smarty_function_convertEmotion($params, Smarty_Internal_Template $template)
{
    $emotionConverter = Shopware()->Container()->get('shopware_emotion.emotion_struct_converter');

    $legacyEmotion = $emotionConverter->convertEmotion($params['emotion']);

    /** @var Element $element */
    foreach ($params['emotion']->getElements() as $index => $element) {
        $legacyEmotion['elements'][$index] = $emotionConverter->convertEmotionElement($element);
    }

    $legacyEmotion['elements'] = array_values($legacyEmotion['elements']);

    $template->assign($params['assign'], $legacyEmotion);
}
