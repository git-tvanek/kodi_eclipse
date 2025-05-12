<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Category;
use App\Factory\Interface\ICategoryFactory;
use Nette\Utils\Strings;

/**
 * Továrna pro vytváření kategorií
 * 
 * @implements IFactory<Category>
 */
class CategoryFactory implements ICategoryFactory
{
    /**
     * Vytvoří novou instanci kategorie
     * 
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        // Zajištění povinných polí
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Category name is required');
        }

        // Automatické vytvoření slugu
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }

        // Výchozí hodnoty pro nepovinná pole
        $data['parent_id'] = $data['parent_id'] ?? null;
        
        return Category::fromArray($data);
    }

    /**
     * Vytvoří kopii existující kategorie
     * 
     * @param Category $category Existující kategorie
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Category
     */
    public function createFromExisting(Category $category, array $overrideData = [], bool $createNew = true): Category
    {
        $data = $category->toArray();
        
        // Přepsat data novými hodnotami
        foreach ($overrideData as $key => $value) {
            $data[$key] = $value;
        }
        
        // Pokud byl změněn název a není explicitně uveden slug, vygenerovat nový
        if (isset($overrideData['name']) && !isset($overrideData['slug'])) {
            $data['slug'] = Strings::webalize($overrideData['name']);
        }
        
        // Při vytváření nové instance odstranit ID
        if ($createNew) {
            unset($data['id']);
        }
        
        return Category::fromArray($data);
    }

    /**
     * Vytvoří kořenovou kategorii
     * 
     * @param string $name Název kategorie
     * @param string|null $slug Slug kategorie (volitelný)
     * @return Category
     */
    public function createRoot(string $name, ?string $slug = null): Category
    {
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => null
        ]);
    }

    /**
     * Vytvoří podkategorii
     * 
     * @param string $name Název kategorie
     * @param int $parentId ID nadřazené kategorie
     * @param string|null $slug Slug kategorie (volitelný)
     * @return Category
     */
    public function createSubcategory(string $name, int $parentId, ?string $slug = null): Category
    {
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId
        ]);
    }
}