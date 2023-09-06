<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Models\Config\Form;
use Shopware\Models\Shop\Locale;
use Shopware\Models\User\Role;
use Shopware\Models\User\User;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ConfigGetFormTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const TEST_USER_USERNAME = 'testuser';
    private const TEST_ROLE_NAME = 'testadminrole';
    private const TEST_FORM_NAME = 'FormUnderTest';
    private const TEST_FORM_ELEMENT_NAME = 'formElementUnderTest';
    private const TEST_LOCALE_LOCALE = 'test_TEST';
    private const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen if there is an en/en_GB locale',
        'de_DE' => 'de_DE: Getestete Einstellung',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
        'en_GB' => 'en_GB: Tested Setting',
        'en' => 'en: Tested Setting (fallback)',
    ];
    private const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
    ];
    private const TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK = [
        'aa_DJ' => 'aa_DJ: Language fallback that should never happen if there is an en/en_GB locale',
        'en' => 'en: Fallback locale translation',
        self::TEST_LOCALE_LOCALE => 'test_TEST: Tested Test Translation',
    ];

    /**
     * Locales that where created on the fly for the tests.
     *
     * @var list<string> an array of locales (e.g. ['test_TEST', 'de_DE'])
     *
     * @see self::getLocaleIdOrCreate creates locales for the tests
     * @see self::formLocaleTestCleanup removes these locales
     */
    private array $temporaryLocales = [];

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

    public function testFormElementSettingTranslationIsUserLocaleIfMatching(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate(self::TEST_LOCALE_LOCALE));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS[self::TEST_LOCALE_LOCALE],
            $storeSettings[0][1]
        );
    }

    public function testFormElementSettingTranslationIsFallbackIfNonTranslatedUserLocale(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('fr_FR'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS['en_GB'],
            $storeSettings[0][1],
            'Should be the en_GB translation, as that is the translation of the first, hard-coded fallback locale.'
        );
    }

    public function testFormElementSettingTranslationIsFallbackIfNonTranslatedUserTerritory(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('de_CH'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS);
        static::assertEquals(
            self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_FALLBACKS['en_GB'],
            $storeSettings[0][1],
            'Should be the en_GB translation, as that is the translation of the first, hard-coded fallback locale.'
            . ' Here, de_DE exists as well, but we define there is no logic to match potentially "similar" locales.'
        );
    }

    public function testFormElementSettingTranslationIsFirstTranslationIfUnmatchedAndWithoutFallbacks(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('en_GB'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK);
        static::assertEquals(
            self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITHOUT_FALLBACK['aa_DJ'],
            $storeSettings[0][1],
            'If no matching translation and no locale-based (en_GB, en) translation exists, should use '
            . 'the first (index-based) translation from the store settings.'
        );
    }

    public function testFormElementSettingTranslationIsEnglishFallbackIfBritishEnglishDoesNotExist(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate('de_DE'));
        $storeSettings = $this->getTranslatedFormElementStoreSettings(self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK);
        static::assertEquals(
            self::TEST_FORM_ELEMENT_STORE_TRANSLATIONS_WITH_EN_FALLBACK['en'],
            $storeSettings[0][1],
            'If neither a matching location, nor the preferred en_GB fallback exist, should fall back to '
            . 'en translation'
        );
    }

    public function testFormElementSettingTranslationCanBeFixedString(): void
    {
        $this->createAndLoginAdminUserWithLocaleId($this->getLocaleIdOrCreate(self::TEST_LOCALE_LOCALE));
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
    private function createAndLoginAdminUserWithLocaleId($localeId): void
    {
        $entityManager = $this->getContainer()->get('models');

        $user = new User();
        $user->setUsername(self::TEST_USER_USERNAME);
        $user->setLocaleId($localeId);
        // Set lockedUntil to the past, so the user can log in.
        $user->setLockedUntil('1970-01-01 00:00:00 UTC');

        // Set password analogously to \Shopware\Commands\AdminCreateCommand::setPassword:
        $passwordEncoderRegistry = $this->getContainer()->get('passwordencoder');
        $defaultEncoderName = $passwordEncoderRegistry->getDefaultPasswordEncoderName();
        $encoder = $passwordEncoderRegistry->getEncoderByName($defaultEncoderName);
        $user->setPassword($encoder->encodePassword('testpassword'));
        $user->setEncoder($encoder->getName());

        // The user must have a role and shall be an admin.
        $userRole = new Role();
        $userRole->setName(self::TEST_ROLE_NAME);
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
            'username' => self::TEST_USER_USERNAME,
            'password' => 'testpassword',
        ]);
        $loginResponse = $this->dispatch('backend/Login/login');
        static::assertGreaterThanOrEqual(200, $loginResponse->getHttpResponseCode(), 'Login failed.');
        static::assertLessThanOrEqual(299, $loginResponse->getHttpResponseCode(), 'Login failed.');
        $this->resetRequest();
        $this->resetResponse();
    }

    private function formLocaleTestCleanup(): void
    {
        $entityManager = $this->getContainer()->get('models');

        $oldEntities = array_merge(
            $entityManager->getRepository(Locale::class)->findBy([
                'locale' => array_merge(
                    [self::TEST_LOCALE_LOCALE],
                    $this->temporaryLocales
                ),
            ]),
            $entityManager->getRepository(User::class)->findBy([
                'username' => self::TEST_USER_USERNAME,
            ]),
            $entityManager->getRepository(Role::class)->findBy([
                'name' => self::TEST_ROLE_NAME,
            ]),
            $entityManager->getRepository(Form::class)->findBy([
                'name' => self::TEST_FORM_NAME,
            ])
        );

        foreach ($oldEntities as $oldEntity) {
            $entityManager->remove($oldEntity);
        }
        $entityManager->flush();
        // Assume temporaryLocales have been removed.
        $this->temporaryLocales = [];
    }

    private function getLocaleIdOrCreate(string $locale): int
    {
        $entityManager = $this->getContainer()->get('models');

        $foundLocale = $entityManager->getRepository(Locale::class)->findOneBy([
            'locale' => $locale,
        ]);

        if ($foundLocale !== null) {
            return $foundLocale->getId();
        }

        $newLocale = new Locale();
        $newLocale->setLocale($locale);
        $newLocale->setLanguage('<' . $locale . ' language name as created for ' . \get_class($this) . '>');
        $newLocale->setTerritory('<' . $locale . ' territory name>');
        $entityManager->persist($newLocale);
        // Flush $newLocale to database,so we can retrieve its new id afterwards.
        $entityManager->flush($newLocale);

        $this->temporaryLocales[] = $locale;

        return $newLocale->getId();
    }

    /**
     * Creates a {@link \Shopware\Models\Config\Form} with a select {@link * \Shopware\Models\Config\Element} with a setting that has
     * $settingsTranslations as its translations.
     *
     * A user must already be logged for a locale to be selectable and to prevent a redirect to /backend/.
     *
     * @param string|array<string, string> $settingsTranslations
     *
     * @return array<list<string>> the element's store
     */
    private function getTranslatedFormElementStoreSettings($settingsTranslations): array
    {
        $entityManager = $this->getContainer()->get('models');

        $form = new Form();
        $form->setName(self::TEST_FORM_NAME);
        $form->setElement(
            'select',
            self::TEST_FORM_ELEMENT_NAME,
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
            urlencode(json_encode($requestFilter, JSON_THROW_ON_ERROR))
        );

        // Disable ACLs for this request:
        $this->Request()->setMethod('GET');
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl();
        try {
            $response = $this->dispatch($requestUrl);
        } finally {
            // Re-enable ACLs:
            $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl(false);
        }
        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);

        static::assertMatchesRegularExpression(',^\s*application/json\s*(;.*)?$,u', $response->getHeader('Content-Type'));

        // Only accept 2xx success codes. Here, redirects may be a sign
        // that the login did not work.
        static::assertGreaterThanOrEqual(200, $response->getHttpResponseCode());
        static::assertLessThanOrEqual(299, $response->getHttpResponseCode());

        $responseBody = $response->getBody();
        static::assertIsString($responseBody);
        $responseDataTransferObject = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

        // Basic assertions about the response to catch non-test-related errors early:
        static::assertTrue($responseDataTransferObject['success']);
        static::assertNotEmpty($responseDataTransferObject['data']);

        $formData = $responseDataTransferObject['data'];
        $formDataErrorMessageSuffix = ' Form data does not match expectation: ' . json_encode($formData);

        static::assertNotEmpty(
            $formData['elements'],
            'Form elements are missing.' . $formDataErrorMessageSuffix
        );
        static::assertNotEmpty(
            $formData['elements'][0]['options'],
            'Form element options are missing.' . $formDataErrorMessageSuffix
        );
        static::assertNotEmpty(
            $formData['elements'][0]['options']['store'],
            'Form element store options are missing.' . $formDataErrorMessageSuffix
        );

        return $formData['elements'][0]['options']['store'];
    }
}
