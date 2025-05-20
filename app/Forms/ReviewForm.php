<?php

declare(strict_types=1);

namespace App\Forms;

use App\Entity\AddonReview;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class ReviewForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /** @var bool */
    private bool $userLoggedIn;
    
    /** @var int|null */
    private ?int $userId;
    
    /**
     * Konstruktor
     * 
     * @param bool $userLoggedIn Je uživatel přihlášen
     * @param int|null $userId ID přihlášeného uživatele
     */
    public function __construct(bool $userLoggedIn = false, ?int $userId = null)
    {
        $this->userLoggedIn = $userLoggedIn;
        $this->userId = $userId;
    }
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param int $addonId ID doplňku, ke kterému se váže recenze
     * @param AddonReview|null $review Existující recenze pro režim úpravy
     * @return Form
     */
    public function create(int $addonId, ?AddonReview $review = null): Form
    {
        $form = new Form();
        
        // ID doplňku
        $form->addHidden('addon_id', (string)$addonId);
        
        // Údaje pro nepřihlášené uživatele
        if (!$this->userLoggedIn) {
            $form->addText('name', 'Vaše jméno:')
                ->setRequired('Prosím zadejte své jméno.');
                
            $form->addEmail('email', 'Váš e-mail:')
                ->setRequired(false)
                ->addRule(Form::Email, 'Prosím zadejte platnou e-mailovou adresu.');
        } else {
            // Přidat ID uživatele pro přihlášené uživatele
            $form->addHidden('user_id', (string)$this->userId);
        }
        
        // Hodnocení
        $form->addRadioList('rating', 'Hodnocení:', [
            5 => '5 hvězdiček',
            4 => '4 hvězdičky',
            3 => '3 hvězdičky',
            2 => '2 hvězdičky',
            1 => '1 hvězdička'
        ])->setRequired('Prosím vyberte hodnocení.');
        
        // Komentář
        $form->addTextArea('comment', 'Komentář:')
            ->setRequired(false)
            ->setHtmlAttribute('rows', 5);
            
        // ID pro režim úpravy
        if ($review) {
            $form->addHidden('id', (string)$review->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $review ? 'Upravit recenzi' : 'Přidat recenzi');
        
        // Předvyplnění formuláře při úpravách
        if ($review) {
            $defaults = $review->toArray();
            
            // Převést objekty DateTime na řetězce
            if (isset($defaults['created_at']) && $defaults['created_at'] instanceof \DateTime) {
                $defaults['created_at'] = $defaults['created_at']->format('Y-m-d H:i:s');
            }
            
            $form->setDefaults($defaults);
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($review) {
            $this->processForm($form, $values, $review);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param AddonReview|null $review
     */
    private function processForm(Form $form, ArrayHash $values, ?AddonReview $review): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $review);
        }
    }
}