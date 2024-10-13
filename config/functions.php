<?php
// Memulai session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Menghasilkan token unik
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Mengubah timestamp menjadi format hari, tanggal, bulan, tahun (contoh: Rabu, 2 Juli 2025)
function formatTimestamp($timestamp) {
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $dayName = $days[date('w', $timestamp)];
    
    $formattedDate = $dayName . ', ' . date('j', $timestamp) . ' ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
    
    return $formattedDate;
}

startSession();

// Contoh penggunaan fungsi-fungsi di atas:

// Memulai sesi
// startSession();

// Menghasilkan token
// $token = generateToken();
// echo "Token: " . $token . "<br>";

// Mengubah timestamp ke format yang diinginkan
// $timestamp = strtotime('2025-07-02'); // Contoh timestamp
// $formattedDate = formatTimestamp($timestamp);
// echo "Formatted Date: " . $formattedDate;
