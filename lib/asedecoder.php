<?php

namespace Colorpalettes;

/**
 * A class to decode Adobe Swatch Exchange files
 */
class ASEDecoder
{

    /**
     * Decodes an ASE file
     * @param string $file
     * @return array
     */
    public static function decodeFile($file): array
    {
        $swatches = array();
        $fp = fopen($file, "rb");

        // get the header;
        $data = fread($fp, 4); // 4 is the byte size of a whole on a 32-bit PC.
        if ($data != "ASEF") {
            return $swatches;
        }

        // get the ase version number
        $data = fread($fp, 4);
        $headerVersion = unpack("n*", $data);
        $version = $headerVersion[1].".".$headerVersion[2];

        // get the number of chunks
        $data = fread($fp, 4);
        $chunkCountArray = unpack("N", $data);
        $chunkCount = $chunkCountArray[1];
        for ($chunk = 0; $chunk < $chunkCount; $chunk++) {
            $data = fread($fp, 4);
            $chunkTypeArray = unpack("H*", $data);
            $chunkType = $chunkTypeArray[1];

            $data = fread($fp, 2);
            $chunkSizeArray = unpack("n", $data);
            $chunkSize = $chunkSizeArray[1] - 2;

            $data = fread($fp, 2);
            $chuckCharCountArray = unpack("n", $data);
            $chunkCharCount = $chuckCharCountArray[1];

            switch ($chunkType) {
                case "c0010000": // pallate name chunk
                    $data = fread($fp, $chunkCharCount * 2);
                    $name = utf8_decode($data);
                    break;
                case "00010000": // swatch chunk
                case "00000000": // final chunk
                    $data = fread($fp, $chunkCharCount * 2);
                    $colorTitle = utf8_decode($data);
                    $data = fread($fp, 4);
                    $colorSpace = trim($data);
                    $r = $g = $b = 0;
                    $rh = $gh = $bh = 0;
                    switch ($colorSpace) {
                        case "RGB":
                            $rr = fread($fp, 4);
                            $gr = fread($fp, 4);
                            $br = fread($fp, 4);
                            $rh = self::dec2hex((int) round((self::toFloat($rr) * 255), 0));
                            $gh = self::dec2hex((int) round((self::toFloat($gr) * 255), 0));
                            $bh = self::dec2hex((int) round((self::toFloat($br) * 255), 0));
                            $r = (int) round((self::toFloat($rr) * 255), 0);
                            $g = (int) round((self::toFloat($gr) * 255), 0);
                            $b = (int) round((self::toFloat($br) * 255), 0);
                            break;
                        case "CMYK":
                            $c = self::toFloat(fread($fp, 4));
                            $m = self::toFloat(fread($fp, 4));
                            $y = self::toFloat(fread($fp, 4));
                            $k = self::toFloat(fread($fp, 4));
                            $out = self::cmykToRgb($c, $m, $y, $k);
                            $r = $out->r;
                            $g = $out->g;
                            $b = $out->b;
                            break;
                        case "GRAY":
                            $val = dechex((int) round((self::toFloat(fread($fp, 4)) * 255), 0));
                            $r = $val;
                            $g = $val;
                            $b = $val;
                            break;
                        case "LAB":
                            $l = self::toFloat(fread($fp, 4));
                            $a = self::toFloat(fread($fp, 4));
                            $b = self::toFloat(fread($fp, 4));
                            $out = self::labToRgb($l, $a, $b);
                            $r = $out->r;
                            $g = $out->g;
                            $b = $out->b;
                            break;
                        default:
                            $data = fread($fp, $chunkSize - 2);
                            break;
                    }
                    $data = fread($fp, 2); // end of chunk
                    $swatches[] = [
                        'hex' => '"#'.$rh.$gh.$bh.'" ',
                        'r' => $r,
                        'g' => $g,
                        'b' => $b,
                        'title' => $colorTitle,
                    ];
                    break;
            }
        }
        fclose($fp);

        return $swatches;
    }

    /**
     * @param $data
     * @return float
     */
    private static function toFloat($data): float
    {
        $t = unpack("C*", pack("S*", 256));
        if ($t[1] == 1) {
            $a = unpack("f*", $data);
        } else {
            $a = unpack("f*", strrev($data));
        }

        return (float) $a[1];
    }

    /**
     * @param $c
     * @param $m
     * @param $y
     * @param $k
     * @return \stdClass
     */
    private static function cmykToRgb($c, $m, $y, $k): \stdClass
    {
        $o = new \stdClass();
        $r = 1 - ($c * (1 - $k)) - $k;
        $g = 1 - ($m * (1 - $k)) - $k;
        $b = 1 - ($y * (1 - $k)) - $k;
        $o->r = self::dec2hex((int) round($r * 255));
        $o->g = self::dec2hex((int) round($g * 255));
        $o->b = self::dec2hex((int) round($b * 255));

        return $o;
    }

    /**
     * @param $l
     * @param $a
     * @param $b
     * @return \stdClass
     */
    private static function labToRgb($l, $a, $b): \stdClass
    {
        $o = new \stdClass();
        $refX = 95.047;
        $refY = 100.000;
        $refZ = 108.883;

        $varY = ($l + 16) / 116;
        $varX = $a / 500 + $varY;
        $varZ = $varY - $b / 200;

        if ($varY ^ 3 > 0.008856) {
            $varY = $varY ^ 3;
        } else {
            $varY = ($varY - 16 / 116) / 7.787;
        }

        if ($varX ^ 3 > 0.008856) {
            $varX = $varX ^ 3;
        } else {
            $varX = ($varX - 16 / 116) / 7.787;
        }

        if ($varZ ^ 3 > 0.008856) {
            $varZ = $varZ ^ 3;
        } else {
            $varZ = ($varZ - 16 / 116) / 7.787;
        }

        $x = $refX * $varX; //ref_X =  95.047     Observer= 2ďż˝, Illuminant= D65
        $y = $refY * $varY; //ref_Y = 100.000
        $z = $refZ * $varZ; //ref_Z = 108.883

        $varX = $x / 100; //X from 0 to  95.047      (Observer = 2ďż˝, Illuminant = D65)
        $varY = $y / 100; //Y from 0 to 100.000
        $varZ = $z / 100; //Z from 0 to 108.883

        $varR = $varX * 3.2406 + $varY * -1.5372 + $varZ * -0.4986;
        $varG = $varX * -0.9689 + $varY * 1.8758 + $varZ * 0.0415;
        $varB = $varX * 0.0557 + $varY * -0.2040 + $varZ * 1.0570;

        if ($varR > 0.0031308) {
            $varR = 1.055 * ($varR ^ (1 / 2.4)) - 0.055;
        } else {
            $varR = 12.92 * $varR;
        }

        if ($varG > 0.0031308) {
            $varG = 1.055 * ($varG ^ (1 / 2.4)) - 0.055;
        } else {
            $varG = 12.92 * $varG;
        }

        if ($varB > 0.0031308) {
            $varB = 1.055 * ($varB ^ (1 / 2.4)) - 0.055;
        } else {
            $varB = 12.92 * $varB;
        }

        $o->r = self::dec2hex((int) round($varR * 255));
        $o->g = self::dec2hex((int) round($varG * 255));
        $o->b = self::dec2hex((int) round($varB * 255));

        return $o;
    }

    /**
     * @param $bin
     * @return string
     */
    private static function dec2hex($bin): string
    {
        $ret = "";
        $string = dechex($bin);
        if (strlen($string) == 1) {
            $ret = "0";
        }
        $ret .= $string;

        return $ret;
    }
}
