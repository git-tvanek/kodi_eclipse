<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Role;
use App\Entity\Permission;
use App\Factory\Interface\IRoleFactory;
use App\Factory\Builder\RoleBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nette\Utils\Strings;

/**
 * Továrna pro vytváření rolí
 * 
 * @template-extends SchemaFactory<Role>
 * @implements IRoleFactory
 */
class RoleFactory extends SchemaFactory implements IRoleFactory
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
        return Role::class;
    }
    
    /**
     * Vytvoří builder pro fluent rozhraní
     * 
     * @return RoleBuilder
     */
    public function createBuilder(): RoleBuilder
    {
        return new RoleBuilder($this);
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
            'description' => null,
            'priority' => 0
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDerivedFields(): array
    {
        return [
            'code' => 'name'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function processBeforeCreate(array $data): array
    {
        // Validace kódu role
        if (isset($data['code']) && !preg_match('/^[a-zA-Z0-9_]+$/', $data['code'])) {
            throw new \InvalidArgumentException('Kód role může obsahovat pouze písmena, čísla a podtržítka.');
        }
        
        // Převedení ID oprávnění na reference entit, pokud existují
        if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
            $data['permissions'] = [];
            foreach ($data['permission_ids'] as $permissionId) {
                $data['permissions'][] = $this->entityManager->getReference(
                    Permission::class, 
                    $permissionId
                );
            }
            unset($data['permission_ids']);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateSchema(array $data): array
    {
        // Validace unikátnosti kódu
        if (isset($data['code'])) {
            $existingRole = $this->entityManager->getRepository(Role::class)->findOneBy(['code' => $data['code']]);
            
            if ($existingRole && (!isset($data['id']) || $existingRole->getId() !== $data['id'])) {
                throw new \InvalidArgumentException("Role with code '{$data['code']}' already exists");
            }
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Role
    {
        return parent::create($data);
    }
    
    /**
     * Vytvoří entitu z dat builderu
     * 
     * @param array $data
     * @return Role
     */
    public function createFromBuilder(array $data): Role
    {
        return $this->create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Role
    {
        // Kontrola, zda je entita správného typu
        if (!($entity instanceof Role)) {
            throw new \InvalidArgumentException(sprintf(
                'Entity must be instance of %s, %s given',
                Role::class,
                get_class($entity)
            ));
        }
        
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromName(string $name, ?string $code = null, ?string $description = null, int $priority = 0): Role
    {
        return $this->create([
            'name' => $name,
            'code' => $code ?? Strings::webalize($name),
            'description' => $description,
            'priority' => $priority
        ]);
    }
    
    /**
     * Přidá oprávnění k existující roli
     * 
     * @param Role $role
     * @param Permission $permission
     * @return Role
     */
    public function addPermissionToRole(Role $role, Permission $permission): Role
    {
        $role->addPermission($permission);
        return $role;
    }
    
    /**
     * Odebere oprávnění z existující role
     * 
     * @param Role $role
     * @param Permission $permission
     * @return Role
     */
    public function removePermissionFromRole(Role $role, Permission $permission): Role
    {
        $role->removePermission($permission);
        return $role;
    }
}