<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format tanggal ke bahasa Indonesia (d F Y)
     * Contoh: 16 Mei 2026
     */
    public static function formatDateID($date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->translatedFormat('d F Y');
    }

    /**
     * Format tanggal dan waktu ke bahasa Indonesia (d F Y, H:i)
     * Contoh: 16 Mei 2026, 10:30
     */
    public static function formatDateTimeID($date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->translatedFormat('d F Y, H:i');
    }

    /**
     * Format hari dan tanggal ke bahasa Indonesia (l, d F Y)
     * Contoh: Sabtu, 16 Mei 2026
     */
    public static function formatFullDateID($date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->translatedFormat('l, d F Y');
    }

    /**
     * Format hari dan tanggal + waktu ke bahasa Indonesia (l, d F Y H:i)
     * Contoh: Sabtu, 16 Mei 2026 10:30
     */
    public static function formatFullDateTimeID($date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->translatedFormat('l, d F Y H:i');
    }

    /**
     * Format waktu saja (H:i)
     * Contoh: 10:30
     */
    public static function formatTimeID($date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->format('H:i');
    }
}
