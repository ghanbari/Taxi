<?php

$versions = $container->getParameter('api.version.available');

$mimeTypes = array(
    'json' => array(
        'application/json',
    ),
);

array_walk($mimeTypes, function (&$mimeTypes, $format, $versions) {
    $versionMimeTypes = array();
    foreach ($mimeTypes as $mimeType) {
        foreach ($versions as $version) {
            array_push($versionMimeTypes, sprintf('%s;version=%s', $mimeType, $version));
            array_push($versionMimeTypes, sprintf('%s;v=%s', $mimeType, $version));
        }
    }
    $mimeTypes = array_merge($mimeTypes, $versionMimeTypes);
}, $versions);

$container->loadFromExtension('fos_rest', array(
    'view' => array(
        'mime_types' => $mimeTypes,
    ),
));