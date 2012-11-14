<?php

//namespace Shopware\Components\PaymentSkrill;
//use Shopware\Models\Config;

class Shopware_Components_PaymentSkrill_Checkbox extends Zend_Form_Element_Checkbox
    {
    protected $_pluginID;

    public $description;
    public $logoName;
    public $name;

    public function __construct ($name, $pluginID)
        {
        $this->_type = 'Checkbox';
        $this->_name = strtolower($name);
        $this->name = $this->_name;
        $this->_pluginID = $pluginID;

        parent::__construct(strtolower($name), $options);
        }

    public function save ()
        {
        $payment = Shopware()->Payments()->fetchRow(
                        array('name=?' => $this->_name));
        if (!$this->logoName)
            $this->logoName = 'skrill-chkout_de_110x62.gif';

        if (!$payment)
            {
            $payment = Shopware()->Payments()->createRow(array(
                                    'name' => $this->_name,
                                    'description' => $this->description,
                                    'action' => 'payment_skrill',
                                    'active' => $this->getValue(),
                                    'pluginID' => $this->_pluginID,
                                    'additionaldescription' =>
				    '<!-- Skrill -->
				    <img src="https://www.moneybookers.com/ads/skrill-brand-centre/resources/images/' . $this->logoName . '"/>
				    <!-- Skrill --><br/><br/>' .
				    '<div id="skrill_desc">
					Skrill (Moneybookers) ist die sichere Art, weltweit zu bezahlen, ohne ihre Bezahldaten jedesmal neu einzugeben.
					Sie können in 200 Ländern über 100 verschiedene Zahlungsoptionen nutzen, einschließlich aller wichtigen Kredit- und EC- Karten.
				    </div>'
                                    ));
            }
        else
            {
            $payment->active = $this->getValue() ? 1 : 0;
            }

        $payment->save();
        }

    public function deletePayment ()
        {
        $payment = Shopware()->Payments()->fetchRow(array('name=?' => $this->_name));
        if ($payment)
            $payment->delete();
        }
    }