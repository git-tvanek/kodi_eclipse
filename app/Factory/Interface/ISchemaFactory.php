<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Rozšíření pro továrny s validačním schématem
 * 
 * @template T of object
 * @extends IBaseFactory<T>
 */
interface ISchemaFactory extends IBaseFactory
{
    /**
     * Validuje a normalizuje data podle schématu
     * 
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Pokud validace selže
     */
    public function validateSchema(array $data): array;
}