<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Commands;

use Doctrine\ORM\Query\Expr\Join;
use Exception;
use RuntimeException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shopware ThumbnailGenerateCommand Class
 *
 * This class is used as a command to generate thumbnails from media albums.
 * If no album is defined, thumbnails from all album medias are created.
 */
class ThumbnailGenerateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Manager
     */
    private $generator;

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'albumid') {
            $albumRepository = $this->getContainer()->get(ModelManager::class)->getRepository(Album::class);

            $queryBuilder = $albumRepository->createQueryBuilder('alb')
                ->innerJoin('alb.settings', 'settings', Join::WITH, 'settings.createThumbnails = 1');

            if (is_numeric($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('alb.id', ':id'))
                    ->setParameter('id', addcslashes($context->getCurrentWord(), '%_') . '%');
            }

            $result = $queryBuilder->select(['alb.id'])
                ->getQuery()
                ->getArrayResult();

            return array_column($result, 'id');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:thumbnail:generate')
            ->setDescription('Generates a new Thumbnail.')
            ->addOption(
                'albumid',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of the album which contains the images'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force complete thumbnail generation'
            )
            ->setHelp('The <info>%command.name%</info> generates a thumbnail.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->force = (bool) $input->getOption('force');
        $this->errors = [];
        $this->generator = $this->getContainer()->get(Manager::class);

        $albumId = (int) $input->getOption('albumid');

        foreach ($this->getMediaAlbums($albumId) as $album) {
            $this->createAlbumThumbnails($album);
        }

        $this->printExitMessage();

        return 0;
    }

    /**
     * @param int $albumId
     *
     * @return Album[]
     */
    protected function getMediaAlbums($albumId)
    {
        $em = $this->getContainer()->get(ModelManager::class);

        $builder = $em->createQueryBuilder();
        $builder
            ->select(['album', 'settings'])
            ->from(Album::class, 'album')
            ->innerJoin('album.settings', 'settings', 'WITH', 'settings.createThumbnails = 1');

        if (!empty($albumId)) {
            $builder
                ->where('album.id = :albumId')
                ->setParameter('albumId', $albumId);
        }

        return $builder->getQuery()->getResult();
    }

    protected function printExitMessage()
    {
        if (\count($this->errors) === 0) {
            $this->output->writeln('<info>Thumbnail generation finished successfully</info>');

            return;
        }

        $this->output->writeln('<error>Thumbnail generation finished with errors</error>');

        foreach ($this->errors as $error) {
            $this->output->writeln('<comment>' . $error . '</comment>');
        }
    }

    /**
     * @throws Exception
     */
    private function createAlbumThumbnails(Album $album)
    {
        $this->output->writeln("Generating Thumbnails for Album {$album->getName()} (ID: {$album->getId()})");

        $em = $this->getContainer()->get(ModelManager::class);

        $repository = $em->getRepository(Media::class);

        $query = $repository->getAlbumMediaQuery($album->getId());
        $paginator = $em->createPaginator($query);

        $total = $paginator->count();

        $progressBar = new ProgressBar($this->output, $total);
        $progressBar->setRedrawFrequency(10);
        $progressBar->start();

        foreach ($paginator as $media) {
            try {
                $this->createMediaThumbnails($media);
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        // Force newline when processing the next album
        $this->output->writeln('');
    }

    /**
     * Check each single thumbnail to skip already existing thumbnails
     *
     * @throws Exception
     */
    private function createMediaThumbnails(Media $media)
    {
        if (!$this->imageExists($media)) {
            throw new Exception(sprintf('Base image file "%s" does not exist', $media->getPath()));
        }

        $thumbnails = $media->getThumbnailFilePaths();
        foreach ($thumbnails as $size => $path) {
            if (!$this->force && $this->thumbnailExists($path)) {
                continue;
            }

            $this->generator->createMediaThumbnail($media, [$size], true);
        }
    }

    /**
     * @param string $thumbnailPath
     *
     * @throws Exception
     *
     * @return bool
     */
    private function thumbnailExists($thumbnailPath)
    {
        $mediaService = $this->container->get(MediaServiceInterface::class);
        $projectDir = $this->container->getParameter('shopware.app.rootDir');

        if (!\is_string($projectDir)) {
            throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
        }

        return $mediaService->has($projectDir . $thumbnailPath);
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    private function imageExists(Media $media)
    {
        $mediaService = $this->container->get(MediaServiceInterface::class);
        $projectDir = $this->container->getParameter('shopware.app.rootDir');

        if (!\is_string($projectDir)) {
            throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
        }

        return $mediaService->has($projectDir . $media->getPath());
    }
}
