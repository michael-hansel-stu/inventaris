<?php

function sanitizeInput($input) {
    if ($input === null) {
        return null;
    }
    $cleaned_input = trim($input); // Menghapus spasi di awal dan akhir
    $cleaned_input = htmlspecialchars($cleaned_input, ENT_QUOTES, 'UTF-8'); 
    return $cleaned_input;
}

function sanitizeForSqlLike($conn, $input) {
    if ($input === null) {
        return null;
    }
    $cleaned_input = trim($input);
    // Escape karakter khusus untuk LIKE
    $escaped_input = str_replace(['%', '_'], ['\%', '\_'], $cleaned_input);
    // Kemudian escape untuk SQL secara umum
    return $conn->real_escape_string($escaped_input);
}

function formatRupiah($number, $include_rp_prefix = true) {
    if (!is_numeric($number)) {
        return 'N/A'; // Atau nilai default lain jika bukan angka
    }
    $prefix = $include_rp_prefix ? "Rp " : "";
    return $prefix . number_format($number, 0, ',', '.');
}

function redirect($url) {
    header("Location: " . $url);
    exit; // Menghentikan eksekusi skrip setelah redirect
}

function displayFlashMessages() {
    $output = '';
    // Pesan Sukses
    if (isset($_SESSION['message'])) {
        $output .= '<div id="flashMessageSuccess" class="relative mb-4 p-4 pr-10 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-md">';
        $output .= htmlspecialchars($_SESSION['message']);
        $output .= '<button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900" onclick="closeFlashMessage(\'flashMessageSuccess\')">';
        $output .= '<span aria-hidden="true">&times;</span>';
        $output .= '<span class="sr-only">Tutup pesan</span>';
        $output .= '</button>';
        $output .= '</div>';
        unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
    }
    // Pesan Error
    if (isset($_SESSION['error_message'])) {
        $output .= '<div id="flashMessageError" class="relative mb-4 p-4 pr-10 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-md">';
        $output .= htmlspecialchars($_SESSION['error_message']);
        $output .= '<button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-700 hover:text-red-900" onclick="closeFlashMessage(\'flashMessageError\')">';
        $output .= '<span aria-hidden="true">&times;</span>';
        $output .= '<span class="sr-only">Tutup pesan</span>';
        $output .= '</button>';
        $output .= '</div>';
        unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan
    }
    echo $output;
}

function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>