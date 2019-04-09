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

namespace Shopware\Bundle\EmotionBundle\Service\Gateway\Hydrator;

use Shopware\Bundle\EmotionBundle\Struct\Emotion;
use Shopware\Bundle\EmotionBundle\Struct\EmotionTemplate;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class EmotionHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Emotion
     */
    public function hydrate(array $data)
    {
        $emotion = new Emotion();

        $emotion->setId((int) $data['__emotion_id']);
        $emotion->setActive((bool) $data['__emotion_active']);
        $emotion->setName($data['__emotion_name']);
        $emotion->setCols((int) $data['__emotion_cols']);
        $emotion->setCellSpacing((int) $data['__emotion_cell_spacing']);
        $emotion->setCellHeight((int) $data['__emotion_cell_height']);
        $emotion->setArticleHeight((int) $data['__emotion_article_height']);
        $emotion->setRows((int) $data['__emotion_rows']);
        $emotion->setValidFrom($data['__emotion_valid_from'] ? date_create($data['__emotion_valid_from']) : null);
        $emotion->setValidTo($data['__emotion_valid_to'] ? date_create($data['__emotion_valid_to']) : null);
        $emotion->setUserId((int) $data['__emotion_user_id']);
        $emotion->setShowListing((bool) $data['__emotion_show_listing']);
        $emotion->setIsLandingPage((bool) $data['__emotion_is_landingpage']);
        $emotion->setSeoTitle($data['__emotion_seo_title']);
        $emotion->setSeoKeywords($data['__emotion_seo_keywords']);
        $emotion->setSeoDescription($data['__emotion_seo_description']);
        $emotion->setCreateDate($data['__emotion_create_date'] ? date_create($data['__emotion_create_date']) : null);
        $emotion->setModifiedDate($data['__emotion_modified'] ? date_create($data['__emotion_modified']) : null);
        $emotion->setTemplateId((int) $data['__emotion_template_id']);
        $emotion->setDevices(array_map('intval', explode(',', $data['__emotion_device'])));
        $emotion->setFullscreen((bool) $data['__emotion_fullscreen']);
        $emotion->setMode($data['__emotion_mode']);
        $emotion->setPosition((int) $data['__emotion_position']);
        $emotion->setParentId($data['__emotion_parent_id'] !== null ? (int) $data['__emotion_parent_id'] : null);
        $emotion->setIsPreview((bool) $data['__emotion_preview_id']);
        $emotion->setPreviewSecret($data['__emotion_preview_secret']);
        /** @var int[] $categoryIds */
        $categoryIds = explode(',', $data['__emotion_category_ids']);
        $emotion->setCategoryIds($categoryIds);
        /** @var int[] $shopIds */
        $shopIds = explode(',', $data['__emotion_shop_ids']);
        $emotion->setShopIds($shopIds);

        // assign template
        $this->assignTemplate($emotion, $data);

        // assign attribute
        if (!empty($data['__emotionAttribute_id'])) {
            $this->attributeHydrator->addAttribute($emotion, $data, 'emotionAttribute');
        }

        return $emotion;
    }

    private function assignTemplate(Emotion $emotion, array $data)
    {
        $template = new EmotionTemplate();

        $template->setId((int) $data['__emotionTemplate_id']);
        $template->setName($data['__emotionTemplate_name']);
        $template->setFile($data['__emotionTemplate_file']);

        $emotion->setTemplate($template);
    }
}
