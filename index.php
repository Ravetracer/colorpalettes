<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */

use Colorpalettes\BasePalette,
    Colorpalettes\Exporters\AdobeSwatchExchangeExporter,
    Colorpalettes\Exporters\GimpPaletteExporter,
    Symfony\Component\HttpFoundation\Response,
    Colorpalettes\Importers\GimpPaletteImporter,
    Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Index page
 */
$app->get('/', function () use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->all()->order(['filename' => 'ASC']);

    $resultConv = $app["result_to_array"];
    $pals = $resultConv->getPaletteArray($result);

    return $app->render('index.html.twig', [
        'palettes' => $pals
    ]);
})
->bind('homepage');

/**
 * Export palette to AdobeSwatchExchange
 */
$app->get('/export/ase/{id}', function ($id) use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->where(['id' => (int)$id]);

    /**
     * @var BasePalette $pal
     */
    $pal = $app["result_to_array"]->getPaletteArray($result)[0];
    $exporter = new AdobeSwatchExchangeExporter($pal);
    return $pal->export($exporter);
})
->bind('ase_export');

/**
 * Export palette to GIMP palette file format
 */
$app->get('/export/gpl/{id}', function ($id) use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->where(['id' => (int)$id]);

    /**
     * @var BasePalette $pal
     */
    $pal = $app["result_to_array"]->getPaletteArray($result)[0];
    $exporter = new GimpPaletteExporter($pal);
    return $pal->export($exporter);
})
->bind('gpl_export');

/**
 * Preview route for showing new files in the import folder
 */
$app->get('/previewImports', function () use ($app) {
    $previewFiles = glob(__DIR__ . '/import/*.gpl');

    if (count($previewFiles) <= 0) {
        return new Response('Currently no import files available');
    }

    array_walk($previewFiles, function(&$item) {
        $item = basename($item, '.gpl');
    });

    return $app->render('previewFiles.html.twig', [
        'files' => $previewFiles
    ]);
});

/**
 * Preview a single palette file from the preview folder
 */
$app->get('/import/preview/{palName}', function ($palName) use ($app) {

    $fname = __DIR__ . '/import/' . filter_var($palName, FILTER_SANITIZE_STRING) . '.gpl';
    if (!file_exists($fname)) {
        return new Response("File does not exists!");
    }
    $pal = new BasePalette();
    $importer = new GimpPaletteImporter($fname);
    $pal->import($importer);
    $pal->calculateColorCount();

    return $app->render('preview.html.twig', [
        'pal' => $pal
    ]);
});

/**
 * Convert palette
 */
$app->post('/convert', function(Request $request) use ($app) {
    echo "<pre>", print_r($request, true), "</pre>";
})
->bind('convert_pal');

/**
 * Palette editor
 */

$app->get('/editor', function () use ($app) {
    return $app->render('editor/index.html.twig');
});

$app->run();