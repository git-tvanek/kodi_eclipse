<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AddonRepository extends EntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
        // Předání metadat entity a entity manageru do předka
        $metadata = $entityManager->getClassMetadata(Addon::class);
        parent::__construct($entityManager, $metadata);
    }

    /**
     * Najde doplněk podle slugu
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Najde doplňky podle kategorie s paginací
     */
    public function findByCategory(Category $category, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.category = :category')
            ->setParameter('category', $category)
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }

    /**
     * Najde doplňky podle autora s paginací
     */
    public function findByAuthor(Author $author, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.author = :author')
            ->setParameter('author', $author)
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }

    /**
     * Najde populární doplňky
     */
    public function findPopular(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.downloads_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new Collection($addons);
    }

    /**
     * Najde nejlépe hodnocené doplňky
     */
    public function findTopRated(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new Collection($addons);
    }

    /**
     * Najde nejnovější doplňky
     */
    public function findNewest(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new Collection($addons);
    }

    /**
     * Vyhledá doplňky podle klíčového slova
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.name LIKE :query OR a.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }

    /**
     * Zvýší počet stažení doplňku
     */
    public function incrementDownloadCount(Addon $addon): void
    {
        $addon->incrementDownloadsCount();
        $this->entityManager->persist($addon);
        $this->entityManager->flush();
    }

    /**
     * Aktualizuje hodnocení doplňku
     */
    public function updateRating(Addon $addon): void
    {
        $avgRating = $this->entityManager->createQuery('
            SELECT AVG(r.rating) FROM App\Entity\AddonReview r WHERE r.addon = :addon
        ')
        ->setParameter('addon', $addon)
        ->getSingleScalarResult();

        $addon->setRating($avgRating ?: 0);
        $this->entityManager->persist($addon);
        $this->entityManager->flush();
    }

    /**
     * Vytvoří doplněk včetně relací
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tags = []): void
    {
        $this->entityManager->beginTransaction();

        try {
            // Přidání screenshotů
            foreach ($screenshots as $index => $screenshot) {
                $screenshot->setAddon($addon);
                $screenshot->setSortOrder($index);
                $addon->addScreenshot($screenshot);
            }

            // Přidání tagů
            foreach ($tags as $tag) {
                $addon->addTag($tag);
            }

            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Aktualizuje doplněk včetně relací
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tags = []): void
    {
        $this->entityManager->beginTransaction();

        try {
            // Nejprve odstraníme všechny existující screenshoty
            foreach ($addon->getScreenshots() as $screenshot) {
                $addon->removeScreenshot($screenshot);
                $this->entityManager->remove($screenshot);
            }

            // Přidáme nové screenshoty
            foreach ($screenshots as $index => $screenshot) {
                $screenshot->setAddon($addon);
                $screenshot->setSortOrder($index);
                $addon->addScreenshot($screenshot);
            }

            // Odstraníme všechny tagy
            foreach ($addon->getTags() as $tag) {
                $addon->removeTag($tag);
            }

            // Přidáme nové tagy
            foreach ($tags as $tag) {
                $addon->addTag($tag);
            }

            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Najde podobné doplňky
     */
    public function findSimilarAddons(Addon $addon, int $limit = 5): Collection
    {
        $categoryId = $addon->getCategory()->getId();
        $addonId = $addon->getId();
        
        $qb = $this->createQueryBuilder('a')
            ->where('a.id != :addonId')
            ->andWhere('a.category = :category')
            ->setParameter('addonId', $addonId)
            ->setParameter('category', $addon->getCategory())
            ->orderBy('a.downloads_count', 'DESC')
            ->setMaxResults($limit);

        // Pokud má doplněk tagy, hledáme podle nich
        if (count($addon->getTags()) > 0) {
            $qb = $this->createQueryBuilder('a')
                ->distinct()
                ->join('a.tags', 't')
                ->where('a.id != :addonId')
                ->andWhere('t IN (:tags)')
                ->setParameter('addonId', $addonId)
                ->setParameter('tags', $addon->getTags()->toArray())
                ->orderBy('a.downloads_count', 'DESC')
                ->setMaxResults($limit);
        }

        $similarAddons = $qb->getQuery()->getResult();
        return new Collection($similarAddons);
    }

    /**
     * Paginuje výsledky
     */
    private function paginate(QueryBuilder $qb, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $paginator = new Paginator($qb);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $collection = new Collection(iterator_to_array($paginator->getIterator()));
        $total = count($paginator);
        $pageCount = ceil($total / $itemsPerPage);

        return new PaginatedCollection(
            $collection,
            $total,
            $page,
            $itemsPerPage,
            (int) $pageCount
        );
    }
}