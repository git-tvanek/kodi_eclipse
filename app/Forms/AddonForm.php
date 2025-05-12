<?php

declare(strict_types=1);

namespace App\Forms;

use App\Service\ICategoryService;
use App\Service\IAuthorService;
use App\Service\ITagService;
use App\Model\Addon;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class AddonForm
{
    use SmartObject;
    
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var IAuthorService */
    private IAuthorService $authorService;
    
    /** @var ITagService */
    private ITagService $tagService;
    
    /** @var callable */
    public $onSuccess;
    
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
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Addon|null $addon Existující doplněk pro režim úpravy
     * @return Form
     */
    public function create(?Addon $addon = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Název doplňku:')
            ->setRequired('Prosím zadejte název doplňku.')
            ->addRule(Form::MaxLength, 'Název může být maximálně %d znaků dlouhý.', 100);
            
        $form->addText('slug', 'URL slug:')
            ->setRequired(false)
            ->addRule(Form::Pattern, 'Slug může obsahovat pouze malá písmena, čísla a pomlčky.', '[a-z0-9-]+')
            ->addRule(Form::MaxLength, 'Slug může být maximálně %d znaků dlouhý.', 100)
            ->setOption('description', 'Ponechte prázdné pro automatické vygenerování.');
            
        $form->addTextArea('description', 'Popis:')
            ->setRequired(false)
            ->setHtmlAttribute('rows', 10);
            
        $form->addText('version', 'Verze:')
            ->setRequired('Prosím zadejte verzi doplňku.')
            ->addRule(Form::MaxLength, 'Verze může být maximálně %d znaků dlouhá.', 20);
            
        // URLs
        $form->addText('repository_url', 'URL repozitáře:')
            ->setRequired(false)
            ->addRule(Form::URL, 'Prosím zadejte platnou URL adresu.')
            ->setOption('description', 'Např. GitHub repozitář.');
            
        $form->addText('download_url', 'URL pro stažení:')
            ->setRequired('Prosím zadejte URL pro stažení doplňku.')
            ->addRule(Form::URL, 'Prosím zadejte platnou URL adresu.');
            
        // Kompatibilita verzí
        $form->addText('kodi_version_min', 'Minimální verze Kodi:')
            ->setRequired(false)
            ->addRule(Form::Pattern, 'Verze musí být ve formátu X.Y.Z', '\d+(\.\d+)*');
            
        $form->addText('kodi_version_max', 'Maximální verze Kodi:')
            ->setRequired(false)
            ->addRule(Form::Pattern, 'Verze musí být ve formátu X.Y.Z', '\d+(\.\d+)*');
            
        // Vztahy
        $categories = $this->categoryService->findAll();
        $categoryPairs = [];
        foreach ($categories as $category) {
            $categoryPairs[$category->id] = $category->name;
        }
        
        $form->addSelect('category_id', 'Kategorie:', $categoryPairs)
            ->setRequired('Prosím vyberte kategorii.');
            
        $authors = $this->authorService->findAll();
        $authorPairs = [];
        foreach ($authors as $author) {
            $authorPairs[$author->id] = $author->name;
        }
        
        $form->addSelect('author_id', 'Autor:', $authorPairs)
            ->setRequired('Prosím vyberte autora.');
            
        // Tagy - použití multiselect
        $tags = $this->tagService->getTagsWithCounts();
        $tagPairs = [];
        foreach ($tags as $tag) {
            $tagPairs[$tag['id']] = $tag['name'];
        }
        
        $form->addMultiSelect('tag_ids', 'Tagy:', $tagPairs)
            ->setRequired(false)
            ->setOption('description', 'Vyberte existující tagy nebo přidejte nové níže.');
            
        $form->addText('new_tags', 'Nové tagy:')
            ->setRequired(false)
            ->setOption('description', 'Oddělte tagy čárkou.');
            
        // Multimediální soubory
        $form->addUpload('icon', 'Ikona:')
            ->setRequired(false)
            ->addRule(Form::Image, 'Ikona musí být obrázek ve formátu JPEG, PNG nebo GIF.')
            ->setOption('description', 'Doporučená velikost: 256x256 pixelů.');
            
        $form->addUpload('fanart', 'Fanart:')
            ->setRequired(false)
            ->addRule(Form::Image, 'Fanart musí být obrázek ve formátu JPEG, PNG nebo GIF.')
            ->setOption('description', 'Doporučená velikost: 1280x720 pixelů.');
            
        // Screenshoty - vícenásobný upload
        $form->addMultiUpload('screenshots', 'Screenshoty:')
            ->setRequired(false)
            ->addRule(Form::Image, 'Screenshoty musí být obrázky ve formátu JPEG, PNG nebo GIF.');
            
        // ID pro režim úpravy
        if ($addon) {
            $form->addHidden('id', (string)$addon->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $addon ? 'Uložit změny' : 'Přidat doplněk');
        
        // Předvyplnění formuláře při úpravách
        if ($addon) {
            $defaults = $addon->toArray();
            
            // Převést objekty DateTime na řetězce
            if (isset($defaults['created_at']) && $defaults['created_at'] instanceof \DateTime) {
                $defaults['created_at'] = $defaults['created_at']->format('Y-m-d H:i:s');
            }
            if (isset($defaults['updated_at']) && $defaults['updated_at'] instanceof \DateTime) {
                $defaults['updated_at'] = $defaults['updated_at']->format('Y-m-d H:i:s');
            }
            
            // Získat ID tagů pro předvyplnění
            $addonTags = $this->tagService->findByAddon($addon->id);
            $defaults['tag_ids'] = array_map(function($tag) {
                return $tag->id;
            }, $addonTags->toArray());
            
            $form->setDefaults($defaults);
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($addon) {
            $this->processForm($form, $values, $addon);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Addon|null $addon
     */
    private function processForm(Form $form, ArrayHash $values, ?Addon $addon): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Zpracování tagů - zpracování existujících a nových tagů
        $tagIds = $data['tag_ids'] ?? [];
        
        // Zpracování nových tagů, pokud byly zadány
        if (!empty($data['new_tags'])) {
            $newTagNames = explode(',', $data['new_tags']);
            foreach ($newTagNames as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tagIds[] = $this->tagService->findOrCreate($tagName);
                }
            }
        }
        
        // Odstranění polí specifických pro formulář
        unset($data['new_tags']);
        
        // Zpracování souborů
        $uploads = [
            'icon' => $values->icon,
            'fanart' => $values->fanart,
            'screenshots' => $values->screenshots ?? []
        ];
        
        // Odstranění polí pro upload ze vstupních dat
        unset($data['icon']);
        unset($data['fanart']);
        unset($data['screenshots']);
        
        // Příprava dat pro fasádu
        $data['tag_ids'] = $tagIds;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $uploads, $addon);
        }
    }
}