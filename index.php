<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */

use Colorpalettes\BasePalette,
    Colorpalettes\Exporters\AdobeSwatchExchangeExporter,
    Colorpalettes\Exporters\GimpPaletteExporter;

$app = require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Index page
 */
$app->get('/', function () use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->all();

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

$app->run();