<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IBaseFactory;
use DateTime;
use Nette\Utils\Strings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Základní továrna pro entity
 * 
 * @template T of object
 * @implements IBaseFactory<T>
 */
abstract class BaseFactory implements IBaseFactory
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;
    
    /** @var ValidatorInterface|null */
    protected ?ValidatorInterface $validator;
    
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager, 
        ?ValidatorInterface $validator = null
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }
    
    /**
     * {@inheritdoc}
     */
    abstract public function getEntityClass(): string;
    
    /**
     * Vrací seznam povinných polí
     * 
     * @return array<string>
     */
    protected function getRequiredFields(): array
    {
        return [];
    }
    
    /**
     * Vrací výchozí hodnoty pro nepovinná pole
     * 
     * @return array<string, mixed>
     */
    protected function getDefaultValues(): array
    {
        return [];
    }
    
    /**
     * Vrací definice odvozených polí (např. slug z name)
     * 
     * @return array<string, string> [cílové pole => zdrojové pole]
     */
    protected function getDerivedFields(): array
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): object
    {
        // Validace povinných polí
        $this->validateRequiredFields($data);
        
        // Aplikace výchozích hodnot
        $data = $this->applyDefaultValues($data);
        
        // Zpracování speciálních polí
        $data = $this->processSpecialFields($data);
        
        // Doplnění časových razítek
        $data = $this->addTimestamps($data, true);
        
        // Zpracování před vytvořením entity
        $data = $this->processBeforeCreate($data);
        
        // Vytvoření entity
        $entity = $this->createEntity($data);
        
        // Doctrine validace
        $this->validateEntity($entity);
        
        return $entity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): object
    {
        // Kontrola, zda je entita správného typu
        $entityClass = $this->getEntityClass();
        if (!($entity instanceof $entityClass)) {
            throw new \InvalidArgumentException(sprintf(
                'Entity must be instance of %s, %s given',
                $entityClass,
                get_class($entity)
            ));
        }
        
        // Získat data entity
        $data = $this->entityToArray($entity);
        
        // Přepsat data
        foreach ($overrideData as $key => $value) {
            $data[$key] = $value;
        }
        
        // Při vytváření nové instance odstranit ID
        if ($createNew && isset($data['id'])) {
            unset($data['id']);
        }
        
        // Zpracování odvozených polí
        $data = $this->updateDerivedFields($data);
        
        // Aktualizovat časová razítka
        $data = $this->addTimestamps($data, $createNew);
        
        // Vytvořit novou entitu (nebo aktualizovat existující)
        return $this->create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function updateTimestamps(object $entity, bool $isNew = true): object
    {
        $now = new DateTime();
        
        if ($isNew && method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt($now);
        }
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
        
        return $entity;
    }
    
    /**
     * Validuje entitu pomocí Doctrine validátoru
     * 
     * @param object $entity
     * @throws \InvalidArgumentException
     */
    protected function validateEntity(object $entity): void
    {
        if ($this->validator) {
            $violations = $this->validator->validate($entity);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $property = $violation->getPropertyPath();
                    $message = $violation->getMessage();
                    $errors[] = "$property: $message";
                }
                
                throw new \InvalidArgumentException(
                    sprintf("Entity validation failed: %s", implode('; ', $errors))
                );
            }
        }
    }
    
    /**
     * Validuje přítomnost povinných polí
     * 
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateRequiredFields(array $data): void
    {
        foreach ($this->getRequiredFields() as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \InvalidArgumentException("$field is required");
            }
        }
    }
    
    /**
     * Aplikuje výchozí hodnoty na data
     * 
     * @param array $data
     * @return array
     */
    protected function applyDefaultValues(array $data): array
    {
        foreach ($this->getDefaultValues() as $field => $value) {
            if (!isset($data[$field])) {
                $data[$field] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * Zpracuje speciální pole jako slug, datumy apod.
     * 
     * @param array $data
     * @return array
     */
    protected function processSpecialFields(array $data): array
    {
        // Odvozená pole
        $data = $this->updateDerivedFields($data);
        
        return $data;
    }
    
    /**
     * Aktualizuje odvozená pole na základě jiných polí
     * 
     * @param array $data
     * @return array
     */
    protected function updateDerivedFields(array $data): array
    {
        foreach ($this->getDerivedFields() as $targetField => $sourceField) {
            if (isset($data[$sourceField]) && (!isset($data[$targetField]) || $data[$targetField] === '')) {
                // Speciální případ pro slug
                if ($targetField === 'slug') {
                    $data[$targetField] = Strings::webalize($data[$sourceField]);
                }
                // Zde mohou být další pravidla pro odvozená pole
            }
        }
        
        return $data;
    }
    
    /**
     * Přidá časová razítka k datům
     * 
     * @param array $data
     * @param bool $isNew
     * @return array
     */
    protected function addTimestamps(array $data, bool $isNew): array
    {
        $now = new DateTime();
        
        if ($isNew && !isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        
        $data['updated_at'] = $now;
        
        return $data;
    }
    
    /**
     * Zpracuje data těsně před vytvořením entity
     * Může být přepsáno v konkrétních továrnách
     * 
     * @param array $data
     * @return array
     */
    protected function processBeforeCreate(array $data): array
    {
        return $data;
    }
    
    /**
     * Vytvoří entitu z připravených dat
     * 
     * @param array $data
     * @return T
     */
    protected function createEntity(array $data): object
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();
        
        foreach ($data as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }
        
        return $entity;
    }
    
    /**
     * Převede entitu na pole
     * 
     * @param object $entity
     * @return array
     */
    protected function entityToArray(object $entity): array
    {
        if (method_exists($entity, 'toArray')) {
            return $entity->toArray();
        }
        
        // Jednoduchá reflexe
        $data = [];
        $reflection = new \ReflectionObject($entity);
        
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            if ($property->isInitialized($entity)) {
                $data[$name] = $property->getValue($entity);
            }
        }
        
        return $data;
    }
    
    /**
     * Získá referenci na entitu podle ID
     * 
     * @param string $entityClass
     * @param int $id
     * @return object
     */
    protected function getReference(string $entityClass, int $id): object
    {
        return $this->entityManager->getReference($entityClass, $id);
    }
}