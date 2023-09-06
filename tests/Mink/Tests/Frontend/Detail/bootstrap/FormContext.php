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

namespace Shopware\Tests\Mink\Tests\Frontend\Detail\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Form\Form;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class FormContext extends SubContext
{
    /**
     * @Given /^I am on form (\d+)$/
     */
    public function iAmOnForm(int $id): void
    {
        $this->getPage(Form::class)->open(['formId' => $id]);
    }

    /**
     * @Given /^I should see a captcha$/
     */
    public function iShouldSeeACaptcha(): void
    {
        $this->getPage(Form::class)->checkCaptcha();
    }

    /**
     * @When I submit the inquiry form with:
     */
    public function iSubmitTheInquiryFormWith(TableNode $data): void
    {
        $this->getPage(Form::class)->submitInquiryForm($data->getHash());
    }
}
