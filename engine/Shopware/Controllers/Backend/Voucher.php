<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Voucher
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Marcel Schmäing
 * @author     $Author$
 */

use Shopware\Models\Voucher\Voucher as Voucher,
    Doctrine\ORM\AbstractQuery;
/**
 * Shopware Backend Controller for the Voucher Module
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Voucher extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Models\Voucher\Repository
     */
    protected $voucherRepository = null;

    /**
     * Helper function to get access to the voucher repository.
     * @return \Shopware\Models\Voucher\Repository
     */
    private function getVoucherRepository() {
    	if ($this->voucherRepository === null) {
    		$this->voucherRepository = Shopware()->Models()->getRepository('Shopware\Models\Voucher\Voucher');
    	}
    	return $this->voucherRepository;
    }


    /**
     * Internal helper function to get access to the entity manager.
     * @return null
     */
    private function getManager() {
        if ($this->manager === null) {
            $this->manager= Shopware()->Models();
        }
        return $this->manager;
    }


    /**
     * Registers the different acl permission for the different controller actions.
     *
     * @return void
     */
    protected function initAcl()
    {
        /**
         * permission to delete voucher(s)
         */
        $this->addAclPermission('deleteVoucherAction', 'delete','Insufficient Permissions');

        /**
         * permission to list all vouchers
         */
        $this->addAclPermission('getVoucherAction', 'read','Insufficient Permissions');

        /**
         * permission to list all individual vouchers
         */
        $this->addAclPermission('getVoucherCodesAction', 'read','Insufficient Permissions');

        /**
         * permission to create individual voucher codes
         */
        $this->addAclPermission('createVoucherCodesAction', 'generate','Insufficient Permissions');

        /**
         * permission to export individual voucher codes
         */
        $this->addAclPermission('exportVoucherCodeAction', 'export','Insufficient Permissions');
    }

    /**
     * Disable template engine for all actions
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (in_array($this->Request()->getActionName(), array(
            'validateOrderCode', 'validateVoucherCode', 'validateDescription'))) {
            $this->Front()->Plugins()->Json()->setRenderer(false);
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Deletes a Supplier from the database
     *
     * @return void
     */
    public function deleteVoucherAction()
    {
        $multipleVouchers = $this->Request()->getPost('vouchers');
        $voucherRequestData = empty($multipleVouchers) ? array(array("id" => $this->Request()->id)) : $multipleVouchers;
        try {
            foreach ($voucherRequestData as $voucher) {
                /**@var $model \Shopware\Models\Voucher\Voucher*/
                $model = $this->getVoucherRepository()->find($voucher["id"]);
                $this->getManager()->remove($model);
            }
            $this->getManager()->flush();
            $this->View()->assign(array('success' => true, 'data' => $voucherRequestData));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Returns a JSON string containing all Suppliers
     *
     * @return void
     */
    public function getVoucherAction()
    {
        try {

            $offset = intval($this->Request()->start);
            $limit = intval($this->Request()->limit);
            $filter = $this->Request()->filter;
            $filter = $filter[0]["value"];
            $sqlBindings = array();
            //search for values
            if (!empty($filter)) {
                $searchSQL = "AND v.description LIKE :filter
                            OR v.vouchercode LIKE :filter
                            OR v.value LIKE :filter";
                $sqlBindings["filter"] = "%".$filter."%";
            }
            //sorting data
            $sortData = $this->Request()->sort;
            $sortField = $sortData[0]["property"];
            $dir = $sortData[0]["direction"];
            $sort = "";
            if (!empty($sortField) && $dir === "ASC" || $dir === "DESC") {
                //to prevent sql-injections
                $sortField = Shopware()->Db()->quoteIdentifier($sortField);
                $sort = "ORDER BY " . $sortField . " " . $dir;
            }

            $sql = "
                SELECT SQL_CALC_FOUND_ROWS v.id,
                        v.description,
                        v.vouchercode as voucherCode,
                        v.numberofunits as numberOfUnits,
                        v.valid_from as validFrom,
                        v.valid_to as validTo,
                        v.value,
                        v.modus,
                        v.percental,
                        IF( modus = '0',
                (SELECT count(*) FROM s_order_details as d WHERE articleordernumber =v.ordercode AND d.ordernumber!=0),
                (SELECT count(*) FROM s_emarketing_voucher_codes WHERE voucherID =v.id AND cashed=1))  AS checkedIn
                FROM s_emarketing_vouchers as v
                WHERE (modus = 1 OR modus = 0)
                {$searchSQL}

                {$sort}
                LIMIT {$offset}, {$limit}
            ";

            $vouchers = Shopware()->Db()->fetchAll($sql, $sqlBindings);
            $sql = "SELECT FOUND_ROWS()";
            $totalCount = Shopware()->Db()->fetchOne($sql, array());
            $this->View()->assign(array('success' => true, 'data' => $vouchers, 'totalCount' => $totalCount));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Returns a JSON string containing all Voucher Codes
     *
     * @return void
     */
    public function getVoucherCodesAction()
    {
        try {
            $voucherId = intval($this->Request()->voucherID);

            $orderBy = $this->Request()->getParam('sort');
            $filter = $this->Request()->getParam('filter');
            $filter = $filter[0]["value"];
            $offset = $this->Request()->getParam('start');
            $limit = $this->Request()->getParam('limit');

            $dataQuery = $this->getVoucherRepository()
                              ->getVoucherCodeListQuery($voucherId, $filter, $orderBy, $offset, $limit);

            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $voucherCodes = $dataQuery->getArrayResult();

            $this->View()->assign(array('success' => true, 'data' => $voucherCodes, 'totalCount' => $totalCount));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * creates all necessary voucher codes
     *
     * @return void
     */
    public function createVoucherCodesAction()
    {
        $voucherId = intval($this->Request()->voucherId);
        $numberOfUnits = intval($this->Request()->numberOfUnits);
        $createdVoucherCodes = 0;

        //first delete available codes
        $deleteQuery = $this->getVoucherRepository()->getVoucherCodeDeleteByVoucherIdQuery($voucherId);
        $deleteQuery->execute();
        do {
            //generate voucher codes till the numberOfUnits is reached
            $this->generateVoucherCodes($voucherId,($numberOfUnits - $createdVoucherCodes));

            $query = $this->getVoucherRepository()->getVoucherCodeCountQuery($voucherId);
            $result = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
            $createdVoucherCodes = $result["countCode"];

        } while ($createdVoucherCodes < $numberOfUnits);

        $this->View()->assign(array('success' => true));
    }

    /**
     * exports all voucher codes via csv
     *
     * @return void
     */
    public function exportVoucherCodeAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $voucherId = intval($this->Request()->voucherId);

        $dataQuery = $this->getVoucherRepository()->getVoucherCodeListQuery($voucherId);
        $resultArray = $dataQuery->getArrayResult();

        $this->Response()->setHeader('Content-Type','text/csv; charset=utf-8');
        $this->Response()->setHeader('Content-Disposition','attachment;filename=voucherCodes.csv');
        //use this to set the BOM to show it in the right way for excel and stuff
        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array_keys($resultArray[0]), ";");

        foreach ($resultArray as $value) {
            fputcsv($fp, $value, ";");
        }
        fclose($fp);
    }

    /**
     * helper Method to generate all needed voucher codes
     *
     * @param $voucherId
     * @param $numberOfUnits
     */
    protected function generateVoucherCodes($voucherId, $numberOfUnits) {
        $values = array();
        //wrote in standard sql cause in this case its way faster than doctrine models
        $sql = "INSERT INTO s_emarketing_voucher_codes (voucherID, code) VALUES";
        for($i = 1; $i <= $numberOfUnits; $i++) {
            $code = strtoupper(substr(uniqid("",true),6,8));
            $values[] = "( $voucherId, '". $code. "' )";
            // send the query every each 10000 times
            if($i % 10000 == 0 || $numberOfUnits==$i) {
                Shopware()->Db()->query($sql. implode(',' , $values));
                $values = array();
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    //Data Validation Methods//////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////
    /**
     * Action for the Detail Voucher Form to load all needed data
     */
    public function getVoucherDetailAction()
    {
        try {

            $voucherID = intval($this->Request()->voucherID);

            $query = $this->getVoucherRepository()->getVoucherDetailQuery($voucherID);
            $model = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            $voucher = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            if ($model->getValidFrom() instanceof \DateTime) {
                $voucher["validFrom"] = $model->getValidFrom()->format("d.m.Y");
            } else {
                $voucher["validFrom"] = null;
            }
            if ($model->getValidTo() instanceof \DateTime) {
                $voucher["validTo"] = $model->getValidTo()->format("d.m.Y");
            } else {
                $voucher["validTo"] = null;
            }

            $this->View()->assign(array('success' => true, 'data' => $voucher, 'total' => 1));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }


    /**
     * get the Tax configuration
     * Used for the backend tax-combobox
     */
    public function getTaxConfigurationAction(){

        $builder = $this->getManager()->Tax()->createQueryBuilder('t');
        $builder->orderBy("t.id","ASC");
        $tax = $builder->getQuery()->getArrayResult();

        $this->View()->assign(array("success"=>true, "data"=>$tax));
    }

    /**
     * Creates a new voucher with the passed values
     *
     * @return void
     */
    public function saveVoucherAction()
    {
        $params = $this->Request()->getParams();
        $voucherId = empty($params['voucherID']) ? $params["id"] : $params['voucherID'];
        if(!empty($voucherId)){
            if (!$this->_isAllowed('update', 'voucher')) {
                return;
            }
            //edit voucher
            $voucher = $this->getVoucherRepository()->find($voucherId);
        }
        else{
            if (!$this->_isAllowed('create', 'voucher')) {
                return;
            }
            //new voucher
            $voucher = new Voucher();
        }

            //save empty values
        if (empty($params['validFrom'])) {
            $params['validFrom'] = null;
        }
        if (empty($params['validTo'])) {
            $params['validTo'] = null;
        }

        if (empty($params['customerGroup'])) {
            $params['customerGroup'] = null;
        }
        if (empty($params['shopId'])) {
            $params['shopId'] = null;
        }
        if (empty($params['bindToSupplier'])) {
            $params['bindToSupplier'] = null;
        }

        $params['attribute'] = $params['attribute'][0];
        $voucher->fromArray($params);
        try {
            $this->getManager()->persist($voucher);
            $this->getManager()->flush();
            $data = $this->getVoucherRepository()
                         ->getVoucherDetailQuery($voucher->getId())
                         ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);


            $this->View()->assign(array('success' => true, 'data' => $data));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * Internal helper function to save the dynamic attributes of an article price.
     * @param $voucher
     * @param $attributeData
     * @return mixed
     */
    private function saveVoucherAttributes($voucher, $attributeData)
    {
    	if (empty($attributeData)) {
    		return;
    	}
    	if ($voucher->getId() > 0) {
    		$builder = $this->getManager()->createQueryBuilder();
    		$builder->select(array('attribute'))
    				->from('Shopware\Models\Attribute\Voucher', 'attribute')
    				->where('attribute.voucherId = ?1')
    				->setParameter(1, $voucher->getId());

    		$result = $builder->getQuery()->getOneOrNullResult();
    		if (empty($result)) {
    			$attributes = new \Shopware\Models\Attribute\Voucher();
    		} else {
    			$attributes = $result;
    		}
    	} else {
    		$attributes = new \Shopware\Models\Attribute\Voucher();
    	}
    	$attributes->fromArray($attributeData);
    	$attributes->setVoucher($voucher);
    	$this->getManager()->persist($attributes);
    }


    ///////////////////////////////////////////////////////////////////////////
    //Data Validation Methods//////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////
    /**
     * checks if the entered vouchercode is already defined or not
     */
    public function validateVoucherCodeAction()
    {
        $voucherCode = $this->Request()->value;
        $voucherID = intval($this->Request()->param);
        $voucherData = $this->getVoucherRepository()
                            ->getValidateVoucherCodeQuery($voucherCode, $voucherID)
                            ->getArrayResult();

        if (empty($voucherData)) {
            echo true;
        } else {
            echo false;
        }
    }

    /**
     * checks if the entered ordercode is already defined or not
     */
    public function validateOrderCodeAction()
    {
        $orderCode = $this->Request()->value;
        $voucherID = intval($this->Request()->param);
        $voucherData = $this->getVoucherRepository()
                            ->getValidateOrderCodeQuery($orderCode, $voucherID)
                            ->getArrayResult();

        if (empty($voucherData)) {
            echo true;
        } else {
            echo false;
        }
    }

}
