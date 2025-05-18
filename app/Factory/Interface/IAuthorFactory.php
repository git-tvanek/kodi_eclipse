<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Author;

/**
 * Rozhraní pro továrnu autorů
 * 
 * @extends IFactory<Author>
 */
interface IAuthorFactory extends IFactory
{
    /**
     * Vytvoří novou instanci autora
     * 
     * @param array $data
     * @return Author
     */
    public function create(array $data): Author;
    
    /**
     * Vytvoří kopii existujícího autora
     * 
     * @param Author $author Existující autor
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Author
     */
    public function createFromExisting(Author $author, array $overrideData = [], bool $createNew = true): Author;
    
    /**
     * Vytvoří základního autora pouze s jménem
     * 
     * @param string $name Jméno autora
     * @return Author
     */
    public function createWithName(string $name): Author;
    
    /**
     * Vytvoří autora s kontaktními údaji
     * 
     * @param string $name Jméno autora
     * @param string|null $email E-mail autora
     * @param string|null $website Webová stránka autora
     * @return Author
     */
    public function createWithContact(string $name, ?string $email = null, ?string $website = null): Author;
}