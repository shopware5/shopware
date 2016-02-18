<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;

class FormContext extends SubContext
{
    /**
     * @Given /^I am on form (\d+)$/
     */
    public function iAmOnForm($id)
    {
        $this->getPage('Form')->open(['formId' => $id]);
    }

    /**
     * @Given /^I should see a captcha$/
     */
    public function iShouldSeeACaptcha()
    {
        $this->getPage('Form')->checkCaptcha();
    }

    /**
     * @When I submit the inquiry form with:
     */
    public function iSubmitTheInquiryFormWith(TableNode $data)
    {
        $this->getPage('Form')->submitInquiryForm($data->getHash());
    }
}
