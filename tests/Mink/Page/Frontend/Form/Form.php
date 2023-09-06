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

namespace Shopware\Tests\Mink\Page\Frontend\Form;

use Behat\Mink\Element\NodeElement;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class Form extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = 'shopware.php?sViewport=forms&sFid={formId}';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'captchaPlaceholder' => 'div.captcha--placeholder',
            'captchaImage' => 'div.captcha--placeholder img',
            'captchaHidden' => 'div.captcha--placeholder input',
            'inquiryForm' => 'form#support',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'submitButton' => ['de' => 'Senden', 'en' => 'Send'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
     */
    public function verifyPage(): void
    {
        $errors = [];

        if (!Helper::hasNamedButton($this, 'submitButton')) {
            $errors[] = '- submit button not found!';
        }

        if (!$errors) {
            return;
        }

        $message = ['You are not on a form page:'];
        $message = array_merge($message, $errors);
        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Checks, whether a captcha exists and has loaded correctly
     *
     * @throws Exception
     */
    public function checkCaptcha(): void
    {
        $placeholderSelector = Helper::getRequiredSelector($this, 'captchaPlaceholder');
        $placeholder = $this->find('css', $placeholderSelector);
        if (!$placeholder instanceof NodeElement) {
            Helper::throwException('Could not find captchaPlaceholder');
        }

        $parentFormInput = $placeholder->find('xpath', "/ancestor::form[1]/descendant::input[@type='text']");
        if (!$parentFormInput instanceof NodeElement) {
            Helper::throwException('Could not find parent form input');
        }
        $parentFormInput->focus();

        if (!$this->getSession()->wait(5000, "$('$placeholderSelector').children().length > 0")) {
            $message = 'The captcha was not loaded or does not exist!';
            Helper::throwException($message);
        }

        $this->getSession()->wait(5000);

        $elements = Helper::findElements($this, ['captchaPlaceholder']);
        if (empty($elements['captchaPlaceholder']->getText())) {
            $message = 'The captcha was not loaded correctly!';
            Helper::throwException($message);
        }
    }

    /**
     * Fills the fields of the inquiry form with $data and submits it
     */
    public function submitInquiryForm(array $data): void
    {
        Helper::fillForm($this, 'inquiryForm', $data);
        Helper::pressNamedButton($this, 'submitButton');
    }
}
