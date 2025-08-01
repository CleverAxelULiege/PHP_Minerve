<?php

namespace App\Support;

/**
 * @template T
 */
class PaginatedResult
{
    /**
     * @var T[]
     */
    public array $data;

    public int $currentPage;
    public int $perPage;
    public int $total;
    public int $lastPage;

    /**
     * @param T[] $data
     */
    public function __construct(array $data, int $currentPage, int $perPage, int $total)
    {
        $this->data = $data;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->lastPage = (int) ceil($total / $perPage);
    }
}
