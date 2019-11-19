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

namespace Shopware\Tests\Mink\Page;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\ArticleEvaluation;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Detail extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/detail/index/sArticle/{articleId}?number={number}';

    /**
     * @var string[]
     */
    protected $configuratorTypes = [
        'table' => 'configurator--form',
        'standard' => 'configurator--form upprice--form',
        'select' => 'configurator--form selection--form',
    ];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'productRating' => 'div.product--rating-container .product--rating > meta',
            'productRatingCount' => 'div.product--rating-container .product--rating > span',
            'configuratorForm' => 'form.configurator--form',
            'notificationForm' => 'form.notification--form',
            'notificationSubmit' => '.notification--button',
            'voteForm' => 'form.review--form',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'notificationFormSubmit' => ['de' => 'Eintragen', 'en' => 'Enter'],
            'voteFormSubmit' => ['de' => 'Speichern', 'en' => 'Save'],
            'inquiryLink' => ['de' => 'Fragen zum Artikel?', 'en' => 'Do you have any questions concerning this product?'],
            'compareLink' => ['de' => 'Vergleichen', 'en' => 'Compare'],
            'rememberLink' => ['de' => 'Merken', 'en' => 'Remember'],
            'commentLink' => ['de' => 'Bewerten', 'en' => 'Comment'],
            'addToCartButton' => ['de' => 'In den Warenkorb', 'en' => 'Add to shipping cart'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        $links = Helper::hasNamedLinks($this, ['commentLink', 'inquiryLink']);
        $buttons = Helper::hasNamedButtons($this, ['compareLink', 'rememberLink']);

        if ($links === true && $buttons === true) {
            return;
        }

        $result = [];
        $message = ['You are not on a detail page:'];

        if (is_array($links)) {
            $result = array_merge($result, $links);
        }

        if (is_array($buttons)) {
            $result = array_merge($result, $buttons);
        }

        foreach ($result as $key => $value) {
            $message[] = "- Link '$key' ('$value') not found!";
        }

        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Puts the current article <quantity> times to basket
     *
     * @param int $quantity
     */
    public function addToBasket($quantity = 1)
    {
        $this->fillField('sQuantity', $quantity);
        $this->find('css', "button[name='In den Warenkorb']")->click();
    }

    public function toBasket($offcanvasCart = false)
    {
        if ($offcanvasCart) {
            $text = 'Warenkorb anzeigen';
        } else {
            $text = 'Warenkorb bearbeiten';
        }

        Helper::spin(function () use ($text) {
            try {
                if ($this->find('css', "a[title='$text']")) {
                    return true;
                }
            } catch (\Exception $e) {
                // Page does not contain the text
            }

            return false;
        }, 5);

        $this->clickLink($text);

        /** @var CheckoutCart $checkoutCartPage */
        $checkoutCartPage = $this->getPage('CheckoutCart');
        $checkoutCartPage->verifyPage();
    }

    /**
     * Checks the evaluations of the current article
     *
     * @param string $average
     *
     * @throws \Exception
     */
    public function checkEvaluations(ArticleEvaluation $articleEvaluations, $average, array $evaluations)
    {
        $this->checkRating($articleEvaluations, $average);

        $evaluations = Helper::floatArray($evaluations, ['stars']);
        $result = Helper::assertElements($evaluations, $articleEvaluations);

        if ($result === true) {
            return;
        }

        $messages = ['The following $evaluations are wrong:'];
        foreach ($result as $evaluation) {
            $messages[] = sprintf(
                '%s - Bewertung: %s (%s is "%s", should be "%s")',
                $evaluation['properties']['author'],
                $evaluation['properties']['stars'],
                $evaluation['result']['key'],
                $evaluation['result']['value'],
                $evaluation['result']['value2']
            );
        }
        Helper::throwException($messages);
    }

    /**
     * Sets the configuration of a configurator article
     *
     * @param array[] $configuration
     */
    public function configure(array $configuration)
    {
        $configuratorType = '';

        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            $element = Helper::findElements($this, ['configuratorForm']);

            $configuratorClass = $element['configuratorForm']->getAttribute('class');
            $configuratorType = array_search($configuratorClass, $this->configuratorTypes);
        }

        foreach ($configuration as $group) {
            $field = sprintf('group[%d]', $group['groupId']);
            $this->selectFieldOption($field, $group['value']);

            if ($configuratorType === 'select') {
                $this->pressButton('recalc');
            }
        }

        if ($configuratorType === 'select') {
            return;
        }

        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            $this->pressButton('recalc');
        }
    }

    /**
     * @param string $configuratorOption
     * @param string $configuratorGroup
     *
     * @throws \Exception
     */
    public function canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup)
    {
        $group = $this->findField($configuratorGroup);

        if (empty($group)) {
            $message = sprintf('Configurator group "%s" was not found!', $configuratorGroup);
            Helper::throwException($message);
        }

        $options = $group->findAll('css', 'option');

        foreach ($options as $option) {
            if ($option->getText() == $configuratorOption) {
                $message = sprintf('Configurator option %s founded but should not', $configuratorOption);
                Helper::throwException($message);
            }
        }
    }

    /**
     * Writes an evaluation
     */
    public function writeEvaluation(array $data)
    {
        Helper::fillForm($this, 'voteForm', $data);
        Helper::pressNamedButton($this, 'voteFormSubmit');
    }

    /**
     * Checks a select box
     *
     * @param string $select     Name of the select box
     * @param string $min        First option
     * @param string $max        Last option
     * @param int    $graduation Steps between each options
     *
     * @throws \Exception
     */
    public function checkSelect($select, $min, $max, $graduation)
    {
        $selectBox = $this->findField($select);
        $min = strval($min);
        $max = strval($max);

        if (empty($selectBox)) {
            $message = sprintf('Select box "%s" was not found!', $select);
            Helper::throwException($message);
        }

        $options = $selectBox->findAll('css', 'option');

        $errors = [];
        $optionText = $options[0]->getText();
        $parts = explode(' ', $optionText, 2);
        $value = $parts[0];
        $unit = isset($parts[1]) ? ' ' . $parts[1] : '';

        if ($optionText !== $min) {
            $errors[] = sprintf('The first option of "%s" is "%s"! (should be "%s")', $select, $optionText, $min);
        }

        /** @var NodeElement $option */
        while ($option = next($options)) {
            $optionText = $option->getText();
            $value += $graduation;

            if ($optionText !== $value . $unit) {
                $errors[] = sprintf(
                    'There is the invalid option "%s" in "%s"! ("%s" expected)',
                    $optionText,
                    $select,
                    $value . $unit
                );
            }
        }

        if ($optionText !== $max) {
            $errors[] = sprintf('The last option of "%s" is "%s"! (should be "%s")', $select, $value, $max);
        }

        if (!empty($errors)) {
            Helper::throwException($errors);
        }
    }

    /**
     * Fills the notification form and submits it
     *
     * @param string $email
     */
    public function submitNotification($email)
    {
        $data = [
            [
                'field' => 'sNotificationEmail',
                'value' => $email,
            ],
        ];

        Helper::fillForm($this, 'notificationForm', $data);

        $elements = Helper::findElements($this, ['notificationSubmit']);
        $elements['notificationSubmit']->press();
    }

    public function openEvaluationSection()
    {
        $evaluationTab = $this->getSession()
            ->getPage()
            ->find('css', "a[data-tabname='rating']");

        if ($evaluationTab) {
            $evaluationTab->click();
        }
    }

    /**
     * @param string $average
     *
     * @throws \Exception
     */
    protected function checkRating(ArticleEvaluation $articleEvaluations, $average)
    {
        $elements = Helper::findElements($this, ['productRating', 'productRatingCount']);
        $check = [
            'productRating' => [$elements['productRating']->getAttribute('content'), $average],
            'productRatingCount' => [$elements['productRatingCount']->getText(), count($articleEvaluations)],
        ];

        $check = Helper::floatArray($check);
        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the evaluation! (%s: "%s" instead of %s)', $result, $check[$result][0], $check[$result][1]);
            Helper::throwException($message);
        }
    }
}
