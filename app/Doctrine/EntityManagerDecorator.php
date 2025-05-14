<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    public function __construct(EntityManagerInterface $wrapped)
    {
        parent::__construct($wrapped);
    }
}