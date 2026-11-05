<?php

namespace App\Support;

class CsvWriter
{
    /**
     * Prefix formula-like strings to prevent spreadsheet formula execution.
     *
     * @param  resource  $stream
     * @param  list<mixed>  $row
     */
    public static function write($stream, array $row): void
    {
        $safe = array_map(function ($value) {
            if (! is_string($value) || ! preg_match('/^\s*[=+\-@]/u', $value)) {
                return $value;
            }

            return "'".$value;
        }, $row);

        fputcsv($stream, $safe);
    }
}
