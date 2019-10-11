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

namespace Shopware\Tests\Functional\Controllers\Backend;

class ConfigGetFormTest extends \Enlight_Components_Test_Controller_TestCase
{
    const TEST_USER_USERNAME = 'testuser';
    const TEST_ROLE_NAME = 'testadminrole';
    const TEST_FORM_NAME = 'FormUnderTest';
    const TEST_FORM_ELEMENT_NAME = 'formElementUnderTest';
    const TEST_LOCALE_LOCALE = 'test_TEST';
    const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen if there is an en/en_GB locale',
        'de_DE' => 'de_DE: Getestete Einstellung',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
        'en_GB' => 'en_GB: Tested Setting',
        'en' => 'en: Tested Setting (fallback)',
    ];
    const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
    ];
    const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen if there is an en/en_GB locale',
        'en' => 'en: Fallback locale translation',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
    ];

    /**
     * Locales that where created on the fly for the tests.
     *
     * @var array an array of locales (e.g. ['test_TEST', 'de_DE'])
     *
     * @see self::getLocaleIdOrCreate creates locales for the tests
     * @see self::formLocaleTestCleanup removes these locales
     */
    protected $temporaryLocales = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->formLocaleTestCleanup();
    }

    public function tearDown(): void
    {
        $this->formLocaleTestCleanup();
        $this->reset();
        parent::tearDown();
    }

    public function testFormElementSettingTranslationIsUserLocaleIfMatching()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate(static::TEST_LOCALE_LOCALE));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS[static::TEST_LOCALE_LOCALE],
            $storeSettings[0][1]
        );
    }

    public function testFormElementSettingTranslationIsFallbackIfNonTranslatedUserLocale()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('fr_FR'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS['en_GB'],
            $storeSettings[0][1],
            'Should be the en_GB translation, as that is the translation of the first, hard-coded fallback locale.'
        );
    }

    public function testFormElementSettingTranslationIsFallbackIfNonTranslatedUserTerritory()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('de_CH'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS['en_GB'],
            $storeSettings[0][1],
            'Should be the en_GB translation, as that is the translation of the first, hard-coded fallback locale.'
            . ' Here, de_DE exists as well, but we define there is no logic to match potentially "similar" locales.'
        );
    }

    public function testFormElementSettingTranslationIsFirstTranslationIfUnmatchedAndWithoutFallbacks()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('en_GB'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK);
        static::assertEquals(
            static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK['aa_DJ'],
            $storeSettings[0][1],
            'If no matching translation and no locale-based (en_GB, en) translation exists, should use '
            . 'the first (index-based) translation from the store settings.'
        );
    }

    public function testFormElementSettingTranslationIsEnglishFallbackIfBritishEnglishDoesNotExist()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('de_DE'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK);
        static::assertEquals(
            static::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK['en'],
            $storeSettings[0][1],
            'If neither a matching location, nor the preferred en_GB fallback exist, should fall back to '
            . 'en translation'
        );
    }

    public function testFormElementSettingTranslationCanBeFixedString()
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate(static::TEST_LOCALE_LOCALE));
        $storeSettings = $this->getTranslatedFormElementStoreSettings('A fixed string');
        static::assertEquals(
            'A fixed string',
            $storeSettings[0][1]
        );
    }

    /**
     * Create and signs in new backend admin user with a specified locale.
     *
     * Logging in the user makes the user's identity available for later requests.
     *
     * @param mixed $localeId the id of the new user's locale (a {@link \Shopware\Models\Shop\Locale})
     *
     * @see self::formLocaleTestCleanup removes the user
     */
    protected function createAndLoginAdminUserWithLocaleId($localeId)
    {
        $entityManager = Shopware()->Models();

        $user = new \Shopware\Models\User\User();
        $user->setUsername(static::TEST_USER_USERNAME);
        $user->setLocaleId($localeId);
        // Set lockedUntil to the past, so the user can log in.
        $user->setLockedUntil('1970-01-01 00:00:00 UTC');

        // Set password analogously to \Shopware\Commands\AdminCreateCommand::setPassword:
        /** @var \Shopware\Components\Password\Manager $passworEncoderRegistry */
        $passworEncoderRegistry = Shopware()->Container()->get('passwordencoder');
        $defaultEncoderName = $passworEncoderRegistry->getDefaultPasswordEncoderName();
        $encoder = $passworEncoderRegistry->getEncoderByName($defaultEncoderName);
        $user->setPassword($encoder->encodePassword('testpassword'));
        $user->setEncoder($encoder->getName());

        // The user must have a role and shall be an admin.
        $userRole = new \Shopware\Models\User\Role();
        $userRole->setName(static::TEST_ROLE_NAME);
        $userRole->setDescription('test admin role description');
        $userRole->setSource('custom');
        $userRole->setAdmin(1);
        $userRole->setEnabled(1);
        $user->setRole($userRole);

        $entityManager->persist($userRole);
        $entityManager->persist($user);

        // The user must be flushed to the database before we can log them in.
        $entityManager->flush();

        // Login user.
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'username' => static::TEST_USER_USERNAME,
            'password' => 'testpassword',
        ]);
        $loginResponse = $this->dispatch('backend/Login/login');
        static::assertGreaterThanOrEqual(200, $loginResponse->getHttpResponseCode(), 'Login failed.');
        static::assertLessThanOrEqual(299, $loginResponse->getHttpResponseCode(), 'Login failed.');
        $this->resetRequest();
        $this->resetResponse();
    }

    protected function formLocaleTestCleanup()
    {
        $entityManager = Shopware()->Models();

        $oldEntities = array_merge(
            $entityManager->getRepository('Shopware\Models\Shop\Locale')->findBy([
                'locale' => array_merge(
                    [static::TEST_LOCALE_LOCALE],
                    $this->temporaryLocales
                ),
            ]),
            $entityManager->getRepository('Shopware\Models\User\User')->findBy([
                'username' => static::TEST_USER_USERNAME,
            ]),
            $entityManager->getRepository('Shopware\Models\User\Role')->findBy([
                'name' => static::TEST_ROLE_NAME,
            ]),
            $entityManager->getRepository('Shopware\Models\Config\Form')->findBy([
                'name' => static::TEST_FORM_NAME,
            ])
        );

        foreach ($oldEntities as $oldEntity) {
            $entityManager->remove($oldEntity);
        }
        $entityManager->flush();
        // Assume temporaryLocales have been removed.
        $this->temporaryLocales = [];
    }

    protected function getLocaleIdOrCreate($locale)
    {
        $entityManager = Shopware()->Models();

        $foundLocale = $entityManager->getRepository('Shopware\Models\Shop\Locale')->findOneBy([
            'locale' => $locale,
        ]);

        if ($foundLocale !== null) {
            return $foundLocale->getId();
        }

        $newLocale = new \Shopware\Models\Shop\Locale();
        $newLocale->setLocale($locale);
        $newLocale->setLanguage('<' . $locale . ' language name as created for ' . get_class($this) . '>');
        $newLocale->setTerritory('<' . $locale . ' territory name>');
        $entityManager->persist($newLocale);
        // Flush $newLocale to database,so we can retrieve its new id afterwards.
        $entityManager->flush($newLocale);

        $this->temporaryLocales[] = $newLocale;

        return $newLocale->getId();
    }

    /**
     * Creates a {@link \Shopware\Models\Config\Form} with a select {@link * \Shopware\Models\Config\Element} with a setting that has
     * $settingsTranslations as its translations.
     *
     * A user must already be logged for a locale to be selectable and to prevent a redirect to /backend/.
     *
     * @param string|array $settingsTranslations
     *
     * @return array the element's store
     */
    protected function getTranslatedFormElementStoreSettings($settingsTranslations)
    {
        $entityManager = Shopware()->Models();

        $form = new \Shopware\Models\Config\Form();
        $form->setName(static::TEST_FORM_NAME);
        $form->setElement(
            'select',
            static::TEST_FORM_ELEMENT_NAME,
            [
                'label' => 'The Form Element Under Test',
                'store' => [
                    [
                        'settingUnderTest',
                        $settingsTranslations,
                    ],
                ],
            ]
        );
        $entityManager->persist($form);
        $entityManager->flush($form);

        $requestFilter = [
            [
                'property' => 'id',
                'value' => $form->getId(),
            ],
        ];

        $requestUrl = sprintf(
            'backend/Config/getForm?filter=%s&page=1&_dc=' . time() . '&page=1&start=0&limit=25',
            urlencode(json_encode($requestFilter))
        );

        // Disable ACLs for this request:
        $this->Request()->setMethod('GET');
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
        try {
            $response = $this->dispatch($requestUrl);
        } finally {
            // Re-enable ACLs:
            Shopware()->Plugins()->Backend()->Auth()->setNoAcl(false);
        }

        static::assertRegExp(',^\s*application/json\s*(;.*)?$,u', $response->getHeader('Content-Type'));

        // Only accept 2xx success codes. Here, redirects may be a sign
        // that the login did not work.
        static::assertGreaterThanOrEqual(200, $response->getHttpResponseCode());
        static::assertLessThanOrEqual(299, $response->getHttpResponseCode());

        $responseBody = $response->getBody();
        $responseDataTransferObject = json_decode($responseBody);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'JSON parse error: ' . json_last_error_msg()
                . ' for request to ' . $requestUrl
                . ' which returned ' . $responseBody
            );
        }
        if (!is_object($responseDataTransferObject)) {
            throw new \Exception(
                'Response could not be parsed to an object'
                . ' for request to ' . $requestUrl
                . ' which returned ' . $responseBody
            );
        }

        // Basic assertions about the response to catch non-test-related errors early:
        static::assertTrue($responseDataTransferObject->success);
        static::assertNotEmpty($responseDataTransferObject->data);

        $formData = $responseDataTransferObject->data;
        $formDataErrorMessageSuffix = ' Form data does not match expectation: '
            . json_encode($formData);

        static::assertNotEmpty(
            $formData->elements,
            'Form elements are missing.' . $formDataErrorMessageSuffix
        );
        static::assertNotEmpty(
            $formData->elements[0]->options,
            'Form element options are missing.' . $formDataErrorMessageSuffix
        );
        static::assertNotEmpty(
            $formData->elements[0]->options->store,
            'Form element store options are missing.' . $formDataErrorMessageSuffix
        );

        $storeSettings = $formData->elements[0]->options->store;

        return $storeSettings;
    }
}
