<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Základní rozhraní pro všechny továrny
 * 
 * @template T
 */
interface IFactory
{
    /**
     * Vytvoří novou instanci entity
     * 
     * @param array $data
     * @return T
     */
    public function create(array $data): object;
}