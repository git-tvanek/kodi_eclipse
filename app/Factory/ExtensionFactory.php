<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IExtensionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Základní továrna s podporou rozšíření
 * 
 * @template T of object
 * @extends BaseFactory<T>
 * @implements IExtensionFactory<T>
 */
abstract class ExtensionFactory extends BaseFactory implements IExtensionFactory
{
    /**
     * Konstanty pro fáze životního cyklu
     */
    public const PHASE_BEFORE_CREATE = 'before_create';
    public const PHASE_AFTER_CREATE = 'after_create';
    public const PHASE_BEFORE_UPDATE = 'before_update';
    public const PHASE_AFTER_UPDATE = 'after_update';
    
    /**
     * Seznam všech podporovaných fází
     * 
     * @var array<string>
     */
    protected static array $supportedPhases = [
        self::PHASE_BEFORE_CREATE,
        self::PHASE_AFTER_CREATE,
        self::PHASE_BEFORE_UPDATE,
        self::PHASE_AFTER_UPDATE
    ];
    
    /**
     * Registrovaná rozšíření
     * 
     * @var array<string, array<string, callable>>
     */
    protected array $extensions = [];
    
    /**
     * Konstrukce továrny s rozšířeními
     * 
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?ValidatorInterface $validator = null
    ) {
        parent::__construct($entityManager, $validator);
        
        // Inicializace polí pro každou fázi
        foreach (self::$supportedPhases as $phase) {
            $this->extensions[$phase] = [];
        }
        
        // Inicializace rozšíření
        $this->initializeExtensions();
    }
    
    /**
     * Inicializuje výchozí rozšíření
     * Může být přepsáno v odvozených třídách pro přidání vlastních rozšíření
     */
    protected function initializeExtensions(): void
    {
        // Základní implementace je prázdná
    }
    
    /**
     * {@inheritdoc}
     */
    public function addExtension(string $name, string $phase, callable $callback): self
    {
        // Kontrola platnosti fáze
        if (!in_array($phase, self::$supportedPhases)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported phase "%s". Supported phases are: %s',
                $phase,
                implode(', ', self::$supportedPhases)
            ));
        }
        
        $this->extensions[$phase][$name] = $callback;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function removeExtension(string $name): self
    {
        foreach ($this->extensions as $phase => $extensions) {
            if (isset($extensions[$name])) {
                unset($this->extensions[$phase][$name]);
            }
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasExtension(string $name): bool
    {
        foreach ($this->extensions as $extensions) {
            if (isset($extensions[$name])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function runExtensions(string $phase, $data)
    {
        // Kontrola platnosti fáze
        if (!isset($this->extensions[$phase])) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported phase "%s". Supported phases are: %s',
                $phase,
                implode(', ', self::$supportedPhases)
            ));
        }
        
        // Spuštění všech rozšíření pro danou fázi
        foreach ($this->extensions[$phase] as $callback) {
            $data = $callback($data, $this);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): object
    {
        // Spuštění rozšíření před vytvořením
        $data = $this->runExtensions(self::PHASE_BEFORE_CREATE, $data);
        
        // Vytvoření entity pomocí rodičovské implementace
        $entity = parent::create($data);
        
        // Spuštění rozšíření po vytvoření
        $entity = $this->runExtensions(self::PHASE_AFTER_CREATE, $entity);
        
        return $entity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): object
    {
        // Spuštění rozšíření před aktualizací
        $overrideData = $this->runExtensions(self::PHASE_BEFORE_UPDATE, $overrideData);
        
        // Vytvoření nebo aktualizace entity pomocí rodičovské implementace
        $updatedEntity = parent::createFromExisting($entity, $overrideData, $createNew);
        
        // Spuštění rozšíření po aktualizaci
        $updatedEntity = $this->runExtensions(self::PHASE_AFTER_UPDATE, $updatedEntity);
        
        return $updatedEntity;
    }
}