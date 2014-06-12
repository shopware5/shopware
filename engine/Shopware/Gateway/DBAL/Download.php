<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class Download
{
    /**
     * @var Hydrator\Download
     */
    private $downloadHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Download $downloadHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Download $downloadHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->downloadHydrator = $downloadHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * Returns a list of product downloads for each passed ListProduct struct.
     * This function is used in the core by the Service\Product class.
     *
     * @param Struct\ListProduct[] $products
     * @return Struct\Product\Download[] returns an array which indexed with the product number, each array contains an
     * additionally Download struct array.
     */
    public function getList(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getDownloadFields());

        $query->from('s_articles_downloads', 'download')
            ->leftJoin(
                'download',
                's_articles_downloads_attributes',
                'downloadAttribute',
                'downloadAttribute.downloadID = download.id'
            );

        $query->where('download.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $downloads = array();
        foreach ($data as $row) {
            $key = $row['articleID'];

            $download = $this->downloadHydrator->hydrate($row);
            $downloads[$key][] = $download;
        }

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $downloads[$product->getId()];
        }

        return $result;
    }
}
