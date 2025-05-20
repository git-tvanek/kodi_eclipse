<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Screenshot;
use App\Entity\Addon;
use App\Factory\Interface\IScreenshotFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Továrna pro vytváření screenshotů
 * 
 * @template-extends BaseFactory<Screenshot>
 * @implements IScreenshotFactory
 */
class ScreenshotFactory extends BaseFactory implements IScreenshotFactory
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
        return Screenshot::class;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'addon_id',
            'url'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'description' => null,
            'sort_order' => 0
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function processBeforeCreate(array $data): array
    {
        // Převedení ID na referenci entity
        if (isset($data['addon_id'])) {
            $data['addon'] = $this->entityManager->getReference(Addon::class, $data['addon_id']);
            unset($data['addon_id']);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Screenshot
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): Screenshot
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createWithDescription(int $addonId, string $url, ?string $description = null, int $sortOrder = 0): Screenshot
    {
        return $this->create([
            'addon_id' => $addonId,
            'url' => $url,
            'description' => $description,
            'sort_order' => $sortOrder
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBatch(int $addonId, array $urls): array
    {
        $screenshots = [];
        $index = 0;
        
        foreach ($urls as $url) {
            $screenshots[] = $this->create([
                'addon_id' => $addonId,
                'url' => $url,
                'sort_order' => $index++
            ]);
        }
        
        return $screenshots;
    }
}