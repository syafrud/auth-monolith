<?php

namespace Modules\Shared\Contracts;

interface BookCatalogInterface
{
    /**
     * @return array{id: int, title: string, isbn: string, stock: int}|null
     */
    public function getBookForLoan(int $bookId): ?array;

    /**
     * @return list<array{id: int, title: string, stock: int}>
     */
    public function getAvailableBooks(): array;

    public function decreaseStock(int $bookId, int $quantity): bool;

    public function increaseStock(int $bookId, int $quantity): void;
}
