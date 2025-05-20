<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IBuilderFactory;
use App\Factory\Interface\IEntityBuilder;
use App\Factory\Interface\ISchemaFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Továrna s podporou schémat i builderů
 * 
 * @template T of object
 * @template B of IEntityBuilder
 * @extends SchemaFactory<T>
 * @implements IBuilderFactory<T, B>
 */
abstract class SchemaBuilderFactory extends SchemaFactory implements IBuilderFactory
{
    /**
     * Konstrukce továrny s schématy a buildery
     * 
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
    abstract public function createBuilder(): object;
    
    /**
     * Vytvoří entitu z dat builderu
     * 
     * @param array $data
     * @return T
     */
    public function createFromBuilder(array $data): object
    {
        return $this->create($data);
    }
}