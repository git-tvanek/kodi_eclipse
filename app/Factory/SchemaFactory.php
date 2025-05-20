<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\ISchemaFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Továrna s podporou validačních schémat
 * 
 * @template T of object
 * @extends ExtensionFactory<T>
 * @implements ISchemaFactory<T>
 */
abstract class SchemaFactory extends ExtensionFactory implements ISchemaFactory
{
    /**
     * Konstrukce továrny se schématem
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
    protected function initializeExtensions(): void
    {
        parent::initializeExtensions();
        
        // Přidání validace schématu jako rozšíření
        $this->addExtension('schema_validation', self::PHASE_BEFORE_CREATE, function($data, $factory) {
            if (is_array($data)) {
                return $this->validateSchema($data);
            }
            return $data;
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateSchema(array $data): array
    {
        // Základní implementace pouze vrací data beze změny
        // Konkrétní implementace by měla být v odvozených třídách
        return $data;
    }
}