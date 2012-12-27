<?php

class Shopware_Plugins_Frontend_Skrill_Components_SkrillMultiple extends Zend_Form_MultipleSelect
    {
    public function save ()
        {
        $paymentRow = Shopware()->Payments()->createRow(array(
                                    'name' => 'skrill',
                                    'description' => 'Skrill',
                                    'action' => 'skrill',
                                    'active' => 1,
                                    'pluginID' => $this->getId(),
                                    'additionalDescription' => '',
                                    ))->save();
        
        parent::save();
        }
    }

?>