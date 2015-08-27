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

/**
 * Shopware document / pdf controller
 */
class Shopware_Controllers_Backend_Document extends Enlight_Controller_Action
{
    /**
     * Generate pdf invoice
     * @access public
     */
    public function indexAction()
    {
        $id = $this->Request()->id;
        $netto = $this->Request()->ust_free;
        if ($netto == "false") {
            $netto = false;
        }
        $typ = $this->Request()->typ;
        $voucher = $this->Request()->voucher;
        $date = $this->Request()->date;
        $delivery_date = $this->Request()->delivery_date;
        $bid = $this->Request()->bid;
        $this->View()->setTemplate();
        $document = Shopware_Components_Document::initDocument(
            $id,
            $typ,
            array(
                "netto" => $netto,
                "bid" => $bid,
                "voucher" => $voucher,
                "date" => $date,
                "delivery_date" => $delivery_date,
                "shippingCostsAsPosition"=>true,
                "_renderer"=>"pdf",
                "_preview" => $this->Request()->preview,
                "_previewForcePagebreak" => $this->Request()->pagebreak,
                "_previewSample" => $this->Request()->sampleData,
                "docComment" => utf8_decode($this->Request()->docComment),
                "forceTaxCheck" => $this->Request()->forceTaxCheck
            )
        );

        $document->render();
    }

    /**
     * Duplicate document properties
     */
    public function duplicatePropertiesAction()
    {
        $this->View()->setTemplate();
        $id = $this->Request()->id;

        // Update statement
        $getDocumentTypes = Shopware()->Db()->fetchAll(
            "SELECT DISTINCT id FROM s_core_documents WHERE id != ?",
            array($id)
        );
        foreach ($getDocumentTypes as $targetID) {
            $deleteOldRows = Shopware()->Db()->query(
                "DELETE FROM s_core_documents_box WHERE documentID = ?",
                array($targetID["id"])
            );
            $sqlDuplicate = "INSERT IGNORE INTO s_core_documents_box
                SELECT NULL AS id, ? AS documentID , name, style, value
                FROM s_core_documents_box WHERE `documentID` = ?;
            ";
            Shopware()->Db()->query($sqlDuplicate, array($targetID["id"], $id));
        }
    }
}
