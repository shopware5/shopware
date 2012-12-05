<?php
/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */

/**
 * Create a Ratepay_XML Object
 *
 * Set the url to the server with the method "setRatepayserver($ratepayserver)"
 * Set the parameter list with a nested array to create the request XML
 * 
 */
class pi_ratepay_xml_service
{

    var $live;
    var $operation;

    /**
     * This constructor set's a boolean to check if it's live or sandbox
     */
    function pi_ratepay_xml_service($live)
    {
        $this->live = $live;
    }

    // Getter

    /**
     * This method get's the right url to the server
     */
    public function getRatepayserver()
    {
        include('pi_ratepay_xml_service_ini.php');
        if ($this->live) {
            return $pi_ratepay_live_url;
        } else {
            return $pi_ratepay_sandbox_url;
        }
    }

    /**
     *  Use this method for the payment operation's
     */
    public function paymentOperation($xmlRequest)
    {
        $response = $this->httpsPost($xmlRequest->asXML());
        if ($response == false) {
            return false;
        }
        $xmlResponse = new SimpleXMLElement($response);
        return $xmlResponse;
    }

    /**
     * This method send a request to the RatePAY server and get the response
     */
    private function httpsPost($xmlRequest)
    {
        // Initialisation
        $ch = curl_init();
        // Set parameters
        curl_setopt($ch, CURLOPT_URL, $this->getRatepayserver());
        // Return a variable instead of posting it directly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Active the POST method
        curl_setopt($ch, CURLOPT_POST, 1);
        //Set HTTP Version
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //Set HTTP Header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml; charset=UTF-8",
            "Accept: */*",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Connection: keep-alive"
        ));
        // Request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // Execute the connection
        $result = curl_exec($ch);
        // Close it
        curl_close($ch);
        // Uncomment for xml debug
        //return $this->createXML();
        return $result;
    }

    /**
     * Wrapper method to create the XML
     */
    public function getXMLObject()
    {
        $xmlString = '<request version="1.0" xmlns="urn://www.ratepay.com/payment/1_0"></request>';
        require_once('SimpleXMLExtended.php');
        $xml = new SimpleXMLExtended($xmlString);
        return $xml;
    }

}
