<?php
namespace Responsive;

class Account extends \Emotion\Account
{
    public $cssLocator = array(
        'pageIdentifier1'  => 'section.content-main > div > div.account--content',
        'pageIdentifier2'  => 'section.content-main > div > div.register--content',
        'payment' => 'div.account--payment.account--box strong',
        'logout' => 'div.account--menu-container a.link--logout',
        'registrationForm' => 'form.register--form',
        'billingForm' => 'div.account--billing-form > form',
        'shippingForm' => 'div.account--shipping-form > form',
        'paymentForm' => 'div.account--payment-form > form'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'sendButton'     => array('de' => 'Weiter',  'en' => 'Continue')
    );

    public function checkOrder($orderNumber, $articles, $position = 1)
    {
        $this->open();

        $this->clickLink('Meine Bestellungen');

        $locator_prefix = sprintf(
            'div.account--orders-overview > div.panel--table > div:nth-of-type(%d)',
            $position * 2 + 1
        );

        $locators = array();
        $check = array();
        $esd = array();

        //Check positions
        foreach ($articles as $key => $article) {
            $locators['name' . $key] = sprintf(
                '%s > div.panel--tr:nth-of-type(%d) .order--name',
                $locator_prefix,
                $key + 2
            );
            $locators['quantity' . $key] = sprintf(
                '%s > div.panel--tr:nth-of-type(%d) > div:nth-of-type(2)',
                $locator_prefix,
                $key + 2
            );
            $locators['price' . $key] = sprintf(
                '%s > div.panel--tr:nth-of-type(%d) > div:nth-of-type(3)',
                $locator_prefix,
                $key + 2
            );
            $locators['sum' . $key] = sprintf(
                '%s > div.panel--tr:nth-of-type(%d) > div:nth-of-type(4)',
                $locator_prefix,
                $key + 2
            );

            $check['name' . $key] = array('', $article['product']);
            $check['quantity' . $key] = array('', $article['quantity']);
            $check['price' . $key] = array('', $article['price']);
            $check['sum' . $key] = array('', $article['sum']);

            if (!empty($article['esd'])) {
                $esd[] = $article['product'];
            }
        }

        $locators['orderDate'] = $locator_prefix . ' > div.is--odd > div.column--info-data > p:nth-of-type(1)';
        $locators['orderNumber'] = $locator_prefix . ' > div.is--odd > div.column--info-data > p:nth-of-type(2)';

        $check['orderNumber'] = array('', $orderNumber);

        $elements = \Helper::findElements($this, null, $locators);

        foreach ($check as $key => &$checkStep) {
            $checkStep[0] = $elements[$key]->getText();

            if (strpos($key, 'name') === false) {
                $checkStep = \Helper::toFloat($checkStep);
            }
        }

        if (!empty($esd)) {
            $date = $elements['orderDate']->getText();

            $downloads = $this->getEsdArray($date);

            foreach ($downloads as $key => $download) {
                $check['esd' . $key] = array($download, $date .' '. $esd[$key]);
            }
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the order! (%s: %s instead of %s)', $result, $check[$result][0], $check[$result][1]);
            \Helper::throwException(array($message));
        }
    }

    public function register($data)
    {
        $this->verifyPage();

        \Helper::fillForm($this, 'registrationForm', $data);
        \Helper::pressNamedButton2($this, 'sendButton', null, 'de');
    }
}
