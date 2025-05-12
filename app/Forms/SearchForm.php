<?php

declare(strict_types=1);

namespace App\Forms;

use App\Service\ICategoryService;
use App\Service\ITagService;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class SearchForm
{
    use SmartObject;
    
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var ITagService */
    private ITagService $tagService;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Konstruktor
     * 
     * @param ICategoryService $categoryService
     * @param ITagService $tagService
     */
    public function __construct(ICategoryService $categoryService, ITagService $tagService)
    {
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param array $defaults Výchozí hodnoty
     * @return Form
     */
    public function create(array $defaults = []): Form
    {
        $form = new Form();
        
        // Vyhledávací dotaz
        $form->addText('query', 'Vyhledávací dotaz:')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', 'Hledaný výraz...');
        
        // Kategorie
        $categories = $this->categoryService->findAll();
        $categoryPairs = [];
        foreach ($categories as $category) {
            $categoryPairs[$category->id] = $category->name;
        }
        
        $form->addMultiSelect('category_ids', 'Kategorie:', $categoryPairs)
            ->setRequired(false);
            
        // Tagy
        $tags = $this->tagService->getTagsWithCounts();
        $tagPairs = [];
        foreach ($tags as $tag) {
            $tagPairs[$tag['id']] = $tag['name'];
        }
        
        $form->addMultiSelect('tag_ids', 'Tagy:', $tagPairs)
            ->setRequired(false);
            
        // Hodnocení
        $form->addSelect('min_rating', 'Minimální hodnocení:', [
            null => '-- Bez omezení --',
            5 => '5 hvězdiček',
            4 => '4+ hvězdiček',
            3 => '3+ hvězdiček',
            2 => '2+ hvězdiček',
            1 => '1+ hvězdička',
            0 => '0 hvězdiček'  
        ])->setPrompt('-- Bez omezení --');
        
        // Verze Kodi
        $form->addText('kodi_version', 'Verze Kodi:')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', 'např. 19.4')
            ->addRule(Form::Pattern, 'Verze musí být ve formátu X.Y.Z', '\d+(\.\d+)*');
            
        // Řazení
        $form->addSelect('sort_by', 'Řadit podle:', [
            'name' => 'Názvu',
            'downloads_count' => 'Počtu stažení',
            'rating' => 'Hodnocení',
            'created_at' => 'Data přidání'
        ]);
        
        $form->addSelect('sort_dir', 'Směr řazení:', [
            'ASC' => 'Vzestupně',
            'DESC' => 'Sestupně'
        ]);
        
        // Ovládací prvky formuláře
        $form->addSubmit('search', 'Vyhledat');
        
        // Předvyplnění formuláře
        if (!empty($defaults)) {
        // Upravit neplatné hodnoty
            if (isset($defaults['min_rating']) && $defaults['min_rating'] === 0) {
                $defaults['min_rating'] = null; // Změnit 0 na null pro "Bez omezení"       
            }
        $form->setDefaults($defaults);

        }

        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) {
            $this->processForm($form, $values);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     */
    private function processForm(Form $form, ArrayHash $values): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data);
        }
    }
}