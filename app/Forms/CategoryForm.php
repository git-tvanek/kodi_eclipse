<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\Category;
use App\Service\ICategoryService;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class CategoryForm
{
    use SmartObject;
    
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var callable */
    public $onSuccess;
    
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
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Category|null $category Existující kategorie pro režim úpravy
     * @return Form
     */
    public function create(?Category $category = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Název kategorie:')
            ->setRequired('Prosím zadejte název kategorie.')
            ->addRule(Form::MaxLength, 'Název může být maximálně %d znaků dlouhý.', 100);
            
        $form->addText('slug', 'URL slug:')
            ->setRequired(false)
            ->addRule(Form::Pattern, 'Slug může obsahovat pouze malá písmena, čísla a pomlčky.', '[a-z0-9-]+')
            ->addRule(Form::MaxLength, 'Slug může být maximálně %d znaků dlouhý.', 100)
            ->setOption('description', 'Ponechte prázdné pro automatické vygenerování.');
        $form->addTextArea('description', 'Popis kategorie:')
            ->setRequired(false)
            ->addRule(Form::MaxLength, 'Popis může být maximálně %d znaků dlouhý.', 5000);
            
        // Nadřazená kategorie
        $categories = $this->categoryService->findAll();
        $categoryPairs = [null => '-- Žádná (kořenová kategorie) --'];
        
        foreach ($categories as $cat) {
            // Nemůžeme vybrat sami sebe jako nadřazenou kategorii
            if ($category && $cat->id === $category->id) {
                continue;
            }
            $categoryPairs[$cat->id] = $cat->name;
        }
        
        $form->addSelect('parent_id', 'Nadřazená kategorie:', $categoryPairs)
            ->setPrompt('-- Žádná (kořenová kategorie) --')
            ->setRequired(false);
            
        // ID pro režim úpravy
        if ($category) {
            $form->addHidden('id', (string)$category->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $category ? 'Uložit změny' : 'Přidat kategorii');
        
        // Předvyplnění formuláře při úpravách
        if ($category) {
            $form->setDefaults($category->toArray());
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($category) {
            $this->processForm($form, $values, $category);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Category|null $category
     */
    private function processForm(Form $form, ArrayHash $values, ?Category $category): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $category);
        }
    }
}