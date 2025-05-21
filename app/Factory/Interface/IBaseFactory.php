<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Základní rozhraní pro továrny entit
 * 
 * @template T
 */
interface IBaseFactory
{
    /**
     * Vytvoří novou entitu z pole dat
     * 
     * @param array $data
     * @return T
     */
    public function create(array $data);
    
    /**
     * Vytvoří novou entitu na základě existující entity
     * 
     * @param T $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return T
     */
    public function createFromExisting($entity, array $data, bool $isNew = true);

    /**
     * Vrátí název entity, kterou továrna vytváří
     * 
     * @return string
     */
    public function getEntityClass(): string;
}