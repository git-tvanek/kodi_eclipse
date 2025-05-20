<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Rozšíření pro továrny podporující buildery
 * 
 * @template T of object
 * @template B of IEntityBuilder
 * @extends IBaseFactory<T>
 */
interface IBuilderFactory extends IBaseFactory
{
    /**
     * Vytvoří builder pro fluent rozhraní
     * 
     * @return B
     */
    public function createBuilder(): object;
}