<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\Author;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class AuthorForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Author|null $author Existující autor pro režim úpravy
     * @return Form
     */
    public function create(?Author $author = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Jméno autora:')
            ->setRequired('Prosím zadejte jméno autora.')
            ->addRule(Form::MaxLength, 'Jméno může být maximálně %d znaků dlouhé.', 100);
            
        $form->addEmail('email', 'E-mail:')
            ->setRequired(false)
            ->addRule(Form::Email, 'Prosím zadejte platnou e-mailovou adresu.');
            
        $form->addText('website', 'Webová stránka:')
            ->setRequired(false)
            ->addRule(Form::URL, 'Prosím zadejte platnou URL adresu.');
            
        // ID pro režim úpravy
        if ($author) {
            $form->addHidden('id', (string)$author->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $author ? 'Uložit změny' : 'Přidat autora');
        
        // Předvyplnění formuláře při úpravách
        if ($author) {
            $defaults = $author->toArray();
            
            // Převést objekty DateTime na řetězce
            if (isset($defaults['created_at']) && $defaults['created_at'] instanceof \DateTime) {
                $defaults['created_at'] = $defaults['created_at']->format('Y-m-d H:i:s');
            }
            
            $form->setDefaults($defaults);
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($author) {
            $this->processForm($form, $values, $author);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Author|null $author
     */
    private function processForm(Form $form, ArrayHash $values, ?Author $author): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $author);
        }
    }
}