<?php

class Shopware_Components_Skrill_Multiselect extends Zend_Form_Element_Multiselect
    {
    public function save ()
        { // $this->Config()->fieldName
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
