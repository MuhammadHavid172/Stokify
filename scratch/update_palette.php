<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/../resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.blade\.php$/');
$count = 0;

$replacements = [
    'from-[#F97316] to-[#EA580C]' => 'from-[#235347] to-[#0B2B26]',
    'hover:from-[#EA580C] hover:to-[#C2410C]' => 'hover:from-[#163832] hover:to-[#051F20]',
    'shadow-orange-500/20' => 'shadow-[#235347]/20',
    'focus:border-[#F97316]' => 'focus:border-[#235347]',
    'focus:ring-[#F97316]' => 'focus:ring-[#235347]',
    'text-[#F97316]' => 'text-[#235347]',
    'border-[#F97316]' => 'border-[#235347]',
    'bg-[#F97316]' => 'bg-[#235347]',
    'bg-orange-50' => 'bg-[#DAF1DE]/40',
    'border-orange-200' => 'border-[#8EB69B]/30',
    'border-orange-100' => 'border-[#8EB69B]/20',
    'hover:text-[#EA580C]' => 'hover:text-[#163832]',
    'text-[#EA580C]' => 'text-[#163832]',
    'bg-orange-100' => 'bg-[#DAF1DE]/50',
    'bg-[#2A231C]' => 'bg-[#0B2B26]',
    'bg-[#251D17]' => 'bg-[#0B2B26]',
];

foreach ($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $updated = str_replace(array_keys($replacements), array_values($replacements), $content);
    if ($updated !== $content) {
        file_put_contents($path, $updated);
        $count++;
        echo "Updated: " . basename($path) . "\n";
    }
}

echo "Total updated: $count files\n";
