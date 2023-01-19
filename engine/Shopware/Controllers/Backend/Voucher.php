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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Models\Tax\Tax;
use Shopware\Models\Voucher\Code;
use Shopware\Models\Voucher\Repository;
use Shopware\Models\Voucher\Voucher;

class Shopware_Controllers_Backend_Voucher extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Entity Manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var Repository
     */
    protected $voucherRepository;

    /**
     * Disable template engine for all actions
     *
     * @codeCoverageIgnore
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (\in_array($this->Request()->getActionName(), [
            'validateOrderCode', 'validateVoucherCode', 'validateDescription', ])) {
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
        $voucherRequestData = empty($multipleVouchers) ? [['id' => $this->Request()->getParam('id')]] : $multipleVouchers;
        foreach ($voucherRequestData as $voucher) {
            // First delete the voucher codes because this could be to huge for doctrine
            $this->deleteAllVoucherCodesById((int) $voucher['id']);

            /** @var Voucher $model */
            $model = $this->getVoucherRepository()->find($voucher['id']);
            $this->getManager()->remove($model);
        }
        $this->getManager()->flush();
        $this->View()->assign(['success' => true, 'data' => $voucherRequestData]);
    }

    /**
     * Returns a JSON string containing all Suppliers
     *
     * @return void
     */
    public function getVoucherAction()
    {
        $offset = (int) $this->Request()->getParam('start');
        $limit = (int) $this->Request()->getParam('limit');
        $filter = $this->Request()->getParam('filter');
        $filter = $filter[0]['value'];
        $sqlBindings = [];
        $searchSQL = '';

        // Search for values
        if (!empty($filter)) {
            $searchSQL = 'AND v.description LIKE :filter
                            OR v.vouchercode LIKE :filter
                            OR v.value LIKE :filter
                            OR (SELECT 1 FROM s_emarketing_voucher_codes WHERE voucherID = v.id AND code LIKE :filter LIMIT 1)';
            $sqlBindings['filter'] = '%' . $filter . '%';
        }
        // Sorting data
        $sortData = $this->Request()->getParam('sort');
        $sortField = $sortData[0]['property'];
        $dir = $sortData[0]['direction'];
        $sort = '';
        if ((!empty($sortField) && $dir === 'ASC') || $dir === 'DESC') {
            // To prevent sql-injections
            $sortField = Shopware()->Db()->quoteIdentifier($sortField);
            $sort = 'ORDER BY ' . $sortField . ' ' . $dir;
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
                (SELECT count(*) FROM s_order_details as d WHERE articleordernumber =v.ordercode AND d.ordernumber!='0'),
                (SELECT count(*) FROM s_emarketing_voucher_codes WHERE voucherID =v.id AND cashed=1))  AS checkedIn
                FROM s_emarketing_vouchers as v
                WHERE (modus = 1 OR modus = 0)
                {$searchSQL}

                {$sort}
                LIMIT {$offset}, {$limit}
            ";

        $vouchers = Shopware()->Db()->fetchAll($sql, $sqlBindings);
        $sql = 'SELECT FOUND_ROWS()';
        $totalCount = Shopware()->Db()->fetchOne($sql, []);

        $this->View()->assign(['success' => true, 'data' => $vouchers, 'totalCount' => $totalCount]);
    }

    /**
     * Returns a JSON string containing all Voucher Codes
     *
     * @return void
     */
    public function getVoucherCodesAction()
    {
        $voucherId = (int) $this->Request()->getParam('voucherID');

        $orderBy = $this->Request()->getParam('sort');
        $filter = $this->Request()->getParam('filter');
        $filter = $filter[0]['value'];
        $offset = $this->Request()->getParam('start');
        $limit = $this->Request()->getParam('limit');

        $dataQuery = $this->getVoucherRepository()
            ->getVoucherCodeListQuery($voucherId, $filter, $orderBy, $offset, $limit);

        $paginator = $this->getManager()->createPaginator($dataQuery);

        $totalCount = $paginator->count();
        $voucherCodes = iterator_to_array($paginator);

        $this->View()->assign(['success' => true, 'data' => $voucherCodes, 'totalCount' => $totalCount]);
    }

    /**
     * creates all necessary voucher codes
     *
     * @return void
     */
    public function createVoucherCodesAction()
    {
        $voucherId = (int) $this->Request()->getParam('voucherId');
        $numberOfUnits = (int) $this->Request()->getParam('numberOfUnits');
        $codePattern = (string) $this->Request()->getParam('codePattern');

        $codePattern = str_replace('%D', '%d', $codePattern);
        $codePattern = str_replace('%S', '%s', $codePattern);
        $deletePreviousVoucherCodes = $this->Request()->getParam('deletePreviousVoucherCodes');
        $createdVoucherCodes = 0;

        // verify the pattern of the code only the first time of batch processing batch
        if (!empty($codePattern) && $deletePreviousVoucherCodes === 'true') {
            if (!$this->validateCodePattern($codePattern, $numberOfUnits)) {
                $this->View()->assign(['success' => false, 'errorMsg' => 'CodePattern not complex enough']);

                return;
            }
        }
        // first delete available codes
        if ($deletePreviousVoucherCodes === 'true') {
            $this->deleteAllVoucherCodesById($voucherId);

            $this->View()->assign(['success' => true, 'generatedVoucherCodes' => $createdVoucherCodes]);

            return;
        }
        do {
            // generate voucher codes till the numberOfUnits is reached
            $this->generateVoucherCodes($voucherId, $numberOfUnits - $createdVoucherCodes, $codePattern);

            $query = $this->getVoucherRepository()->getVoucherCodeCountQuery($voucherId);
            $result = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
            $createdVoucherCodes = (int) $result['countCode'];
        } while ($createdVoucherCodes < $numberOfUnits);

        $this->View()->assign(['success' => true, 'generatedVoucherCodes' => $createdVoucherCodes]);
    }

    /**
     * Updates a single voucher code by the given parameters.
     *
     * @return void
     */
    public function updateVoucherCodesAction()
    {
        $codeId = (int) $this->Request()->getParam('id');
        $code = $this->get(ModelManager::class)->getRepository(Code::class)->find($codeId);

        if (!$code instanceof Code) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $code->setCashed($this->Request()->getParam('cashed'));
        $code->setCode($this->Request()->getParam('code'));
        $code->setCustomerId($this->Request()->getParam('customerId'));

        $this->get(ModelManager::class)->persist($code);
        $this->get(ModelManager::class)->flush($code);

        $this->View()->assign(['success' => true]);
    }

    /**
     * exports all voucher codes via csv
     *
     * @return void
     */
    public function exportVoucherCodeAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $voucherId = (int) $this->Request()->getParam('voucherId');

        $dataQuery = $this->getVoucherRepository()->getVoucherCodeListQuery($voucherId);
        $resultArray = $dataQuery->getArrayResult();

        $this->Response()->headers->set('content-type', 'text/csv; charset=utf-8');
        $this->Response()->headers->set('content-disposition', 'attachment;filename=voucherCodes.csv');
        // use this to set the BOM to show it in the right way for excel and stuff
        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');
        if (!\is_resource($fp)) {
            throw new RuntimeException('Could not open temporary stream');
        }
        fputcsv($fp, array_keys($resultArray[0]), ';');

        foreach ($resultArray as $value) {
            fputcsv($fp, $value, ';');
        }
        fclose($fp);
    }

    /**
     * Action for the Detail Voucher Form to load all needed data
     *
     * @return void
     */
    public function getVoucherDetailAction()
    {
        $voucherID = (int) $this->Request()->getParam('voucherID');

        $query = $this->getVoucherRepository()->getVoucherDetailQuery($voucherID);
        $model = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        if (!$model instanceof Voucher) {
            throw new ModelNotFoundException(Voucher::class, $voucherID);
        }
        $voucher = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if ($model->getValidFrom() instanceof DateTime) {
            $voucher['validFrom'] = $model->getValidFrom()->format('d.m.Y');
        } else {
            $voucher['validFrom'] = null;
        }
        if ($model->getValidTo() instanceof DateTime) {
            $voucher['validTo'] = $model->getValidTo()->format('d.m.Y');
        } else {
            $voucher['validTo'] = null;
        }

        $this->View()->assign(['success' => true, 'data' => $voucher, 'total' => 1]);
    }

    /**
     * get the Tax configuration
     * Used for the backend tax-combobox
     *
     * @return void
     */
    public function getTaxConfigurationAction()
    {
        $builder = $this->getManager()->getRepository(Tax::class)->createQueryBuilder('t');
        $builder->orderBy('t.id', 'ASC');
        $tax = $builder->getQuery()->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $tax]);
    }

    /**
     * Creates a new voucher with the passed values
     *
     * @return void
     */
    public function saveVoucherAction()
    {
        $params = $this->Request()->getParams();
        $voucherId = empty($params['voucherID']) ? $params['id'] : $params['voucherID'];
        if (!empty($voucherId)) {
            if (!$this->_isAllowed('update', 'voucher')) {
                return;
            }
            // edit voucher
            $voucher = $this->getVoucherRepository()->find($voucherId);
            if (!$voucher instanceof Voucher) {
                throw new ModelNotFoundException(Voucher::class, $voucherId);
            }
        } else {
            if (!$this->_isAllowed('create', 'voucher')) {
                return;
            }
            // new voucher
            $voucher = new Voucher();
        }

        // save empty values
        $params = array_merge([
            'validFrom' => null,
            'validTo' => null,
            'customerGroup' => null,
            'shopId' => null,
            'bindToSupplier' => null,
            'customerStreamIds' => null,
            'restrictedArticles' => null,
        ], $params);

        $voucher->fromArray($params);
        $this->getManager()->persist($voucher);
        $this->getManager()->flush();
        $data = $this->getVoucherRepository()
            ->getVoucherDetailQuery($voucher->getId())
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * checks if the entered vouchercode is already defined or not
     *
     * @return void
     */
    public function validateVoucherCodeAction()
    {
        $voucherCode = $this->Request()->getParam('value');
        $voucherID = (int) $this->Request()->getParam('param');
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
     *
     * @return void
     */
    public function validateOrderCodeAction()
    {
        $orderCode = $this->Request()->getParam('value');
        $voucherID = (int) $this->Request()->getParam('param');
        $voucherData = $this->getVoucherRepository()
            ->getValidateOrderCodeQuery($orderCode, $voucherID)
            ->getArrayResult();

        if (empty($voucherData)) {
            echo true;
        } else {
            echo false;
        }
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'exportVoucherCode',
        ];
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * permission to delete voucher(s)
         */
        $this->addAclPermission('deleteVoucherAction', 'delete', 'Insufficient Permissions');

        /*
         * permission to list all vouchers
         */
        $this->addAclPermission('getVoucherAction', 'read', 'Insufficient Permissions');

        /*
         * permission to list all individual vouchers
         */
        $this->addAclPermission('getVoucherCodesAction', 'read', 'Insufficient Permissions');

        /*
         * permission to create individual voucher codes
         */
        $this->addAclPermission('createVoucherCodesAction', 'generate', 'Insufficient Permissions');

        /*
         * permission to update individual voucher codes
         */
        $this->addAclPermission('updateVoucherCodesAction', 'generate', 'Insufficient Permissions');

        /*
         * permission to export individual voucher codes
         */
        $this->addAclPermission('exportVoucherCodeAction', 'export', 'Insufficient Permissions');
    }

    /**
     * helper Method to generate all needed voucher codes
     *
     * @param int    $voucherId
     * @param int    $numberOfUnits
     * @param string $codePattern
     *
     * @return void
     */
    protected function generateVoucherCodes($voucherId, $numberOfUnits, $codePattern)
    {
        $values = [];
        // wrote in standard sql cause in this case its way faster than doctrine models
        $sql = 'INSERT IGNORE INTO s_emarketing_voucher_codes (voucherID, code) VALUES';
        for ($i = 1; $i <= $numberOfUnits; ++$i) {
            $code = $this->generateCode($codePattern);
            $values[] = Shopware()->Db()->quoteInto('(?)', [$voucherId, $code]);
            // send the query every each 10000 times
            if ($i % 10000 == 0 || $numberOfUnits == $i) {
                Shopware()->Db()->query($sql . implode(',', $values));
                $values = [];
            }
        }
    }

    /**
     * Helper function to get access to the voucher repository.
     *
     * @return Repository
     */
    private function getVoucherRepository()
    {
        if ($this->voucherRepository === null) {
            $this->voucherRepository = $this->get('models')->getRepository(Voucher::class);
        }

        return $this->voucherRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = $this->get('models');
        }

        return $this->manager;
    }

    /**
     * generates the voucherCode based on the code pattern
     *
     * @param string $codePattern
     *
     * @return mixed|string
     */
    private function generateCode($codePattern)
    {
        if (empty($codePattern)) {
            return strtoupper(substr(uniqid('', true), 6, 8));
        }
        $codePattern = $this->replaceAllMatchingPatterns($codePattern, range('A', 'Z'), '%s');
        $codePattern = $this->replaceAllMatchingPatterns($codePattern, range('0', '9'), '%d');

        return $codePattern;
    }

    /**
     * validates the code pattern
     */
    private function validateCodePattern(string $codePattern, int $numberOfUnits): bool
    {
        $numberOfStringValues = substr_count($codePattern, '%s');
        $numberOfDigitValues = substr_count($codePattern, '%d');

        $numberOfDigitValues = 10 ** $numberOfDigitValues;
        $numberOfDigitValues = $numberOfDigitValues === 1 ? 0 : $numberOfDigitValues;
        $numberOfStringValues = 26 ** $numberOfStringValues;
        $numberOfStringValues = $numberOfStringValues === 1 ? 0 : $numberOfStringValues;
        if (empty($numberOfDigitValues)) {
            $numberOfPossibleCodes = $numberOfStringValues;
        } elseif (empty($numberOfStringValues)) {
            $numberOfPossibleCodes = $numberOfDigitValues;
        } else {
            $numberOfPossibleCodes = $numberOfDigitValues * $numberOfStringValues;
        }

        return ($numberOfPossibleCodes * 0.0001) > $numberOfUnits;
    }

    /**
     * replaced all matching patterns
     *
     * @param array<string|int> $range
     */
    private function replaceAllMatchingPatterns(string $generatedCode, array $range, string $pattern): string
    {
        $allPatternsReplaced = false;
        while (!$allPatternsReplaced) {
            $generatedCode = (string) preg_replace('/\\' . $pattern . '/', (string) $range[Random::getInteger(1, \count($range) - 1)], $generatedCode, 1);
            $allPatternsReplaced = substr_count($generatedCode, $pattern) == 0;
        }

        return $generatedCode;
    }

    /**
     * helper method to fast delete all voucher codes
     */
    private function deleteAllVoucherCodesById(int $voucherId): void
    {
        $allVouchersDeleted = false;
        while (!$allVouchersDeleted) {
            $deleteQuery = $this->getVoucherRepository()->getVoucherCodeDeleteByVoucherIdQuery($voucherId);
            $deleteQuery->execute();
            $sql = 'SELECT count(id) FROM  s_emarketing_voucher_codes WHERE voucherId = ?';
            $vouchersToDelete = Shopware()->Db()->fetchOne($sql, [$voucherId]);
            $allVouchersDeleted = empty($vouchersToDelete);
        }
    }
}
