<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Rozšíření základního rozhraní továrny o podporu rozšíření
 * 
 * @template T of object
 * @extends IBaseFactory<T>
 */
interface IExtensionFactory extends IBaseFactory
{
    /**
     * Přidá rozšíření pro určitou fázi životního cyklu
     * 
     * @param string $name Název rozšíření
     * @param string $phase Fáze životního cyklu ('before_create', 'after_create', 'before_update', 'after_update')
     * @param callable $callback Callback funkce, která zpracuje data
     * @return self
     */
    public function addExtension(string $name, string $phase, callable $callback): self;
    
    /**
     * Odstraní rozšíření
     * 
     * @param string $name Název rozšíření k odstranění
     * @return self
     */
    public function removeExtension(string $name): self;
    
    /**
     * Zkontroluje, zda existuje rozšíření s daným názvem
     * 
     * @param string $name Název rozšíření
     * @return bool
     */
    public function hasExtension(string $name): bool;
    
    /**
     * Spustí všechna registrovaná rozšíření pro danou fázi
     * 
     * @param string $phase Fáze životního cyklu
     * @param mixed $data Data k zpracování
     * @return mixed Zpracovaná data
     */
    public function runExtensions(string $phase, $data);
}