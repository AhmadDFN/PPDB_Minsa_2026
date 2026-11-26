<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $argv[1] ?? $root.'/database/data/madiun-campus-map/source.osm';
$outputPath = $argv[2] ?? $root.'/public/maps/min1-madiun-campus.json';

if (! is_file($sourcePath)) {
    fwrite(STDERR, "Sumber OSM tidak ditemukan: {$sourcePath}\n");
    exit(1);
}

$xml = new DOMDocument;
$xml->load($sourcePath, LIBXML_NONET | LIBXML_NOBLANKS);
$xpath = new DOMXPath($xml);
$nodes = [];
foreach ($xpath->query('/osm/node') as $node) {
    $nodes[$node->getAttribute('id')] = [
        round((float) $node->getAttribute('lon'), 6),
        round((float) $node->getAttribute('lat'), 6),
    ];
}

$features = [];
foreach ($xpath->query('/osm/way') as $way) {
    $tags = [];
    foreach ($xpath->query('./tag', $way) as $tag) {
        $tags[$tag->getAttribute('k')] = $tag->getAttribute('v');
    }

    $type = isset($tags['highway']) ? 'road' : (isset($tags['building']) ? 'building' : null);
    if ($type === null) {
        continue;
    }

    $coordinates = [];
    foreach ($xpath->query('./nd', $way) as $reference) {
        $coordinate = $nodes[$reference->getAttribute('ref')] ?? null;
        if ($coordinate !== null) {
            $coordinates[] = $coordinate;
        }
    }

    if (($type === 'road' && count($coordinates) < 2) || ($type === 'building' && count($coordinates) < 4)) {
        continue;
    }

    $features[] = [
        'type' => $type,
        'class' => $type === 'road' ? $tags['highway'] : 'building',
        'name' => $tags['name'] ?? null,
        'coordinates' => $coordinates,
    ];
}

$output = [
    'bounds' => [
        'west' => 111.510,
        'south' => -7.665,
        'east' => 111.519,
        'north' => -7.653,
    ],
    'features' => $features,
];

$directory = dirname($outputPath);
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    fwrite(STDERR, "Direktori keluaran tidak dapat dibuat.\n");
    exit(1);
}

$json = json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
if (file_put_contents($outputPath, $json) === false) {
    fwrite(STDERR, "Peta offline tidak dapat ditulis.\n");
    exit(1);
}

printf("%d fitur ditulis ke %s (%d byte).\n", count($features), $outputPath, strlen($json));
