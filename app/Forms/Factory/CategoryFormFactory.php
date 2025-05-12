<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\CategoryForm;
use App\Service\ICategoryService;

class CategoryFormFactory
{
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /**
     * Konstruktor
     * 
     * @param ICategoryService $categoryService
     */
    public function __construct(ICategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    
    /**
     * Vytvoří instanci formuláře
     * 
     * @return CategoryForm
     */
    public function create(): CategoryForm
    {
        return new CategoryForm($this->categoryService);
    }
}