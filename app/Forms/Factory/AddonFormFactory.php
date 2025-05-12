<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\AddonForm;
use App\Service\ICategoryService;
use App\Service\IAuthorService;
use App\Service\ITagService;

class AddonFormFactory
{
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var IAuthorService */
    private IAuthorService $authorService;
    
    /** @var ITagService */
    private ITagService $tagService;
    
    /**
     * Konstruktor
     * 
     * @param ICategoryService $categoryService
     * @param IAuthorService $authorService
     * @param ITagService $tagService
     */
    public function __construct(
        ICategoryService $categoryService,
        IAuthorService $authorService,
        ITagService $tagService
    ) {
        $this->categoryService = $categoryService;
        $this->authorService = $authorService;
        $this->tagService = $tagService;
    }
    
    /**
     * Vytvoří instanci formuláře
     * 
     * @return AddonForm
     */
    public function create(): AddonForm
    {
        return new AddonForm(
            $this->categoryService,
            $this->authorService,
            $this->tagService
        );
    }
}