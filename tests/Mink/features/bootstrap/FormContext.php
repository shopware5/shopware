<?php

use Behat\Behat\Context\Step;

class FormContext extends SubContext
{
    /**
     * @Given /^I am on form (\d+)$/
     */
    public function iAmOnForm($id)
    {
        $this->getPage('Form')->open(array('formId' => $id));
    }

    /**
     * @Given /^I should see a captcha$/
     */
    public function iShouldSeeACaptcha()
    {
        $this->getPage('Form')->checkCaptcha();
    }

}