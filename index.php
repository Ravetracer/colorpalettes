<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */

use Colorpalettes\BasePalette;
use Colorpalettes\Exporters\AdobeSwatchExchangeExporter;
use Colorpalettes\Exporters\AdobeColorfileExporter;
use Colorpalettes\Exporters\GimpPaletteExporter;
use Symfony\Component\HttpFoundation\Response;
use Colorpalettes\Importers\GimpPaletteImporter;
use Colorpalettes\Importers\AdobeSwatchExchangeImporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Colorpalettes\BaseColor;

$app = require_once __DIR__.DIRECTORY_SEPARATOR.'bootstrap.php';

/**
 * Index page
 */
$app->get('/', function () use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->all()->order(['filename' => 'ASC']);

    $resultConv = $app["result_to_array"];
    $pals = $resultConv->getPaletteArray($result);

    return $app->render('index.html.twig', [
        'palettes'  => $pals,
    ]);
})
->bind('homepage');

/**
 * Export palette to AdobeSwatchExchange
 */
$app->get('/export/ase/{id}', function ($id) use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');
    $result = $mapper->where(['id' => (int) $id]);

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

    $result = $mapper->where(['id' => (int) $id]);

    /**
     * @var BasePalette $pal
     */
    $pal = $app["result_to_array"]->getPaletteArray($result)[0];
    $exporter = new GimpPaletteExporter($pal);

    return $pal->export($exporter);
})
->bind('gpl_export');

/**
 * Export palette to Adobe Color file (ACO)
 */
$app->get('/export/aco/{id}', function ($id) use ($app) {
    $mapper = $app['spot']->mapper('Entity\Palette');

    $result = $mapper->where(['id' => (int) $id]);

    /**
     * @var BasePalette $pal
     */
    $pal = $app["result_to_array"]->getPaletteArray($result)[0];
    $exporter = new AdobeColorfileExporter($pal);

    return $pal->export($exporter);
})
->bind('aco_export');

/**
 * Preview route for showing new files in the import folder
 */
$app->get('/previewImports', function () use ($app) {
    $previewFiles = glob(__DIR__.'/import/*.gpl');

    if (count($previewFiles) <= 0) {
        return new Response('Currently no import files available');
    }

    array_walk($previewFiles, function (&$item) {
        $item = basename($item, '.gpl');
    });

    return $app->render('previewFiles.html.twig', [
        'files' => $previewFiles,
    ]);
});

/**
 * Preview a single palette file from the preview folder
 */
$app->get('/import/preview/{palName}', function ($palName) use ($app) {

    $fname = __DIR__.'/import/'.filter_var($palName, FILTER_SANITIZE_STRING).'.gpl';
    if (!file_exists($fname)) {
        return new Response("File does not exists!");
    }
    $pal = new BasePalette();
    $importer = new GimpPaletteImporter($fname);
    $pal->import($importer);
    $pal->calculateColorCount();

    return $app->render('preview.html.twig', [
        'pal' => $pal,
    ]);
});

/**
 * Palette editor
 */

$app->get('/editor', function () use ($app) {
    return $app->render('editor/index.html.twig');
})
->bind('editor');

$app->post('/editor/save', function (Request $request) use ($app) {
    $palData = json_decode($request->get('paletteData'), true);
    $cols = (int) $request->get('columns');
    $rows = (int) $request->get('rows');
    $fileType = filter_var($request->get('filetype'), FILTER_SANITIZE_STRING);

    $exportPalette = new BasePalette();
    $colors = [];
    for ($y = 1; $y <= $rows; $y++) {
        for ($x = 1; $x <= $cols; $x++) {
            $currentEntry = $palData[$x.'-'.$y][0];
            preg_match("/#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/", $currentEntry, $matches);
            $r = (int) hexdec($matches[1]);
            $g = (int) hexdec($matches[2]);
            $b = (int) hexdec($matches[3]);
            $currentColor = new BaseColor();
            $currentColor->setRed($r)
                ->setGreen($g)
                ->setBlue($b)
                ->setName(filter_var($request->get('paletteName'), FILTER_SANITIZE_STRING));
            $colors[] = $currentColor;
        }
    }
    $exportPalette->setColors($colors)
        ->setColumns($cols)
        ->setComment(filter_var($request->get('paletteComment'), FILTER_SANITIZE_STRING));

    switch ($fileType) {
        case 'gpl':
            $exporter = new GimpPaletteExporter($exportPalette);
            break;

        case 'ase':
            $exporter = new AdobeSwatchExchangeExporter($exportPalette);
            break;

        case 'aco':
            $exporter = new AdobeColorfileExporter($exportPalette);
            break;
    }

    return new JsonResponse(['status' => 'success', 'exportString' => base64_encode($exporter->getExportContents()), 'extension' => $exporter->getExportFileExtension()]);
});

/**
 * Import .gpl file
 */
$app->post('/editor/importGpl', function (Request $request) use ($app) {
    /**
     * decode submitted Base64 string and replacing standard LF (ASCII char dec. 10) with \n and standard TAB (ASCII char dec. 9) with \t for splitting the array
     */
    $gplData = str_replace([chr(10), chr(9)], ['\n', ' '], base64_decode(str_replace('data:;base64,', '', $request->get('palettefile'))));

    $pal = new BasePalette();
    $importer = new GimpPaletteImporter($gplData);
    $pal->import($importer);
    $pal->calculateColorCount();
    $response = [
        "columns"       => $pal->getColumns() > 1 ? $pal->getColumns() : (count($pal->getColors()) >= 16 ? 16 : count($pal->getColors())),
        "name"          => $pal->getName(),
        "comment"       => $pal->getComment(),
        "numColors"     => $pal->getColorCount(),
        "numColsReal"   => count($pal->getColors()),
        "colors"        => [],
    ];

    /**
     * @var $currentColor BaseColor
     */
    foreach ($pal->getColors() as $currentColor) {
        $response["colors"][] = $currentColor->getCssHex();
    }

    return new JsonResponse($response);
});

/**
 * import ASE file
 */
$app->post('/editor/importASE', function (Request $request) use ($app) {
    $aseData = base64_decode(str_replace('data:;base64,', '', $request->get('palettefile')));

    $fName = 'import/ase_editor_import_'.microtime();
    file_put_contents($fName, $aseData);

    $pal = new BasePalette();
    $importer = new AdobeSwatchExchangeImporter($fName);
    $pal->import($importer);
    $pal->setName('tmpPalette');
    $pal->calculateColorCount();
    $response = [
        "columns"       => count($pal->getColors()) >= 16 ? 16 : count($pal->getColors()),
        "name"          => $pal->getName(),
        "comment"       => $pal->getComment(),
        "numColors"     => $pal->getColorCount(),
        "numColsReal"   => count($pal->getColors()),
        "colors"        => [],
    ];

    /**
     * @var $currentColor BaseColor
     */
    foreach ($pal->getColors() as $currentColor) {
        $response["colors"][] = $currentColor->getCssHex();
    }
    unlink($fName);

    return new JsonResponse($response);
});

$app->run();
