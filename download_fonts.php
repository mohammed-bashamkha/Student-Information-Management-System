<?php
$fonts = [
    'Amiri' => 'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap',
    'Cairo' => 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap'
];

$fontDir = __DIR__ . '/public/fonts';
if (!is_dir($fontDir)) {
    mkdir($fontDir, 0777, true);
}

$cssContent = "";

foreach ($fonts as $name => $url) {
    echo "Fetching $name CSS...\n";
    $opts = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $css = file_get_contents($url, false, $context);

    preg_match_all("/url\((https:\/\/[^)]+\.woff2)\)/", $css, $matches);
    $urls = array_unique($matches[1]);

    foreach ($urls as $index => $fontUrl) {
        echo "  Downloading font file from $fontUrl...\n";
        $fontContent = file_get_contents($fontUrl, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        $base64 = base64_encode($fontContent);
        $css = str_replace($fontUrl, 'data:font/woff2;charset=utf-8;base64,' . $base64, $css);
    }

    $cssContent .= $css . "\n";
}

file_put_contents($fontDir . '/pdf-fonts.css', $cssContent);
echo "Completed creating base64 pdf-fonts.css!\n";
