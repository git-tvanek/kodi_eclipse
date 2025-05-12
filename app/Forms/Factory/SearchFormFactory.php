<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\SearchForm;
use App\Service\ICategoryService;
use App\Service\ITagService;

class SearchFormFactory
{
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var ITagService */
    private ITagService $tagService;
    
    /**
     * Konstruktor
     * 
     * @param ICategoryService $categoryService
     * @param ITagService $tagService
     */
    public function __construct(
        ICategoryService $categoryService,
        ITagService $tagService
    ) {
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }
    
    /**
     * Vytvoří instanci formuláře
     * 
     * @return SearchForm
     */
    public function create(): SearchForm
    {
        return new SearchForm($this->categoryService, $this->tagService);
    }
}