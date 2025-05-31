<?php
$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    ['url' => 'products.php', 'label' => 'Produk'],
    ['url' => 'sales.php', 'label' => 'Penjualan'],
    ['url' => 'orders.php', 'label' => 'Pesanan Supplier'],
    ['url' => 'suppliers.php', 'label' => 'Supplier'],
    ['url' => 'reports.php', 'label' => 'Laporan'],
];

?>
<header class="bg-slate-800 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="<?php echo isset($base_url) ? $base_url . 'index.php' : 'index.php'; ?>" class="font-bold text-xl hover:text-slate-300 transition duration-150">
                    InventarisApp
                </a>
            </div>
            <ul class="flex space-x-4">
                <?php foreach ($nav_items as $item): ?>
                    <li>
                        <a href="<?php echo isset($base_url) ? $base_url . $item['url'] : $item['url']; ?>" 
                           class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-700 transition duration-150 <?php echo ($current_page == $item['url']) ? 'bg-slate-900' : ''; ?>">
                            <?php echo $item['label']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>