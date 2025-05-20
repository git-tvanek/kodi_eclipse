<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Permission;
use App\Factory\Interface\IPermissionFactory;
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
    public function createFromExisting(Permission $permission, array $data): Permission
    {
        return parent::createFromExisting($permission, $data);
    }
}