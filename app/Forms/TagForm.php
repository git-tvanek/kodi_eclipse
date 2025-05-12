<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\Tag;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class TagForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Tag|null $tag Existující tag pro režim úpravy
     * @return Form
     */
    public function create(?Tag $tag = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Název tagu:')
            ->setRequired('Prosím zadejte název tagu.')
            ->addRule(Form::MaxLength, 'Název může být maximálně %d znaků dlouhý.', 50);
            
        $form->addText('slug', 'URL slug:')
            ->setRequired(false)
            ->addRule(Form::Pattern, 'Slug může obsahovat pouze malá písmena, čísla a pomlčky.', '[a-z0-9-]+')
            ->addRule(Form::MaxLength, 'Slug může být maximálně %d znaků dlouhý.', 50)
            ->setOption('description', 'Ponechte prázdné pro automatické vygenerování.');
            
        // ID pro režim úpravy
        if ($tag) {
            $form->addHidden('id', (string)$tag->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $tag ? 'Uložit změny' : 'Přidat tag');
        
        // Předvyplnění formuláře při úpravách
        if ($tag) {
            $form->setDefaults($tag->toArray());
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($tag) {
            $this->processForm($form, $values, $tag);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Tag|null $tag
     */
    private function processForm(Form $form, ArrayHash $values, ?Tag $tag): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $tag);
        }
    }
}