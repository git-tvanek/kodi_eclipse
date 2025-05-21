<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AddonRepository;
use App\Repository\AuthorRepository;
use App\Repository\TagRepository;
use App\Repository\CategoryRepository;
use App\Factory\Interface\IFactoryManager;

/**
 * Implementace služby pro vyhledávání
 * 
 * @implements ISearchService
 */
class SearchService implements ISearchService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /** @var TagRepository */
    private TagRepository $tagRepository;
    
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;
    
    /** @var IFactoryManager */
    private IFactoryManager $factoryManager;
    
    /**
     * Konstruktor
     * 
     * @param AddonRepository $addonRepository
     * @param AuthorRepository $authorRepository
     * @param TagRepository $tagRepository
     * @param CategoryRepository $categoryRepository
     * @param IFactoryManager $factoryManager
     */
    public function __construct(
        AddonRepository $addonRepository,
        AuthorRepository $authorRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        IFactoryManager $factoryManager
    ) {
        $this->addonRepository = $addonRepository;
        $this->authorRepository = $authorRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->factoryManager = $factoryManager;
    }
    
    /**
     * Provede jednoduché vyhledávání
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        // Vyhledávání doplňků
        $addons = $this->addonRepository->search($query, $page, $itemsPerPage);
        
        // Nalezení relevantních tagů
        $tags = $this->findRelevantTags($query, 5);
        
        // Nalezení relevantních autorů
        $authors = $this->findRelevantAuthors($query, 3);
        
        return [
            'query' => $query,
            'addons' => $addons,
            'tags' => $tags,
            'authors' => $authors
        ];
    }
    
    /**
     * Provede pokročilé vyhledávání
     * 
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function advancedSearch(
        string $query, 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): array {
        // Vyhledávání doplňků s pokročilým filtrováním
        $fields = ['name', 'description'];
        $addons = $this->addonRepository->advancedSearch($query, $fields, $filters, $page, $itemsPerPage);
        
        // Získání možných možností filtrů
        $filterOptions = $this->getFilterOptions();
        
        return [
            'query' => $query,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'addons' => $addons
        ];
    }
    
    /**
     * Najde relevantní tagy na základě dotazu
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function findRelevantTags(string $query, int $limit): array
    {
        // Základní implementace pro nalezení tagů podle částečné shody názvu
        $tags = $this->tagRepository->findWithFilters(
            ['name' => $query],
            'name',
            'ASC',
            1,
            $limit
        );
        
        return $tags->getItems()->toArray();
    }
    
    /**
     * Najde relevantní autory na základě dotazu
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function findRelevantAuthors(string $query, int $limit): array
    {
        // Základní implementace pro nalezení autorů podle částečné shody jména
        $authors = $this->authorRepository->findWithFilters(
            ['name' => $query],
            'name',
            'ASC',
            1,
            $limit
        );
        
        return $authors->getItems()->toArray();
    }
    
    /**
     * Získá možnosti filtrů pro pokročilé vyhledávání
     * 
     * @return array
     */
    private function getFilterOptions(): array
    {
        return [
            'categories' => $this->getCategoryOptions(),
            'tags' => $this->getTagOptions(),
            'ratings' => $this->getRatingOptions(),
            'sortOptions' => $this->getSortOptions()
        ];
    }
    
    /**
     * Získá možnosti kategorií pro filtry
     * 
     * @return array
     */
    private function getCategoryOptions(): array
    {
        $categories = [];
        $rows = $this->categoryRepository->findAll()->order('name ASC');
        
        foreach ($rows as $row) {
            $categories[] = [
                'id' => $row->getId(),
                'name' => $row->getName()
            ];
        }
        
        return $categories;
    }
    
    /**
     * Získá možnosti tagů pro filtry
     * 
     * @return array
     */
    private function getTagOptions(): array
    {
        $tagCounts = $this->tagRepository->getTagsWithCounts();
        return array_slice($tagCounts, 0, 20); // Vrátí 20 nejpoužívanějších tagů
    }
    
    /**
     * Získá možnosti hodnocení pro filtry
     * 
     * @return array
     */
    private function getRatingOptions(): array
    {
        return [
            ['value' => 5, 'label' => '5 hvězdiček'],
            ['value' => 4, 'label' => '4+ hvězdiček'],
            ['value' => 3, 'label' => '3+ hvězdiček'],
            ['value' => 2, 'label' => '2+ hvězdiček'],
            ['value' => 1, 'label' => '1+ hvězdiček']
        ];
    }
    
    /**
     * Získá možnosti řazení
     * 
     * @return array
     */
    private function getSortOptions(): array
    {
        return [
            ['field' => 'name', 'direction' => 'ASC', 'label' => 'Název (A-Z)'],
            ['field' => 'name', 'direction' => 'DESC', 'label' => 'Název (Z-A)'],
            ['field' => 'downloads_count', 'direction' => 'DESC', 'label' => 'Nejvíce stahované'],
            ['field' => 'rating', 'direction' => 'DESC', 'label' => 'Nejlépe hodnocené'],
            ['field' => 'created_at', 'direction' => 'DESC', 'label' => 'Nejnovější'],
            ['field' => 'created_at', 'direction' => 'ASC', 'label' => 'Nejstarší']
        ];
    }
}