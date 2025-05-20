<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Factory\Interface\IAddonFactory;
use App\Factory\Builder\AddonBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

/**
 * Továrna pro vytváření doplňků
 * 
 * @template-extends BuilderFactory<Addon, AddonBuilder>
 * @implements IAddonFactory
 */
class AddonFactory extends BuilderFactory implements IAddonFactory
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?ValidatorInterface $validator = null
    ) {
        parent::__construct($entityManager, $validator);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return Addon::class;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilder(): AddonBuilder
    {
        return new AddonBuilder($this);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'name',
            'version',
            'author_id',
            'category_id',
            'download_url'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'description' => null,
            'repository_url' => null,
            'icon_url' => null,
            'fanart_url' => null,
            'kodi_version_min' => null,
            'kodi_version_max' => null,
            'downloads_count' => 0,
            'rating' => 0.0
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDerivedFields(): array
    {
        return [
            'slug' => 'name'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function processBeforeCreate(array $data): array
    {
        // Převedení ID na reference entit
        if (isset($data['author_id'])) {
            $data['author'] = $this->entityManager->getReference(Author::class, $data['author_id']);
            unset($data['author_id']);
        }
        
        if (isset($data['category_id'])) {
            $data['category'] = $this->entityManager->getReference(Category::class, $data['category_id']);
            unset($data['category_id']);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Addon
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Addon
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBase(
        string $name,
        string $version,
        int $authorId,
        int $categoryId,
        string $downloadUrl
    ): Addon
    {
        return $this->create([
            'name' => $name,
            'version' => $version,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'download_url' => $downloadUrl
        ]);
    }
}