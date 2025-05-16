<?php

declare(strict_types=1);

namespace App\Repository\Query;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use App\Entity\Addon;
use App\Collection\AddonCollection;
use App\Collection\PaginatedCollection;

/**
 * Dotazovací objekt pro pokročilé vyhledávání doplňků
 */
class AddonAdvancedSearchQuery
{
    private Explorer $database;
    private string $tableName = 'addons';
    private string $query;
    private array $fields;
    private array $filters;
    private int $page;
    private int $itemsPerPage;
    
    public function __construct(
        Explorer $database, 
        string $query,
        array $fields = ['name', 'description'],
        array $filters = [],
        int $page = 1,
        int $itemsPerPage = 10
    ) {
        $this->database = $database;
        $this->query = trim($query);
        $this->fields = $fields;
        $this->filters = $filters;
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
    }
    
    /**
     * Provede vyhledávání a vrátí výsledky
     * 
     * @return PaginatedCollection<Addon>
     */
    public function execute(): PaginatedCollection
    {
        if (empty($this->query)) {
            // Použijeme standardní filtrování pokud není dotaz
            return $this->executeSimpleFiltering();
        }
        
        $selection = $this->database->table($this->tableName);
        $keywords = preg_split('/\s+/', $this->query);
        
        // Vytvoření podmínek vyhledávání
        $conditions = [];
        $params = [];
        
        foreach ($this->fields as $field) {
            foreach ($keywords as $keyword) {
                $conditions[] = "$field LIKE ?";
                $params[] = "%{$keyword}%";
            }
        }
        
        if (!empty($conditions)) {
            $selection->where(implode(' OR ', $conditions), ...$params);
        }
        
        // Aplikovat další filtry
        $selection = $this->applyFilters($selection);
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $this->itemsPerPage);
        
        // Aplikovat stránkování
        $selection->limit($this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
        
        // Převést na entity a vypočítat relevanci
        $items = [];
        foreach ($selection as $row) {
            $addon = Addon::fromArray($row->toArray());
            
            // Výpočet jednoduchého skóre relevance
            $relevance = 0;
            foreach ($keywords as $keyword) {
                foreach ($this->fields as $field) {
                    $value = $addon->{$field} ?? '';
                    if (is_string($value) && stripos($value, $keyword) !== false) {
                        $relevance += 1;
                        // Zvýšit skóre pro přesné shody v názvu
                        if ($field === 'name' && stripos($value, $keyword) === 0) {
                            $relevance += 2;
                        }
                    }
                }
            }
            
            $items[] = [
                'addon' => $addon,
                'relevance' => $relevance
            ];
        }
        
        // Seřadit podle relevance
        usort($items, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        // Extrahovat jen doplňky z řazených položek
        $addons = array_map(function($item) {
            return $item['addon'];
        }, $items);
        
        // Vytvořit typovanou kolekci s výsledky
        $collection = new AddonCollection($addons);
        
        // Zabalit do stránkované kolekce
        return new PaginatedCollection(
            $collection,
            $count,
            $this->page,
            $this->itemsPerPage,
            $pages
        );
    }
    
    /**
     * Provede jednoduché filtrování bez hledání klíčových slov
     * 
     * @return PaginatedCollection<Addon>
     */
    private function executeSimpleFiltering(): PaginatedCollection
    {
        $selection = $this->database->table($this->tableName);
        $selection = $this->applyFilters($selection);
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $this->itemsPerPage);
        
        // Aplikovat stránkování
        $selection->limit($this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
        
        // Převést na entity
        $addons = [];
        foreach ($selection as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        // Vytvořit typovanou kolekci
        $collection = new AddonCollection($addons);
        
        // Zabalit do stránkované kolekce
        return new PaginatedCollection(
            $collection,
            $count,
            $this->page,
            $this->itemsPerPage,
            $pages
        );
    }
    
    /**
     * Aplikuje filtry na dotaz
     * 
     * @param Selection $selection
     * @return Selection
     */
    private function applyFilters(Selection $selection): Selection
    {
        foreach ($this->filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'category_ids':
                    if (is_array($value) && !empty($value)) {
                        $selection->where('category_id IN ?', $value);
                    } elseif (!is_array($value) && $value) {
                        $selection->where('category_id', $value);
                    }
                    break;
                    
                case 'author_ids':
                    if (is_array($value) && !empty($value)) {
                        $selection->where('author_id IN ?', $value);
                    } elseif (!is_array($value) && $value) {
                        $selection->where('author_id', $value);
                    }
                    break;
                
                case 'tag_ids':
                    if (is_array($value) && !empty($value)) {
                        $selection->where('id IN ?', 
                            $this->database->table('addon_tags')
                                ->where('tag_id IN ?', $value)
                                ->select('addon_id')
                        );
                    }
                    break;
                    
                case 'min_rating':
                    $selection->where('rating >= ?', $value);
                    break;
                    
                case 'max_rating':
                    $selection->where('rating <= ?', $value);
                    break;
                    
                default:
                    if (property_exists('App\Model\Addon', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        return $selection;
    }
}