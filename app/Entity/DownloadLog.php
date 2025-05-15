<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\DownloadLogRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DownloadLogRepository::class)]
#[ORM\Table(name: 'downloads_log')]
#[ORM\Index(columns: ['created_at'], name: 'idx_download_created_at')]
class DownloadLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;
    
    #[ORM\ManyToOne(targetEntity: Addon::class)]
    #[ORM\JoinColumn(name: 'addon_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Addon $addon;
    
    #[ORM\Column(type: 'datetime')]
    private DateTime $created_at;
    
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ip_address = null;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $user_agent = null;
    
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->created_at = new DateTime();
    }
    
    /**
     * Vrací ID záznamu
     * 
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * Vrací doplněk, ke kterému se tento záznam vztahuje
     * 
     * @return Addon
     */
    public function getAddon(): Addon
    {
        return $this->addon;
    }
    
    /**
     * Nastaví doplněk, ke kterému se tento záznam vztahuje
     * 
     * @param Addon $addon
     * @return self
     */
    public function setAddon(Addon $addon): self
    {
        $this->addon = $addon;
        return $this;
    }
    
    /**
     * Vrací datum a čas vytvoření záznamu
     * 
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }
    
    /**
     * Nastaví datum a čas vytvoření záznamu
     * 
     * @param DateTime $created_at
     * @return self
     */
    public function setCreatedAt(DateTime $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
    
    /**
     * Vrací IP adresu uživatele
     * 
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }
    
    /**
     * Nastaví IP adresu uživatele
     * 
     * @param string|null $ip_address
     * @return self
     */
    public function setIpAddress(?string $ip_address): self
    {
        $this->ip_address = $ip_address;
        return $this;
    }
    
    /**
     * Vrací User Agent uživatele
     * 
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }
    
    /**
     * Nastaví User Agent uživatele
     * 
     * @param string|null $user_agent
     * @return self
     */
    public function setUserAgent(?string $user_agent): self
    {
        $this->user_agent = $user_agent;
        return $this;
    }
    
    /**
     * Konvertuje entitu do pole
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'addon_id' => $this->addon->getId(),
            'created_at' => $this->created_at,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent
        ];
    }
    
    /**
     * Vytvoří entitu z pole dat
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $entity = new self();
        
        if (isset($data['created_at']) && !($data['created_at'] instanceof DateTime)) {
            $data['created_at'] = new DateTime($data['created_at']);
        }
        
        if (isset($data['created_at'])) {
            $entity->setCreatedAt($data['created_at']);
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