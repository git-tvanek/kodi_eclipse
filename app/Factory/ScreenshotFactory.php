<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\Screenshot;
use App\Factory\Interface\IScreenshotFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Screenshot
 * 
 * @extends BaseFactory<Screenshot>
 * @implements IScreenshotFactory<Screenshot>
 */
class ScreenshotFactory extends BaseFactory implements IScreenshotFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Screenshot::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Screenshot
    {
        /** @var Screenshot $screenshot */
        $screenshot = $this->createNewInstance();
        return $this->createFromExisting($screenshot, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Screenshot
    {
        if (isset($data['addon_id'])) {
            /** @var Addon $addon */
            $addon = $this->getReference(Addon::class, (int)$data['addon_id']);
            $entity->setAddon($addon);
        } elseif (isset($data['addon']) && $data['addon'] instanceof Addon) {
            $entity->setAddon($data['addon']);
        }
        
        if (isset($data['url'])) {
            $entity->setUrl($data['url']);
        }
        
        if (isset($data['description'])) {
            $entity->setDescription($data['description']);
        }
        
        if (isset($data['sort_order'])) {
            $entity->setSortOrder((int)$data['sort_order']);
        }
        
        return $entity;
    }
}