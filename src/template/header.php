<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../lib/functions.php';

// Menentukan base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$base_path = ($script_dir == '/') ? '/' : rtrim($script_dir, '/') . '/';
$base_url = $protocol . $host . $base_path;

?>

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitizeInput($page_title) . ' - Sistem Inventaris' : 'Sistem Inventaris'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style type="text/tailwindcss">
        .modal {
            display: none;
        }
        .modal.active {
            display: flex;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800 font-sans"> <?php // MODIFIKASI: Tambahkan class "flex flex-col min-h-screen" ?>

    <?php include_once __DIR__ . '/navigation.php'; // Memasukkan navigasi utama ?>
    <main class="flex-grow container mx-auto px-4 py-6"> <?php // MODIFIKASI: Tambahkan class "flex-grow" ?>
        <?php
            // Tempat untuk menampilkan flash messages
            displayFlashMessages(); // Fungsi ini ada di src/lib/functions.php
        ?>

</body>
</html>