<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\ISchemaFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Továrna používající validační schéma
 * 
 * @template T of object
 * @extends BaseFactory<T>
 * @implements ISchemaFactory<T>
 */
abstract class SchemaFactory extends BaseFactory implements ISchemaFactory
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
    public function create(array $data): object
    {
        // Validace dat podle schématu
        $validatedData = $this->validateSchema($data);
        
        // Pokračovat standardním procesem
        return parent::create($validatedData);
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateSchema(array $data): array
    {
        // Tato metoda musí být implementována v konkrétních továrnách
        // V základní implementaci pouze vrátí data beze změny
        return $data;
    }
}