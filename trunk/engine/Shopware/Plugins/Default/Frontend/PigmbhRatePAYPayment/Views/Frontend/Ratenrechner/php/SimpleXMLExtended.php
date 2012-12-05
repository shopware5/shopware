<?php

/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */
class SimpleXMLExtended extends SimpleXMLElement
{
    /**
     * This method add a new child element with a cdata tag arround to a SimpleXMLExtended object
     * 
     * @param string $sName
     * @param string $sValue
     * @return SimpleXMLExtended
     */
    public function addCDataChild($sName, $sValue)
    {
        $oNodeOld = dom_import_simplexml($this);
        $oNodeNew = new DOMNode();
        $oDom = new DOMDocument();
        $oDataNode = $oDom->appendChild($oDom->createElement($sName));
        $oDataNode->appendChild($oDom->createCDATASection($sValue));
        $oNodeTarget = $oNodeOld->ownerDocument->importNode($oDataNode, true);
        $oNodeOld->appendChild($oNodeTarget);
        return simplexml_import_dom($oNodeTarget);
    }

}
