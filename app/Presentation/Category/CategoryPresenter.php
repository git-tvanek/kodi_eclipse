<?php

declare(strict_types=1);

namespace App\Presentation\Category;

use App\Presentation\BasePresenter;
use App\Facade\CategoryFacade;
use App\Facade\AddonFacade;
use App\Facade\TagFacade;
use App\Forms\Factory\CategoryFormFactory;
use Nette\Application\UI\Form;

class CategoryPresenter extends BasePresenter
{
    /** @var CategoryFacade */
    private CategoryFacade $categoryFacade;
    
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var TagFacade */
    private TagFacade $tagFacade;
    
    /** @var CategoryFormFactory */
    private CategoryFormFactory $categoryFormFactory;
    
    /**
     * Constructor
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        AddonFacade $addonFacade,
        TagFacade $tagFacade,
        CategoryFormFactory $categoryFormFactory
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->addonFacade = $addonFacade;
        $this->tagFacade = $tagFacade;
        $this->categoryFormFactory = $categoryFormFactory;
    }
    
    /**
     * Default action - list all categories
     */
    public function renderDefault(): void
    {
        // Get category hierarchy with statistics
        $categoryHierarchy = $this->categoryFacade->getCategoryHierarchyWithStats();
        
        // Get popular categories
        $popularCategories = $this->categoryFacade->getPopularCategories(5);
        
        $this->template->categoryHierarchy = $categoryHierarchy;
        $this->template->popularCategories = $popularCategories;
    }
    
    /**
     * Detail action - show a specific category
     */
    public function renderDetail(string $slug, int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get category by slug
        $category = $this->categoryFacade->getCategoryBySlug($slug);
        if (!$category) {
            $this->error('Category not found');
        }
        
        // Get subcategories
        $subcategories = $this->categoryFacade->getSubcategories($category->id);
        
        // Get path (breadcrumbs)
        $categoryPath = $this->categoryFacade->getCategoryPath($category->id);
        
        // Get addons in this category
        $addons = $this->addonFacade->searchAddons('', ['category_ids' => [$category->id]], $page, $itemsPerPage);
        
        // Get tag cloud for this category
        $tagCloud = $this->tagFacade->generateTagCloud(30, $category->id);
        
        $this->template->category = $category;
        $this->template->subcategories = $subcategories;
        $this->template->categoryPath = $categoryPath;
        $this->template->addons = $addons;
        $this->template->tagCloud = $tagCloud;
        $this->template->page = $page;
    }
    
    /**
     * Add action - form for adding a new category
     */
    public function renderAdd(): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to add a category', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        $this->template->title = 'Add New Category';
    }
    
    /**
     * Edit action - form for editing a category
     */
    public function renderEdit(int $id): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn()) {
        //     $this->flashMessage('You must be logged in to edit a category', 'danger');
        //     $this->redirect('Sign:in');
        // }
        
        // Get category by ID
        $category = $this->categoryFacade->getCategory($id);
        if (!$category) {
            $this->error('Category not found');
        }
        
        $this->template->category = $category;
        $this->template->title = 'Edit Category: ' . $category->name;
        
        // Set form defaults
        $this['categoryForm']->setDefaults($category->toArray());
    }

    /**
    * Delete action - remove a category
    */
    public function renderDelete(int $id): void
    {
    // Check if user is logged in (in a real app)
    // if (!$this->getUser()->isLoggedIn()) {
    //     $this->flashMessage('Pro smazání kategorie musíte být přihlášen', 'danger');
    //     $this->redirect('Sign:in');
    // }
    
        try {
            // Delete the category
            $result = $this->categoryFacade->deleteCategory($id);
        
            if ($result) {
                $this->flashMessage('Kategorie byla úspěšně smazána', 'success');
            } else {
                $this->flashMessage('Kategorii se nepodařilo smazat', 'danger');
            }
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při mazání kategorie: ' . $e->getMessage(), 'danger');
    }
    
    // Redirect to category list
    $this->redirect('Category:default');
    }
    
    /**
     * Component factory for the category form
     */
    protected function createComponentCategoryForm(): Form
    {
        $form = $this->categoryFormFactory->create();
        
        // Set callback for form processing
        $form->onSuccess[] = function (array $data) {
            $this->processCategoryForm($data);
        };
        
        return $form->create();
    }
    
    /**
     * Process category form
     */
    private function processCategoryForm(array $data): void
    {
        if (isset($data['id'])) {
            // Aktualizace existující kategorie
            $categoryId = $this->categoryFacade->updateCategory($data['id'], $data);
            $this->flashMessage('Category updated successfully', 'success');
            
            // Získání aktualizované kategorie
            $category = $this->categoryFacade->getCategory($categoryId);
            $this->redirect('Category:detail', $category->slug);
        } else {
            // Vytvoření nové kategorie
            // Extrakce jednotlivých parametrů z pole data
            $name = $data['name'];
            $parentId = $data['parent_id'] ?? null;
            $slug = $data['slug'] ?? null;
            
            $categoryId = $this->categoryFacade->createCategory($name, $parentId, $slug);
            $this->flashMessage('Category created successfully', 'success');
            
            // Získání vytvořené kategorie
            $category = $this->categoryFacade->getCategory($categoryId);
            $this->redirect('Category:detail', $category->slug);
        }
    }
}