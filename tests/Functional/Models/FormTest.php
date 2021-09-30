<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Models;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Form\Form;
use Shopware\Models\Form\Repository;

class FormTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var array<string, string>
     */
    public array $testData = [
        'name' => 'Testform123',
        'text' => 'This is a Testform',
        'email' => 'max@mustermann.com',
        'emailTemplate' => 'Test Email Template',
        'emailSubject' => 'Test Email Subject',
        'text2' => 'Test Text2',
    ];

    protected ModelManager $em;

    /**
     * @var Repository
     */
    protected $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository(Form::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $form = $this->repo->findOneBy(['name' => 'Testform123']);

        if (!empty($form)) {
            $this->em->remove($form);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Test case
     */
    public function testGetterAndSetter(): void
    {
        $form = new Form();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $form->$setMethod($value);

            static::assertEquals($form->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testFromArrayWorks(): void
    {
        $form = new Form();
        $form->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($form->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testFormShouldBePersisted(): void
    {
        $form = new Form();
        $form->fromArray($this->testData);

        $this->em->persist($form);
        $this->em->flush();

        $formId = $form->getId();

        // remove form from entity manager
        $this->em->detach($form);
        unset($form);

        $form = $this->repo->find($formId);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($form->$getMethod(), $value);
        }
    }
}
