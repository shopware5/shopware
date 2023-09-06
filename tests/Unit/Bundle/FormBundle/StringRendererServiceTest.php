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

namespace Shopware\Tests\Unit\Bundle\FormBundle;

use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\FormBundle\StringRendererService;
use Shopware\Tests\Unit\Bundle\FormBundle\fixtures\View;
use stdClass;

class StringRendererServiceTest extends TestCase
{
    /**
     * @var StringRendererService
     */
    private $stringRendererService;

    /**
     * @var View ;
     */
    private $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stringRendererService = new StringRendererService();
        $this->view = new View();
    }

    public function testStringWithoutPlaceholders()
    {
        static::assertSame('foobar', $this->stringRendererService->render('foobar', [], []));
    }

    public function testStringWithSimplePlaceholder()
    {
        $view = ['someKey' => 'someValue'];
        $input = 'foobar {$someKey}';
        $expectedOutput = 'foobar someValue';

        static::assertSame($expectedOutput, $this->stringRendererService->render($input, $view, []));
    }

    public function testStringWithMultiLevelPlaceholder()
    {
        $view = ['someKey' => [
            'secondKey' => 'secondVal',
        ]];
        $input = 'foobar {$someKey.secondKey}';
        $expectedOutput = 'foobar secondVal';

        static::assertSame($expectedOutput, $this->stringRendererService->render($input, $view, []));
    }

    public function testStringWithNotExistingPlaceholder()
    {
        $view = ['someKey' => 'someValue'];
        $input = 'foobar {$someOtherKey}';
        $expectedString = 'foobar ';

        $renderedString = $this->stringRendererService->render($input, $view, []);
        static::assertSame($expectedString, $renderedString);
    }

    public function testViewVariable()
    {
        $expectedString1 = 'Formular Name: Kontaktformular';

        // make sure that behind "Formular Beschreibung" is a white space like: "Formular Beschreibung "
        $expectedString2 = 'Formular Beschreibung 
Schreiben Sie uns eine eMail.


Wir freuen uns auf Ihre Kontaktaufnahme.

';

        $renderedString1 = $this->stringRendererService->render(
            'Formular Name: {$sSupport.name}',
            $this->view->getAssign(),
            []
        );

        $renderedString2 = $this->stringRendererService->render(
            'Formular Beschreibung {$sSupport.text}',
            $this->view->getAssign(),
            []
        );

        static::assertSame($expectedString1, $renderedString1);
        static::assertSame($expectedString2, $renderedString2);
    }

    public function testElementVariable()
    {
        $element = [
            'id' => '35',
            'name' => 'vorname',
            'note' => 'Feld Name: {$sElement.name}',
            'typ' => 'text',
            'required' => '1',
            'label' => 'Vorname',
            'class' => 'normal',
            'value' => '',
            'error_msg' => '',
        ];

        $expectedString = 'Feld Name: vorname';
        $renderedString = $this->stringRendererService->render($element['note'], $this->view->getAssign(), $element);
        static::assertSame($expectedString, $renderedString);
    }

    public function testViewAndElementVariables()
    {
        $element = [
            'id' => '24',
            'name' => 'anrede',
            'note' => 'Formular Name: {$sSupport.name} und Element Typ: {$sElement.typ}',
            'typ' => 'select',
            'required' => '1',
            'label' => 'Anrede',
            'class' => 'normal',
            'value' => 'Frau;Herr',
            'error_msg' => '',
        ];

        $expectedString = 'Formular Name: Kontaktformular und Element Typ: select';
        $renderedString = $this->stringRendererService->render($element['note'], $this->view->getAssign(), $element);
        static::assertSame($expectedString, $renderedString);
    }

    public function testViewThrowsException()
    {
        $view = $this->view->getAssign();
        $view['sTestException'] = [0 => 1, 2 => 3, 4 => 5];

        $this->expectException(Exception::class);
        $this->stringRendererService->render('testString {$sTestException} test 123', $view, []);
    }

    public function testElementThrowsException()
    {
        $element = [
            'id' => '24',
            'name' => 'anrede',
            'note' => 'Formular Name: {$sSupport.name} und Element Typ: {$sElement.typ}',
            'typ' => 'select',
            'required' => '1',
            'label' => 'Anrede',
            'class' => 'normal',
            'value' => new stdClass(),
            'error_msg' => '',
        ];

        $this->expectException(Exception::class);
        $this->stringRendererService->render(
            'testString {$sElement.value} test 123',
            $this->view->getAssign(),
            $element
        );
    }
}
