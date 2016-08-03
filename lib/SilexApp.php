<?php
/**
 * Created by PhpStorm.
 * User: cnielebock
 * Date: 11.07.16
 * Time: 15:46
 */
namespace Colorpalettes;

use Silex\Application;

/**
 * Class SilexApp
 * @package Colorpalettes
 */
class SilexApp extends Application
{
    use Application\MonologTrait;
    use Application\TwigTrait;
    use Application\UrlGeneratorTrait;
}