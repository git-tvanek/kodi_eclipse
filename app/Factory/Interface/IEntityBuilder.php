<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Rozhraní pro buildery entit
 * 
 * @template T of object
 */
interface IEntityBuilder
{
    /**
     * Vrátí aktuální data builderu
     * 
     * @return array
     */
    public function getData(): array;
    
    /**
     * Vytvoří entitu z aktuálních dat
     * 
     * @return T
     */
    public function build(): object;
}