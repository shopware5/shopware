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
 * @package    Shopware_Components
 * @subpackage Import
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 * Shopware Import Component
 *
 */
class Shopware_Components_Import
{

    /**
     * Entity Manager
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager = null;

    /**
     * Repository for the article model.
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * Repository for the articleDetail model.
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository = null;

    /**
     * Repository for the category model
     * @var \Shopware\Models\Category\Repository
     */
    protected $categoryRepository = null;

    /**
     * Repository for the customer model
     * @var \Shopware\Models\Customer\Repository
     */
    protected $customerRepository = null;

    /**
     * @var string path to termporary uploaded file for import
     */
    protected $uploadedFilePath;

    /**
     * Internal helper function to get access to the entity manager.
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Helper function to get access to the article repository.
     * @return Shopware\Models\Article\Repository
     */
    protected function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = $this->getManager()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Helper function to get access to the articleDetail repository.
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getArticleDetailRepository()
    {
        if ($this->articleDetailRepository === null) {
            $this->articleDetailRepository = $this->getManager()->getRepository('Shopware\Models\Article\Detail');
        }
        return $this->articleDetailRepository;
    }

    /**
     * Helper function to get access to the category repository.
     * @return \Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = $this->getManager()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->categoryRepository;
    }

    /**
     * Helper function to get access to the customer repository.
     * @return Shopware\Models\Article\Repository
     */
    protected function getCustomerRepository()
    {
        if ($this->customerRepository === null) {
            $this->customerRepository = $this->getManager()->getRepository('Shopware\Models\Customer\Customer');
        }
        return $this->customerRepository;
    }
    /**
     * Imports customers from CSV file
     * @param string $filePath
     * @throws \Exception
     */
    public function importArticlesCsv($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $articleIds = array();
        $errors = array();
        $counter = 0;

        /** @var \Shopware\Components\Api\Resource\Article $articleResource */
        $articleResource = \Shopware\Components\Api\Manager::getResource('article');

        $articleMetaData       = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Article');
        $articleDetailMetaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Detail');

        $articleMapping = array();
        foreach ($articleMetaData->fieldMappings as $fieldMapping) {
            $articleMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $articleDetailMapping = array();
        foreach ($articleDetailMetaData->fieldMappings as $fieldMapping) {
            $articleDetailMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $this->getManager()->getConnection()->beginTransaction(); // suspend auto-commit

        $postInsertData = array();

        try {
            foreach ($results as $articleData) {
                $counter++;

                // Prevent invalid records from being imported and throw a exception
                if(empty($articleData['name'])) {
                    throw new \Exception("Article name may not be empty");
                }
                if(empty($articleData['ordernumber'])) {
                    throw new \Exception("Article ordernumber may not be empty");
                }
                if(!empty($articleData['ordernumber'])) {
                    if(preg_match('/[^a-zA-Z0-9-_. ]/', $articleData['ordernumber']) !== 0) {
                        throw new \Exception("Invalid ordernumber: {$articleData['ordernumber']}");
                    }
                }

                $result = $this->saveArticle($articleData, $articleResource, $articleMapping, $articleDetailMapping);
                if(!$result instanceof \Shopware\Models\Article\Article) {
                    $errors[] = $result;
                    continue;
                }
                if ($result) {
                    $articleIds[] = $result->getId();

                    $updateData = array();

                    if (!empty($articleData['similar'])) {
                        $similars = explode('|', $articleData['similar']);
                        foreach ($similars as $similarId) {
                            $updateData['similar'][] = array('number' => $similarId);
                        }
                    }

                    if (!empty($articleData['crosselling'])) {
                        $crossSellings = explode('|', $articleData['crosselling']);
                        foreach ($crossSellings as $crosssellingId) {
                            $updateData['related'][] = array('number' => $crosssellingId);
                        }
                    }

                    // During the import each article creates a set with it own configruatorOptions as Options
                    // when persisting only these options will be set to be set-option relations
                    // in order to fix this, we set all options active which can be assigned to a given article
                    /** @var \Shopware\Models\Article\Configurator\Set $configuratorSet */
                    $configuratorSet = $result->getConfiguratorSet();
                    if($configuratorSet !== null) {
                        $configuratorSet->getOptions()->clear();
                        $articleRepository = $this->getArticleRepository();
                        $ids = $articleRepository->getArticleConfiguratorSetOptionIds($result->getId());
                        if(!empty($ids)) {
                            $configuratorOptionRepository = Shopware()->Models()->getRepository('\Shopware\Models\Article\Configurator\Option');
                            $optionModels = $configuratorOptionRepository->findBy(array("id" => $ids));
                            $configuratorSet->setOptions($optionModels);
                        }


                    }

                    if (!empty($updateData)) {
                        $updateData['id'] = $result->getId();
                        $postInsertData[] = $updateData;
                    }
                }

                $this->getManager()->flush();
                $this->getManager()->clear();
            }

            foreach ($postInsertData as $updateData) {
                $result = $articleResource->update($updateData['id'], $updateData);
            }

            $this->insertPrices($results);

            $this->getManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getManager()->getConnection()->rollback();

            if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                $messages = array();
                /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                foreach ($e->getViolations() as $violation) {
                    $messages[] = sprintf(
                        '%s: %s', $violation->getPropertyPath(), $violation->getMessage()
                    );
                }

                $errormessage = implode("\n", $messages);
            } else {
                $errormessage = $e->getMessage();
            }

            $errors[] = "Error in line {$counter}: $errormessage\n";

            $errors = $this->toUtf8($errors);
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => "Error: ".$message,
            ));
            return;
        }

        if(!empty($errors)) {
            $errors = $this->toUtf8($errors);
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => "Error: ".$message,
            ));
            return;
        }

        echo json_encode(array(
             'success' => true,
             'message' => sprintf("Successfully saved: %s", count($articleIds))
        ));
    }


    /**
     * Transforms legacy configurator data
     *
     * @param $configuratorData
     * @return array
     */
    protected function prepareLegacyConfiguratorImport($configuratorData)
    {
        $variants = array();
        $values   = explode("\n", $configuratorData);
        foreach ($values as $value) {
            $value = explode('|', trim($value));
            if (count($value) < 4) {
                continue;
            }

            $value[1] = explode(',', $value[1]);
            $value[3] = trim($value[3], ', ');
            $variant  = array(
                'additionalText' => $value[3],
                'number'         => $value[0],
                'inStock'        => $value[1][0]
            );
            $value[3] = explode(',', $value[3]);

            if (isset($value[1][1])) {
                $variant['active'] = $value[1][1];
            }

            if (isset($value[1][2])) {
                $variant['standard'] = $value[1][2];
            }

            $variant['configuratorOptions'] = array();

            for ($i = 0, $c = count($value[3]); $i < $c; $i++) {
                $value[3][$i] = explode(':', $value[3][$i]);

                $variant['configuratorOptions'][] = array(
                    'group'  => trim($value[3][$i][0]),
                    'option' => trim($value[3][$i][1])
                );
            }

            $variant['prices'] = array(array(
                'price' => $value[2],
            ));

            $variants[] = $variant;
        }

        $groups = array();
        foreach ($variants as $variant) {
            foreach ($variant['configuratorOptions'] as $configuratorOption) {
                if (!isset($groups[$configuratorOption['group']])) {
                    $groups[$configuratorOption['group']] = array();
                }

                if (!in_array($configuratorOption['option'], $groups[$configuratorOption['group']])) {
                    $groups[$configuratorOption['group']][] = $configuratorOption['option'];
                }
            }
        }

        $configuratorGroups = array();
        foreach ($groups as $groupName => $options) {
            $configuratorGroup = array();
            $configuratorGroup['name'] = $groupName;
            $configuratorGroup['options'] = array();
            foreach ($options as $option) {
                $configuratorGroup['options'][] = array(
                    'name' => $option
                );
            }
            $configuratorGroups[] = $configuratorGroup;
        }

        $configurator['variants'] = $variants;
        $configurator['configuratorSet'] = array('groups' => $configuratorGroups);

        return $configurator;
    }

    /**
     * Takes a new style configurator and converts it into a proper array
     * @param $configuratorData
     * @return array
     */
    protected  function prepareNewConfiguratorImport($configuratorData) {
        $configuratorGroups = array();
        $configuratorOptions = array();

        // split string into parts and recieve group and options this way
        $pairs = explode("|", $configuratorData);
        foreach($pairs as $pair) {
            list($group, $option) = explode(":", $pair);

            $currentGroup = array("name" => $group, "options" => array(array("name" => $option)));
            $configuratorGroups[] = $currentGroup;

            $configuratorOptions[]= array("option" => $option, "group" => $group);

        }

        return array(
            array('groups' => $configuratorGroups),      // ConfiguratorSet
            $configuratorOptions                         // ConfiguratorOptions
        );

    }

    /**
     * Prepare an articles' translation. Needed to be called before the article mapping is done
     * @param $data
     * @return array
     */
    protected function prepareTranslation($data) {

        $translationByLanguage = array();

        $whitelist = array(
            'name'              => 'name',
            'additionaltext'    => 'additionaltext',
            'description_long'  => 'descriptionLong',
            'description'       => 'description',
            'packUnit'          => 'packunit',
            'keywords'          => 'keywords'
        );

        // first get a list of all available translation by language ID
        foreach($data as $key => $value) {
            foreach($whitelist as $translationKey => $translationMapping) {
                if(strpos($key, $translationKey.'_') !== false) {
                    $parts = explode('_', $key);
                    $language = array_pop($parts);
                    if(!is_numeric($language)) {
                        continue;
                    }

                    if(!isset($translationByLanguage[$language])) {
                        $translationByLanguage[$language] = array();
                        $translationByLanguage[$language]['shopId'] = $language;
                    }
                    $translationByLanguage[$language][$translationMapping] = $value;

                    // remove translation and whitelist entry in order not to double-set translations
                    unset($data[$key]);
                    unset($whitelist[$translationKey]);
                }
            }
        }

        $data['translations'] = $translationByLanguage;
        return $data;

    }

    /**
     * @param array $input
     * @param array $mapping
     * @param array $whitelist
     * @return array
     */
    protected function mapFields($input, $mapping = array(), $whitelist = array())
    {
        $output = array();

        $whitelist = $mapping + $whitelist;

        foreach ($input as $key => $value) {
            if (isset($mapping[$key])) {
                $output[$mapping[$key]] = $value;
            } elseif (in_array($key, $whitelist)) {
                $output[$key] = $value;
            } else {
                // fields we don't know we don't want
            }
        }

        return $output;
    }

    /**
     * @param string $categorypaths
     * @return array
     * @throws \Exception
     */
    public function createCategoriesByCategoryPaths($categorypaths)
    {
        $categoryIds = array();
        $categorypaths = explode("\n", $categorypaths);

        foreach ($categorypaths as $categorypath) {
            $categorypath = trim($categorypath);
            if (empty($categorypath)) {
                continue;
            }

            $categories = explode('|', $categorypath);
            $categoryId = 1;
            foreach ($categories as $categoryName) {
                $categoryName = trim($categoryName);
                if (empty($categoryName)) {
                    break;
                }

                $categoryModel = $this->getCategoryRepository()->findOneBy(array('name' => $categoryName, 'parentId' => $categoryId));
                if (!$categoryModel) {
                    $parent = $this->getCategoryRepository()->find($categoryId);
                    if (!$parent) {
                        throw new \Exception(sprintf('Could not find %s '));
                    }
                    $categoryModel = new \Shopware\Models\Category\Category();
                    $categoryModel->setParent($parent);
                    $categoryModel->setName($categoryName);
                    $this->getManager()->persist($categoryModel);
                    $this->getManager()->flush();
                    $this->getManager()->clear();
                }

                $categoryId = $categoryModel->getId();

                if (empty($categoryId)) {
                    continue;
                }

                if(!in_array($categoryId, $categoryIds)) {
                    $categoryIds[] = $categoryId;
                }

            }
        }

        return $categoryIds;
    }

    /**
     * @param array $input
     * @param string $prefix
     * @return array
     */
    protected function prefixToArray(&$input, $prefix)
    {
        $output = array();
        foreach ($input as $key => $value) {
            if (stripos($key, $prefix) === 0) {
                $oldKey = $key;
                $key = substr($key, strlen($prefix));
                $output[$key] = $value;
                unset($input[$oldKey]);
            }
        }

        return $output;
    }

    /**
     * @param array $articleData
     * @param $articleResource
     * @param array $articleMapping
     * @param array $articleDetailMapping
     * @return \Shopware\Models\Article\Article
     * @throws \Exception
     */
    protected function saveArticle($articleData, $articleResource, $articleMapping, $articleDetailMapping)
    {
        $importImages = false;

        $articleData = $this->toUtf8($articleData);

        $articleRepostiory       = $this->getArticleRepository();
        $articleDetailRepostiory = $this->getArticleDetailRepository();

        if (empty($articleData['ordernumber'])) {
            return false;
        }

        unset($articleData['articleID'], $articleData['articledetailsID']);

        $isOldConfigurator = false;
        if (isset($articleData['configurator']) && !empty($articleData['configurator']) && empty($articleData['configuratorsetID'])) {
            $isOldConfigurator = true;
            $configurator = $this->prepareLegacyConfiguratorImport($articleData['configurator']);
        }

        $isNewConfigurator = false;
        if(isset($articleData['configuratorOptions']) && !empty($articleData['configuratorOptions'])) {
            if(!isset($articleData['configuratorsetID']) || empty($articleData['configuratorsetID'])) {
                return sprintf("Article with ordernumber %s is a variant but has no configuratorSetID. It is probably broken and was skipped",$articleData['ordernumber'] );
            }
            list($configuratorSet, $configuratorOptions) = $this->prepareNewConfiguratorImport($articleData['configuratorOptions']);
            $isNewConfigurator = true;
        }


        $articleData = $this->prepareTranslation($articleData);

        $isOldVariant = false;
        if (!empty($articleData['additionaltext']) && empty($articleData['mainnumber']) && empty($articleData['configuratorsetID'])) {
            $isOldVariant = true;
            $groupName = $articleData['ordernumber'] . '-Group';

            $configuratorSet = array(
                'groups' => array(array(
                    'name'    => $groupName,
                    'options' => array(
                        array('name' => $articleData['additionaltext'])
                    ))
                )
            );

            $configuratorOptions = array(array(
                'group'  => $groupName,
                'option' => $articleData['additionaltext']
            ));
        } elseif (!empty($articleData['mainnumber']) && empty($articleData['configurator']) && empty($articleData['configuratorsetID'])) {
            $isOldVariant = true;
            $groupName = $articleData['mainnumber'] . '-Group';
            $configuratorOptions = array(array(
                'group'  => $groupName,
                'option' => $articleData['additionaltext']
            ));
        }

        // unset legacy attributes
        unset($articleData['attributegroupID']);
        unset($articleData['attributevalues']);

        $updateData = $this->mapFields($articleData, $articleMapping, array('taxId', 'tax', 'supplierId', 'supplier', 'whitelist', 'translations', 'baseprice', 'pseudoprice'));
        $detailData = $this->mapFields($articleData, $articleDetailMapping);

        if (!empty($articleData['categorypaths'])) {
            $categoryIds = $this->createCategoriesByCategoryPaths($articleData['categorypaths']);

            unset($articleData['categories']);
            unset($articleData['categorypaths']);
            foreach ($categoryIds as $categoryId) {
                $updateData['categories'][] = array('id' => $categoryId);
            }
        }

        if (isset($articleData['tax']) && empty($articleData['tax'])) {
            $updateData['tax'] = 19;
        }

        $prices = array(
            'price' => 'price',
            'baseprice' => 'basePrice',
            'pseudoprice' => 'pseudoPrice'
        );
        $detailData['prices'] = array();
        foreach($prices as $priceKey => $mappedName) {
            if(!empty($articleData[$priceKey])) {
                $detailData['prices'][0][$mappedName] = $articleData[$priceKey];
            }
        }
        if(empty($detailData['prices'])) {
            unset($detailData['prices']);
        }


        if (!empty($articleData['propertyValues'])) {
            $propertyValues = explode('|', $articleData['propertyValues']);
            foreach ($propertyValues as $propertyValue) {
                $updateData['propertyValues'][] = array('id' => $propertyValue);
            }
        }

        if ($importImages && !empty($articleData['images'])) {
            $images = explode('|', $articleData['images']);
            foreach ($images as $imageLink) {
                $updateData['images'][] = array('link' => $imageLink);
            }
        }

        if (!empty($articleData['categories'])) {
            $categories = explode('|', $articleData['categories']);
            foreach ($categories as $categoryId) {
                $updateData['categories'][] = array('id' => $categoryId);
            }
        }

        // unset similar and crosselling, will be inserted post insert
        unset($articleData['similar']);
        unset($articleData['crosselling']);

        $attribute = $this->prefixToArray($articleData, 'attr_');
        if (!empty($attribute)) {
            $detailData['attribute'] = $attribute;
        }

        if ($isOldVariant) {
            if (isset($configuratorSet)) {
                /** @var \Shopware\Models\Article\Detail $articleDetailModel */
                $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['ordernumber']));
                if ($articleDetailModel) {
                    /** @var \Shopware\Models\Article\Article $articleModel */
                    $articleModel = $articleDetailModel->getArticle();
                    if (!$articleModel) {
                        throw new \Exception('Article not Found');
                    }
                }

                $updateData['configuratorSet'] = $configuratorSet;
                $updateData['variants'][0] = $detailData;
                $updateData['variants'][0]['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][0]['standard'] = true;
                $updateData['mainDetail'] = $detailData;
//                $updateData['mainDetail']['number'] .= '_main';

                if ($articleModel) {
                    throw new \Exception(sprintf('Legacy variant article with ordernumber %s can only be imported once.', $articleData['ordernumber']));
                } else {
                    $result = $articleResource->create($updateData);
                }
            } else {
                /** @var \Shopware\Models\Article\Detail $articleDetailModel */
                $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['mainnumber']));
                if ($articleDetailModel) {
                    /** @var \Shopware\Models\Article\Article $articleModel */
                    $articleModel = $articleDetailModel->getArticle();
                    if (!$articleModel) {
                        throw new \Exception('Article not Found');
                    }
                }
                $updateData = array();
                $detailData['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][] = $detailData;

                if ($articleModel) {
                    $result = $articleResource->update($articleModel->getId(), $updateData);
                } else {
                    throw new \Exception('Parent variant not found');
                }
            }

            return $result;
        }

        // For old 3.x configurators
        if ($isOldConfigurator) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['ordernumber']));
            if ($articleDetailModel) {
                /** @var \Shopware\Models\Article\Article $articleModel */
                $articleModel = $articleDetailModel->getArticle();
                if (!$articleModel) {
                    throw new \Exception('Article not Found');
                }
            }

            $updateData['configuratorSet'] = $configurator['configuratorSet'];
            $updateData['variants'] = $configurator['variants'];
            $updateData['mainDetail'] = $detailData;

            if ($articleModel) {
                throw new \Exception(sprintf('Legacy configurator article with ordernumber %s can only be imported once.', $articleData['ordernumber']));
            } else {
                $result = $articleResource->create($updateData);
            }

            return $result;
        }

        // For configurators as used in SW 4
        if($isNewConfigurator) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['mainnumber']));
            if ($articleDetailModel) {
                /** @var \Shopware\Models\Article\Article $articleModel */
                $articleModel = $articleDetailModel->getArticle();
                if (!$articleModel) {
                    throw new \Exception('Article not Found');
                }
            }

            // update?
            if(isset($articleModel) && $articleModel !== null) {
                $updateData = array('variants'=>array());
                $detailData['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][] = $detailData;
                $result = $articleResource->update($articleModel->getId(), $updateData);
            }else{
                $updateData['configuratorSet'] = $configuratorSet;
                $updateData['variants'][0] = $detailData;
                $updateData['variants'][0]['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][0]['standard'] = true;
                $updateData['mainDetail'] = $detailData;
                $result = $articleResource->create($updateData);
            }


            return $result;
        }

        /** @var \Shopware\Models\Article\Detail $articleDetailModel */
        $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['ordernumber']));
        if ($articleDetailModel) {
            /** @var \Shopware\Models\Article\Article $articleModel */
            $articleModel = $articleDetailModel->getArticle();
            if (!$articleModel) {
                throw new \Exception('Article not Found');
            }
        }

        if ($articleModel) {
            if ($articleDetailModel->getKind() == 1) {
                $updateData['mainDetail'] = $detailData;
            } elseif ($articleDetailModel->getKind() == 2) {
                $detailData['id'] = $articleDetailModel->getId();
                $updateData = array();
                $updateData['variants'][] = $detailData;
            }
            $result = $articleResource->update($articleModel->getId(), $updateData);
        } else {
            $updateData['mainDetail'] = $detailData;
            $result = $articleResource->create($updateData);
        }
        return $result;
    }

    /**
     * @param array $input
     * @return array
     */
    protected function toUtf8(array $input)
    {
        // detect whether the input is UTF-8 or ISO-8859-1
        array_walk_recursive($input, function (&$value) {
            // will fail, if special chars are encoded to latin-1
            // $isUtf8 = (utf8_encode(utf8_decode($value)) == $value);

            // might have issues with encodings other than utf-8 and latin-1
            $isUtf8 = (mb_detect_encoding($value, 'UTF-8', true) !== false);
            if (!$isUtf8) {
                $value = utf8_encode($value);
            }
            return $value;
        });

        return $input;
    }


}
