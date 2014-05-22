<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class Download extends Gateway
{
    /**
     * @var Hydrator\Download
     */
    private $downloadHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Download $downloadHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Download $downloadHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->downloadHydrator = $downloadHydrator;
    }

    /**
     * Returns a list of product downloads for each passed ListProduct struct.
     * This function is used in the core by the Service\Product class.
     *
     * @param Struct\ListProduct[] $products
     * @return Struct\Product\Download[] returns an array which indexed with the product number, each array contains an
     * additionally Download struct array.
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getDownloadFields())
            ->addSelect($this->getTableFields('s_articles_downloads_attributes', 'downloadAttribute'));

        $query->from('s_articles_downloads', 'downloads')
            ->leftJoin(
                'downloads',
                's_articles_downloads_attributes',
                'downloadAttribute',
                'downloadAttribute.downloadID = downloads.id'
            );

        $query->where('downloads.articleID IN (:ids)')
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

    private function getDownloadFields()
    {
        return array(
            'downloads.id',
            'downloads.articleID',
            'downloads.description',
            'downloads.filename',
            'downloads.size'
        );
    }
}
