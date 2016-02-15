<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */

use Colorpalettes\BasePalette,
    Colorpalettes\Importers\AdobeSwatchExchangeImporter,
    Colorpalettes\Importers\GimpPaletteImporter,
    Colorpalettes\Exporters\AdobeSwatchExchangeExporter,
    Colorpalettes\Exporters\GimpPaletteExporter,
    Symfony\Component\HttpFoundation\Response;

$app = require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * @var \Spot\Locator
 */
$spot = require_once __DIR__ . DIRECTORY_SEPARATOR . 'db_conf.php';

/**
 * Index page
 */
$app->get('/', function () use ($app, $spot) {
    $pals = [];
    $palFiles = glob(
        'temp_pals' .
        DIRECTORY_SEPARATOR .
        '*.gpl');

    foreach ($palFiles as $currentFile) {
        $palObj = new BasePalette();
        $palObj->import(new GimpPaletteImporter($currentFile));

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
 * Test pages
 */
$app->get('/import/gpl/{paletteFile}', function ($paletteFile) use ($app) {
    $paletteFile = filter_var($paletteFile, FILTER_SANITIZE_STRING);
    $palettePath = 'temp_pals' . DIRECTORY_SEPARATOR . $paletteFile . '.gpl';

    if (!file_exists($palettePath)) {
        return new Response('Palette file: ' . $paletteFile . ' not found!');
    }

    $pal = new BasePalette();
    var_dump($pal->import(new GimpPaletteImporter($palettePath)));

    var_dump($pal->getColors());
});

$app->get('/export/ase/to/gpl/{paletteFile}', function ($paletteFile) use ($app) {
    $paletteFile = filter_var($paletteFile, FILTER_SANITIZE_STRING);
    $palettePath = 'temp_pals' . DIRECTORY_SEPARATOR . $paletteFile . '.ase';

    if (!file_exists($palettePath)) {
        return new Response('Palette file: ' . $paletteFile . ' not found!');
    }

    $pal = new BasePalette();
    $pal->import(new AdobeSwatchExchangeImporter($palettePath));

    $exporter = new GimpPaletteExporter($pal);
    $expContents = $exporter->getExportContents();

    return new Response($expContents, 200, [
        'Content-type'          => 'application/octet-stream',
        'Content-length'        => sizeof($expContents),
        'Content-Disposition'   => 'attachment;filename="' . $pal->getFilename() . '.' . $exporter->getExportFileExtension() . '"'
    ]);
});

$app->get('/export/gpl/to/ase/{paletteFile}', function ($paletteFile) use ($app) {
    $paletteFile = filter_var($paletteFile, FILTER_SANITIZE_STRING);
    $palettePath = 'temp_pals' . DIRECTORY_SEPARATOR . $paletteFile . '.gpl';

    if (!file_exists($palettePath)) {
        return new Response('Palette file: ' . $paletteFile . ' not found!');
    }

    $pal = new BasePalette();
    $pal->import(new GimpPaletteImporter($palettePath));

    $exporter = new GimpPaletteExporter($pal);
    return $pal->export($exporter);
});

$app->run();