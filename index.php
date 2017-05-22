<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 01.02.16
 * Time: 17:06
 */
declare(strict_types = 1);

use Colorpalettes\{BaseColor, BasePalette};
use Colorpalettes\Importers\{AdobeSwatchExchangeImporter, GimpPaletteImporter};
use Colorpalettes\Exporters\{AdobeSwatchExchangeExporter, AdobeColorfileExporter, GimpPaletteExporter};
use Symfony\Component\HttpFoundation\{Response, Request, JsonResponse};
use Colorpalettes\DatabaseHelper;

$app = require_once __DIR__.DIRECTORY_SEPARATOR.'bootstrap.php';

/**
 * Index page
 *
 * @return Response
 */
$app->get('/', function () use ($app) : Response {
    $cnt = DatabaseHelper::getPaletteCount($app);
    $pals = DatabaseHelper::getLimitedEntries($app, 0, $cnt);

    return $app->render('index.html.twig', [
        'palettes'      => $pals,
        //'offset'        => 12,
        'numpals'       => $cnt,
    ]);
})
->bind('homepage');

$app->get('/load/{offset}', function ($offset) use ($app) : Response {
    $endReached = false;
    $pals = DatabaseHelper::getLimitedEntries($app, (int) $offset, 12);
    if (count($pals) <= 0) {
        $endReached = true;
    }
    $offset += 12;

    return new JsonResponse([
        'html'          => $endReached ? '' : $app->renderView('palette_list.html.twig', ['palettes'  => $pals]),
        'offset'        => $offset,
        'endreached'    => $endReached,
    ]);
})
->bind('loadmore');

/**
 * Export palette to AdobeSwatchExchange
 *
 * @return Response
 */
$app->get('/export/ase/{id}', function ($id) use ($app) : Response {
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
 *
 * @return Response
 */
$app->get('/export/gpl/{id}', function ($id) use ($app) : Response {
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
 *
 * @return Response
 */
$app->get('/export/aco/{id}', function ($id) use ($app) : Response {
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
 *
 * @return Response
 */
$app->get('/previewImports', function () use ($app) : Response {
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
 *
 * @return Response
 */
$app->get('/import/preview/{palName}', function ($palName) use ($app) : Response {

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

/**********************************
 *
 *          Palette editor
 *
 **********************************/

/**
 * Main page
 *
 * @return Response
 */
$app->get('/editor', function () use ($app) : Response {
    return $app->render('editor/index.html.twig');
})
->bind('editor');

/**
 * Save colors to desired format
 *
 * @return JsonResponse
 */
$app->post('/editor/save', function (Request $request) use ($app) : JsonResponse {
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
        case 'ase':
            $exporter = new AdobeSwatchExchangeExporter($exportPalette);
            break;

        case 'aco':
            $exporter = new AdobeColorfileExporter($exportPalette);
            break;

        default:
            $exporter = new GimpPaletteExporter($exportPalette);
            break;
    }

    return new JsonResponse(['status' => 'success', 'exportString' => base64_encode($exporter->getExportContents()), 'extension' => $exporter->getExportFileExtension()]);
});

/**
 * Import .gpl file
 *
 * @return JsonResponse
 */
$app->post('/editor/importGpl', function (Request $request) use ($app) : JsonResponse {
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
 *
 * @return JsonResponse
 */
$app->post('/editor/importASE', function (Request $request) use ($app) : JsonResponse {
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

/**
 * Run the app
 */
$app->run();
