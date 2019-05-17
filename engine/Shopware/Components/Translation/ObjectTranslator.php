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

namespace Shopware\Components\Translation;

/**
 * Translation context that can be used to translate properties on a specific translatable object type.
 */
class ObjectTranslator
{
    /**
     * @var int
     */
    private $fallback;

    /**
     * @var int
     */
    private $language;

    /**
     * @var bool
     */
    private $loaded;

    /**
     * @var array
     */
    private $translations;

    /**
     * @var \Shopware_Components_Translation
     */
    private $translationService;

    /**
     * @var string
     */
    private $type;

    public function __construct(\Shopware_Components_Translation $translationService, string $type, int $language, int $fallback)
    {
        $this->language = $language;
        $this->loaded = false;
        $this->type = $type;
        $this->fallback = $fallback ?: $translationService->getFallbackLocaleId($language);
        $this->translationService = $translationService;
    }

    /**
     * Translates an array property.
     */
    public function translateObjectProperty(array $object, string $translationIndex, ?string $objectIndex = null, ?string $fallback = null): array
    {
        if (!$this->areTranslationsLoaded()) {
            $this->loadTranslations();
        }

        $objectIndex = $objectIndex ?: $translationIndex;

        $translation = $this->translations[$object['id']];
        if ($translation && !empty($translation[$translationIndex])) {
            $object[$objectIndex] = $translation[$translationIndex];
        } elseif ($fallback) {
            $object[$objectIndex] = $fallback;
        }

        return $object;
    }

    /**
     * Loads all translations.
     */
    private function loadTranslations(): void
    {
        $this->translations = $this->translationService->readBatchWithFallback($this->language, $this->fallback, $this->type);
        $this->loaded = true;
    }

    /**
     * Determines if the translations have been loaded.
     */
    private function areTranslationsLoaded(): bool
    {
        return $this->loaded;
    }
}
