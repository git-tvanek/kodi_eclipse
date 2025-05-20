<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Author;
use App\Factory\Interface\IAuthorFactory;
use App\Factory\Builder\AuthorBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

/**
 * Továrna pro vytváření autorů
 * 
 * @template-extends BuilderFactory<Author, AuthorBuilder>
 * @implements IAuthorFactory
 */
class AuthorFactory extends BuilderFactory implements IAuthorFactory
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
        return Author::class;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilder(): AuthorBuilder
    {
        return new AuthorBuilder($this);
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
            'email' => null,
            'website' => null,
            'is_deleted' => false
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Author
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Author
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createWithName(string $name): Author
    {
        return $this->create([
            'name' => $name
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createWithContact(string $name, ?string $email = null, ?string $website = null): Author
    {
        return $this->create([
            'name' => $name,
            'email' => $email,
            'website' => $website
        ]);
    }
}