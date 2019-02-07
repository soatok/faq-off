<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use ParagonIE\EasyDB\EasyDB;

/**
 * Class Entity
 * @package Soatok\FaqOff
 */
abstract class Entity
{
    /** @var EasyDB $db */
    protected $db;

    public function __construct(EasyDB $db)
    {
        $this->db = $db;
    }
}
