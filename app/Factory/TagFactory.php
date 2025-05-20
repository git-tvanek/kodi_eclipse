<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Tag;
use App\Factory\Interface\ITagFactory;
use App\Factory\Builder\TagBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nette\Utils\Strings;

/**
 * Továrna pro vytváření tagů
 * 
 * @template-extends BuilderFactory<Tag, TagBuilder>
 * @implements ITagFactory
 */
class TagFactory extends BuilderFactory implements ITagFactory
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
        return Tag::class;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilder(): TagBuilder
    {
        return new TagBuilder($this);
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
    protected function getDerivedFields(): array
    {
        return [
            'slug' => 'name'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Tag
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Tag
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createWithName(string $name): Tag
    {
        return $this->create([
            'name' => $name
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBatch(array $names): array
    {
        $tags = [];
        
        foreach ($names as $name) {
            $tags[] = $this->createWithName($name);
        }
        
        return $tags;
    }
}