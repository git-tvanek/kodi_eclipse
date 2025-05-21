<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\AddonReview;

/**
 * Rozhraní pro továrnu AddonReviewFactory
 * 
 * @template-extends IBaseFactory<AddonReview>
 */
interface IAddonReviewFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci recenze z pole dat
     * 
     * @param array $data Data pro vytvoření recenze
     * @return AddonReview Vytvořená instance
     */
    public function create(array $data): AddonReview;
    
    /**
     * Aktualizuje existující entitu recenze
     * 
     * @param AddonReview $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return AddonReview Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): AddonReview;
}