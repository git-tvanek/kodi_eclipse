<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette\SmartObject;

class Author
{
    use SmartObject;

    public int $id;
    public string $name;
    public ?string $email;
    public ?string $website;
    public DateTime $created_at;

    /**
     * Create an Author instance from array data
     */
    public static function fromArray(array $data): self
    {
        $author = new self();
        
        if (isset($data['id'])) {
            $author->id = (int) $data['id'];
        }
        
        $author->name = $data['name'];
        $author->email = $data['email'] ?? null;
        $author->website = $data['website'] ?? null;
        
        if (isset($data['created_at'])) {
            $author->created_at = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
        } else {
            $author->created_at = new DateTime();
        }
        
        return $author;
    }

    /**
     * Convert the Author instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'email' => $this->email,
            'website' => $this->website,
            'created_at' => $this->created_at,
        ];
    }
}