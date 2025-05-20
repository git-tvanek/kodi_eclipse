<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Permission;
use App\Factory\Interface\IPermissionFactory;
use App\Factory\Builder\PermissionBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Továrna pro vytváření oprávnění
 * 
 * @template-extends BaseFactory<Permission>
 * @implements IPermissionFactory
 */
class PermissionFactory extends BaseFactory implements IPermissionFactory
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
        return Permission::class;
    }
    
    /**
     * Vytvoří builder pro fluent rozhraní
     * 
     * @return PermissionBuilder
     */
    public function createBuilder(): PermissionBuilder
    {
        return new PermissionBuilder($this);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'name',
            'resource',
            'action'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'description' => null
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Permission
    {
        return parent::create($data);
    }
    
    /**
     * Vytvoří entitu z dat builderu
     * 
     * @param array $data
     * @return Permission
     */
    public function createFromBuilder(array $data): Permission
    {
        return $this->create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createPermission(string $name, string $resource, string $action, ?string $description = null): Permission
    {
        return $this->create([
            'name' => $name,
            'resource' => $resource,
            'action' => $action,
            'description' => $description
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Permission
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * Vytvoří sadu CRUD oprávnění pro zadaný zdroj
     * 
     * @param string $resource Název zdroje
     * @return array<Permission> Pole vytvořených oprávnění
     * @throws \InvalidArgumentException Pokud zdroj neodpovídá požadovanému formátu
     */
    public function createCrudPermissions(string $resource): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $resource)) {
            throw new \InvalidArgumentException('Zdroj může obsahovat pouze písmena, čísla a podtržítka.');
        }
        
        return [
            $this->createPermission(
                "Vytvoření {$resource}", 
                $resource, 
                'create', 
                "Oprávnění pro vytvoření {$resource}"
            ),
            $this->createPermission(
                "Čtení {$resource}", 
                $resource, 
                'read', 
                "Oprávnění pro čtení {$resource}"
            ),
            $this->createPermission(
                "Úprava {$resource}", 
                $resource, 
                'update', 
                "Oprávnění pro úpravu {$resource}"
            ),
            $this->createPermission(
                "Odstranění {$resource}", 
                $resource, 
                'delete', 
                "Oprávnění pro odstranění {$resource}"
            )
        ];
    }
}