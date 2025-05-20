<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Category;
use App\Factory\Interface\ICategoryFactory;
use App\Factory\Builder\CategoryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nette\Utils\Strings;

/**
 * Továrna pro vytváření kategorií
 * 
 * @template-extends BuilderFactory<Category, CategoryBuilder>
 * @implements ICategoryFactory
 */
class CategoryFactory extends BuilderFactory implements ICategoryFactory
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
        return Category::class;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilder(): CategoryBuilder
    {
        return new CategoryBuilder($this);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'name'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'parent' => null,
            'is_deleted' => false
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
        // Převedení ID nadřazené kategorie na referenci
        if (isset($data['parent_id'])) {
            $data['parent'] = $this->entityManager->getReference(Category::class, $data['parent_id']);
            unset($data['parent_id']);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Category
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Category
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createRoot(string $name, ?string $slug = null): Category
    {
        return $this->create([
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createSubcategory(string $name, int $parentId, ?string $slug = null): Category
    {
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId
        ]);
    }
}