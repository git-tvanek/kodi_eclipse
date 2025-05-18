<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Author;
use App\Factory\Interface\IAuthorFactory;
use DateTime;

/**
 * Továrna pro vytváření autorů
 * 
 * @implements IFactory<Author>
 */
class AuthorFactory implements IAuthorFactory
{
    /**
     * Vytvoří novou instanci autora
     * 
     * @param array $data
     * @return Author
     */
    public function create(array $data): Author
    {
        // Zajištění povinných polí
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Author name is required');
        }

        // Výchozí hodnoty pro nepovinná pole
        $data['email'] = $data['email'] ?? null;
        $data['website'] = $data['website'] ?? null;
        $data['created_at'] = $data['created_at'] ?? new DateTime();
        
        return Author::fromArray($data);
    }

    /**
     * Vytvoří kopii existujícího autora
     * 
     * @param Author $author Existující autor
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Author
     */
    public function createFromExisting(Author $author, array $overrideData = [], bool $createNew = true): Author
    {
        $data = $author->toArray();
        
        // Přepsat data novými hodnotami
        foreach ($overrideData as $key => $value) {
            $data[$key] = $value;
        }
        
        // Při vytváření nové instance odstranit ID
        if ($createNew) {
            unset($data['id']);
            // Při vytváření kopie nastavit nové datum vytvoření
            $data['created_at'] = new DateTime();
        }
        
        return Author::fromArray($data);
    }

    /**
     * Vytvoří základního autora pouze s jménem
     * 
     * @param string $name Jméno autora
     * @return Author
     */
    public function createWithName(string $name): Author
    {
        return $this->create([
            'name' => $name
        ]);
    }

    /**
     * Vytvoří autora s kontaktními údaji
     * 
     * @param string $name Jméno autora
     * @param string|null $email E-mail autora
     * @param string|null $website Webová stránka autora
     * @return Author
     */
    public function createWithContact(string $name, ?string $email = null, ?string $website = null): Author
    {
        return $this->create([
            'name' => $name,
            'email' => $email,
            'website' => $website
        ]);
    }
}