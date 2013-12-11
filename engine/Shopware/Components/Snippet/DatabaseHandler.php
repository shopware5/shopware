<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Components\Snippet;

use Shopware;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @category  Shopware
 * @package   Shopware\Components\Snippet
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class DatabaseHandler
{
    /**
     * @var $kernelRoot
     */
    protected $kernelRoot;

    /**
     * @var The entity manager
     */
    protected $em;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var OutputInterface optional output used in CLI
     */
    protected $output;

    /**
     * @var Enlight_Config_Adapter_File Snippet input adapter
     */
    protected $inputAdapter;

    /**
     * @var Enlight_Config_Adapter_DbTable Snippet output adapter
     */
    protected $outputAdapter;

    /**
     * @param ModelManager $em
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param $kernelRoot
     */
    public function __construct(ModelManager $em, Enlight_Components_Db_Adapter_Pdo_Mysql $db, $kernelRoot)
    {
        $this->em = $em;
        $this->kernelRoot = $kernelRoot;
        $this->db = $db;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Loads all snippets from all files in $snippetsDir
     * (including subfolders) and writes them to the database.
     *
     * @param null $snippetsDir
     */
    public function loadToDatabase($snippetsDir = null)
    {
        $snippetsDir = $snippetsDir ? : $this->kernelRoot . 'snippets/';
        if (!file_exists($snippetsDir)) {
            return;
        }

        $localeRepository = $this->em->getRepository('Shopware\Models\Shop\Locale');

        $this->inputAdapter = new \Enlight_Config_Adapter_File(array(
            'configDir' => $snippetsDir,
        ));
        $this->outputAdapter = new \Enlight_Config_Adapter_DbTable(array(
            'db' => $this->db,
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => array('shopID', 'localeID')
        ));

        $finder = new Finder();
        $finder->files()->in($snippetsDir);
        $defaultLocale = $localeRepository->findOneBy(array('locale' => 'en_GB'));

        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath) - 4) {
                $namespace = substr($filePath, 0, -4);
            } else {
                continue;
            }

            if ($this->output) {
                $this->output->writeln('<info>Importing ' . $namespace . ' namespace</info>');
            }

            $namespaceData = new \Enlight_Components_Snippet_Namespace(array(
                'adapter' => $this->inputAdapter,
                'name' => $namespace,
            ));

            foreach ($namespaceData->read()->toArray() as $index => $values) {
                if ($index == 'default') {
                    $locale = $defaultLocale;
                } else {
                    $locale = $localeRepository->findOneBy(array('locale' => $index));
                }

                $namespaceData->setSection(array(1, $locale->getId()))->read();
                $namespaceData->setData($values);
                $this->outputAdapter->write($namespaceData, array_keys($values), true, false, true);
                if ($this->output) {
                    $this->output->writeln('<info>Imported ' . count($values) . ' snippets into ' . $locale->getLocale() . '</info>');
                }
            }

            if ($this->output) {
                $this->output->writeln('<info></info>');
            }
        }
    }

    /**
     * Dumps all snippets from database into the provided $snippetsDir
     *
     * @param string|null $snippetsDir
     * @param string $localeName
     */
    public function dumpFromDatabase($snippetsDir, $localeName)
    {
        $snippetsDir = $this->kernelRoot . $snippetsDir . '/';
        if (!file_exists($snippetsDir)) {
            return;
        }

        $snippetRepository = $this->em->getRepository('Shopware\Models\Snippet\Snippet');

        $locale = $this->em->getRepository('Shopware\Models\Shop\Locale')->findOneByLocale($localeName);

        $this->outputAdapter = new \Enlight_Config_Adapter_File(array(
            'configDir' => $snippetsDir . '/',
        ));
        $this->inputAdapter = new \Enlight_Config_Adapter_DbTable(array(
            'db' => $this->db,
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => array('localeID', 'shopID')
        ));

        $namespaces = array_map(
            function ($result) {
                return $result['namespace'];
            },
            $snippetRepository->getDistinctNamespacesQuery($locale->getId())->getArrayResult()
        );

        if (count($namespaces) == 0) {
            if ($this->output) {
                $this->output->writeln('<error>No snippets found for the given locale(s)</error>');
            }
            return;
        }

        $data = array();

        foreach ($namespaces as $namespace) {
            if (!array_key_exists($namespace, $data)) {
                $data[$namespace] = true;
                $content = new \Enlight_Components_Snippet_Namespace(array(
                    'adapter' => $this->inputAdapter,
                    'name' => $namespace,
                    'section' => array(
                        $locale->getId()
                    )
                ));

                $content->setSection($locale->getLocale());
                $this->outputAdapter->write($content, true);
            }
        }
    }

    /**
     * Loads all snippets from all files in $snippetsDir
     * (including subfolders) and removes them from the database.
     *
     * @param null $snippetsDir
     * @param boolean $removeDirty
     */
    public function removeFromDatabase($snippetsDir = null, $removeDirty = false)
    {
        $snippetsDir = $snippetsDir ? : $this->kernelRoot . 'snippets/';
        if (!file_exists($snippetsDir)) {
            return;
        }

        $localeRepository = $this->em->getRepository('Shopware\Models\Shop\Locale');

        $this->inputAdapter = new \Enlight_Config_Adapter_File(array(
            'configDir' => $snippetsDir,
        ));
        $this->outputAdapter = new \Enlight_Config_Adapter_DbTable(array(
            'db' => $this->db,
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => array('shopID', 'localeID')
        ));

        $finder = new Finder();
        $finder->files()->in($snippetsDir);
        $defaultLocale = $localeRepository->findOneBy(array('locale' => 'en_GB'));

        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath) - 4) {
                $namespace = substr($filePath, 0, -4);
            } else {
                continue;
            }

            if ($this->output) {
                $this->output->writeln('<info>Processing ' . $namespace . ' namespace</info>');
            }

            $namespaceData = new \Enlight_Components_Snippet_Namespace(array(
                'adapter' => $this->inputAdapter,
                'name' => $namespace,
            ));

            foreach ($namespaceData->read()->toArray() as $index => $values) {

                if ($index == 'default') {
                    $locale = $defaultLocale;
                } else {
                    $locale = $localeRepository->findOneBy(array('locale' => $index));
                }

                $namespaceData->setSection(array(1, $locale->getId()))->read();
                $namespaceData->setData($values);
                $this->outputAdapter->delete($namespaceData, array_keys($values), $removeDirty);
                if ($this->output) {
                    $this->output->writeln('<info>Deleted ' . count($values) . ' snippets from ' . $locale->getLocale() . '</info>');
                }
            }

            if ($this->output) {
                $this->output->writeln('<info></info>');
            }
        }
    }
}
