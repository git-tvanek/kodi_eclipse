<?php

declare(strict_types=1);

namespace App\Repository\Query;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use App\Model\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Dotazovací objekt pro filtrování doplňků
 */
class AddonFilterQuery
{
    private Explorer $database;
    private string $tableName = 'addons';
    private array $filters;
    private string $sortBy;
    private string $sortDir;
    private int $page;
    private int $itemsPerPage;
    
    public function __construct(
        Explorer $database, 
        array $filters = [],
        string $sortBy = 'name',
        string $sortDir = 'ASC',
        int $page = 1,
        int $itemsPerPage = 10
    ) {
        $this->database = $database;
        $this->filters = $filters;
        $this->sortBy = $sortBy;
        $this->sortDir = $sortDir;
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
    }
    
    /**
     * Provede filtrování a vrátí výsledky
     * 
     * @return PaginatedCollection<Addon>
     */
    public function execute(): PaginatedCollection
    {
        $selection = $this->database->table($this->tableName);
        
        // Aplikovat filtry
        foreach ($this->filters as $key => $value) {
            // Přeskočit prázdné filtry
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'description':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
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
                        // Poddotaz pro nalezení doplňků s určitými tagy
                        $selection->where('id IN ?', 
                            $this->database->table('addon_tags')
                                ->where('tag_id IN ?', $value)
                                ->select('addon_id')
                        );
                    } elseif (!is_array($value) && $value) {
                        $selection->where('id IN ?', 
                            $this->database->table('addon_tags')
                                ->where('tag_id', $value)
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
                    
                case 'min_downloads':
                    $selection->where('downloads_count >= ?', $value);
                    break;
                    
                case 'max_downloads':
                    $selection->where('downloads_count <= ?', $value);
                    break;
                    
                case 'kodi_version':
                    // Zpracování kompatibility s verzí Kodi
                    $selection->where('kodi_version_min <= ? AND (kodi_version_max >= ? OR kodi_version_max IS NULL)', $value, $value);
                    break;
                    
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at >= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                    
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at <= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                    
                default:
                    // Pro přímé shody polí (jako id, category_id atd.)
                    if (property_exists('App\Model\Addon', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $this->itemsPerPage);
        
        // Aplikovat řazení
        if (property_exists('App\Model\Addon', $this->sortBy)) {
            $selection->order("{$this->sortBy} {$this->sortDir}");
        } else {
            $selection->order("name ASC"); // Výchozí řazení
        }
        
        // Aplikovat stránkování
        $selection->limit($this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
        
        // Převést na entity
        $addons = [];
        foreach ($selection as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        // Vytvořit typovanou kolekci
        $collection = new Collection($addons);
        
        // Zabalit do stránkované kolekce
        return new PaginatedCollection(
            $collection,
            $count,
            $this->page,
            $this->itemsPerPage,
            $pages
        );
    }
}