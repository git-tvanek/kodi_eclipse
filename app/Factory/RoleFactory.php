<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Role;
use App\Factory\Interface\IRoleFactory;
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
     * {@inheritdoc}
     */
    public function createFromExisting(Role $role, array $data): Role
    {
        $overrideData = $data;
        $overrideData['id'] = $role->getId();
        
        return parent::createFromExisting($role, $overrideData, false);
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
}