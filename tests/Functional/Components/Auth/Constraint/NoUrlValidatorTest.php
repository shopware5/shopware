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

namespace Shopware\Tests\Functional\Components\Auth\Constraint;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Auth\Constraint\NoUrl;
use Shopware\Components\Auth\Constraint\NoUrlValidator;
use Shopware\Components\Validator\NoUrlValidator as Validator;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class NoUrlValidatorTest extends TestCase
{
    use ContainerTrait;

    public function testIsNotValidWithUrlInText(): void
    {
        $testString = 'https://example.de';

        $snippetManager = $this->getContainer()->get('snippets');

        $noUrlValidator = new NoUrlValidator(
            $snippetManager,
            new Validator()
        );

        // We don't need addError to work. We only need to know if it is called
        $this->expectExceptionMessage('Call to a member function buildViolation() on null');
        $noUrlValidator->validate($testString, new NoUrl());
    }

    public function testIsValidWithoutUrlInText(): void
    {
        $testString = 'Test';

        $snippetManager = $this->getContainer()->get('snippets');

        $noUrlValidator = new NoUrlValidator(
            $snippetManager,
            new Validator()
        );

        $noUrlValidator->validate($testString, new NoUrl());

        $this->expectNotToPerformAssertions();
    }
}
