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

    /**
     * Will calculate the page selection possible for the users.
     */
    public static function getPageSelection(int $resultsPerPage, int $nbrOfResults, int $currentPage)
    {
        $totalPages = ceil($nbrOfResults / $resultsPerPage);
        $maxDisplayed = 9; //preferably an odd number just for aesthetics purposes. (symetry)
        $half = (int)($maxDisplayed / 2);

        $start = max(1, $currentPage - $half);
        $end = $start + $maxDisplayed - 1;

        if ($end > $totalPages) {
            $end = $totalPages;
            $start = max(1, $end - $maxDisplayed + 1);
        }

        $pages = [];
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }
}
