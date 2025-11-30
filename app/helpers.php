<?php

if (!function_exists('format_currency')) {
    /**
     * Format số tiền thành định dạng USD
     * 
     * @param float|int|string $amount
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, $decimals = 2)
    {
        return '$' . number_format((float)$amount, $decimals, '.', ',');
    }
}

