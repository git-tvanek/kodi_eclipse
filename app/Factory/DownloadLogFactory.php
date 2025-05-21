<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\DownloadLog;
use App\Factory\Interface\IDownloadLogFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy DownloadLog
 * 
 * @extends BaseFactory<DownloadLog>
 * @implements IDownloadLogFactory<DownloadLog>
 */
class DownloadLogFactory extends BaseFactory implements IDownloadLogFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DownloadLog::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): DownloadLog
    {
        /** @var DownloadLog $downloadLog */
        $downloadLog = $this->createNewInstance();
        return $this->createFromExisting($downloadLog, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): DownloadLog
    {
        if (isset($data['addon_id'])) {
            /** @var Addon $addon */
            $addon = $this->getReference(Addon::class, (int)$data['addon_id']);
            $entity->setAddon($addon);
        } elseif (isset($data['addon']) && $data['addon'] instanceof Addon) {
            $entity->setAddon($data['addon']);
        }
        
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
            $entity->setCreatedAt($createdAt);
        } elseif ($isNew) {
            $entity->setCreatedAt(new DateTime());
        }
        
        if (isset($data['ip_address'])) {
            $entity->setIpAddress($data['ip_address']);
        }
        
        if (isset($data['user_agent'])) {
            $entity->setUserAgent($data['user_agent']);
        }
        
        return $entity;
    }
}