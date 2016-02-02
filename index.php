<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */

use Colorpalettes\GimpPalette;
use Symfony\Component\HttpFoundation\Response;

$app = require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Index page
 */
$app->get('/', function () use ($app) {

    $pals = [];
    $palFiles = glob(
        'temp_pals' .
        DIRECTORY_SEPARATOR .
        '*.gpl');

    foreach ($palFiles as $currentFile) {
        $palObj = new GimpPalette($currentFile);
        if ($palObj->getColumns() == 1) {
            $palObj->setColumns(16);
        }
        $pals[] = $palObj;
    }

    return $app->render('index.html.twig', [
        'palettes' => $pals
    ]);
});

/**
 * Test page
 */
$app->get('/export/{paletteFile}', function ($paletteFile) use ($app) {
    $paletteFile = filter_var($paletteFile, FILTER_SANITIZE_STRING);
    $palettePath = 'temp_pals' . DIRECTORY_SEPARATOR . $paletteFile . '.gpl';

    if (!file_exists($palettePath)) {
        return new Response('Palette file: ' . $paletteFile . ' not found!');
    }

    $pal = new GimpPalette($palettePath);
    $pal->setColumns(16);

    return new Response("<pre>" . $pal->getExportContents() . "</pre>");
});

$app->run();