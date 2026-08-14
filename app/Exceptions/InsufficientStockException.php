<?php

namespace App\Exceptions;

use App\Models\Product;
use Exception;

/**
 * Thrown by StockService when a sale or sample would take a product's stock
 * below zero. Carries the raw numbers so the controller can build a
 * translated, locale-aware message rather than a hardcoded one here.
 */
class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly Product $product,
        public readonly float $available,
        public readonly float $requested,
    ) {
        parent::__construct("Insufficient stock for product #{$product->id}: available {$available}, requested {$requested}.");
    }
}
