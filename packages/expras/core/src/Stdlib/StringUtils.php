<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.04.2014
 * Time: 20:38
 */

namespace ExprAs\Core\Stdlib;

class StringUtils
{
    protected static $_tanslitMap
        = ["А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Е" => "E", "Ё" => "E", "Ж" => "J", "З" => "Z", "И" => "I", "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "e", "ж" => "j", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "yi", "ь" => "'", "э" => "e", "ю" => "yu", "я" => "ya"];

    protected static $_metaphoneRuReplacePatterns
        = ['~(йо|ио|йе|ие)~iu' => 'и', '~(о|ы|я)~iu'       => 'а', '~(е|ё|э)~iu'       => 'и', '~ю~iu'             => 'у'];

    final public const string METAPHONE_RU_ALPHABET = "оеаиуэюяпстрклмнбвгджзйфхцчшщыё"; //алфавит кроме исключаемых букв
    final public const string METAPHONE_RU_ZVONKIE = "бздвг"; //звонкие
    final public const string METAPHONE_RU_GLUHIE = "пстфк"; //глухие
    final public const string METAPHONE_RU_SOGLASNIE = "псткбвгджзфхцчшщ"; //согласные, перед которыми звонкие оглушаются


    protected static $_soundexRuTranslitMap
        = ['А' => 'A', 'а' => 'a', 'Б' => 'B', 'б' => 'b', 'В' => 'V', 'в' => 'v', 'Г' => 'G', 'г' => 'g', 'Д' => 'D', 'д' => 'd', 'Е' => 'E', 'е' => 'e', 'Ё' => 'E', 'ё' => 'e', 'Ж' => 'Zh', 'ж' => 'zh', 'З' => 'Z', 'з' => 'z', 'И' => 'I', 'и' => 'i', 'Й' => 'J', 'й' => 'j', 'К' => 'K', 'к' => 'k', 'Л' => 'L', 'л' => 'l', 'М' => 'M', 'м' => 'm', 'Н' => 'N', 'н' => 'n', 'О' => 'O', 'о' => 'o', 'П' => 'P', 'п' => 'p', 'Р' => 'R', 'р' => 'r', 'С' => 'S', 'с' => 's', 'Т' => 'T', 'т' => 't', 'У' => 'U', 'у' => 'u', 'Ф' => 'F', 'ф' => 'f', 'Х' => 'H', 'х' => 'h', 'Ц' => 'C', 'ц' => 'c', 'Ч' => 'Ch', 'ч' => 'ch', 'Ш' => 'Sh', 'ш' => 'sh', 'Щ' => 'Sch', 'щ' => 'sch', 'Ъ' => '\'', 'ъ' => '\'', 'Ы' => 'Y', 'ы' => 'y', 'Ь' => '\'', 'ь' => '\'', 'Э' => 'E', 'э' => 'e', 'Ю' => 'Ju', 'ю' => 'ju', 'Я' => 'Ja', 'я' => 'ja'];

    protected static $_soundexRuCodes
        = [
            'A' => [[0, -1, -1], 'I' => [[0, 1, -1]], 'J' => [[0, 1, -1]], 'Y' => [[0, 1, -1]], 'U' => [[0, 7, -1]]],
            'B' => [[7, 7, 7]],
            'C' => [[5, 5, 5], [4, 4, 4], 'Z' => [[4, 4, 4], 'S' => [[4, 4, 4]]], 'S' => [[4, 4, 4], 'Z' => [[4, 4, 4]]], 'K' => [[5, 5, 5], [45, 45, 45]], 'H' => [[5, 5, 5], [4, 4, 4], 'S' => [[5, 54, 54]]]],
            'D' => [[3, 3, 3], 'T' => [[3, 3, 3]], 'Z' => [[4, 4, 4], 'H' => [[4, 4, 4]], 'S' => [[4, 4, 4]]], 'S' => [[4, 4, 4], 'H' => [[4, 4, 4]], 'Z' => [[4, 4, 4]]], 'R' => ['S' => [[4, 4, 4]], 'Z' => [[4, 4, 4]]]],
            'E' => [[0, -1, -1], 'I' => [[0, 1, -1]], 'J' => [[0, 1, -1]], 'Y' => [[0, 1, -1]], 'U' => [[1, 1, -1]]],
            'F' => [[7, 7, 7], 'B' => [[7, 7, 7]]],
            'G' => [[5, 5, 5]],
            'H' => [[5, 5, -1]],
            'I' => [[0, -1, -1], 'A' => [[1, -1, -1]], 'E' => [[1, -1, -1]], 'O' => [[1, -1, -1]], 'U' => [[1, -1, -1]]],
            'J' => [[4, 4, 4]],
            'K' => [[5, 5, 5], 'H' => [[5, 5, 5]], 'S' => [[5, 54, 54]]],
            'L' => [[8, 8, 8]],
            'M' => [[6, 6, 6], 'N' => [[66, 66, 66]]],
            'N' => [[6, 6, 6], 'M' => [[66, 66, 66]]],
            'O' => [[0, -1, -1], 'I' => [[0, 1, -1]], 'J' => [[0, 1, -1]], 'Y' => [[0, 1, -1]]],
            'P' => [[7, 7, 7], 'F' => [[7, 7, 7]], 'H' => [[7, 7, 7]]],
            'Q' => [[5, 5, 5]],
            'R' => [
                [9, 9, 9],
                'Z' => [[94, 94, 94], [94, 94, 94]],
                // special case
                'S' => [[94, 94, 94], [94, 94, 94]],
            ],
            // special case
            'S' => [[4, 4, 4], 'Z' => [[4, 4, 4], 'T' => [[2, 43, 43]], 'C' => ['Z' => [[2, 4, 4]], 'S' => [[2, 4, 4]]], 'D' => [[2, 43, 43]]], 'D' => [[2, 43, 43]], 'T' => [[2, 43, 43], 'R' => ['Z' => [[2, 4, 4]], 'S' => [[2, 4, 4]]], 'C' => ['H' => [[2, 4, 4]]], 'S' => ['H' => [[2, 4, 4]], 'C' => ['H' => [[2, 4, 4]]]]], 'C' => [[2, 4, 4], 'H' => [[4, 4, 4], 'T' => [[2, 43, 43], 'S' => ['C' => ['H' => [[2, 4, 4]]], 'H' => [[2, 4, 4]]], 'C' => ['H' => [[2, 4, 4]]]], 'D' => [[2, 43, 43]]]], 'H' => [[4, 4, 4], 'T' => [[2, 43, 43], 'C' => ['H' => [[2, 4, 4]]], 'S' => ['H' => [[2, 4, 4]]]], 'C' => ['H' => [[2, 4, 4]]], 'D' => [[2, 43, 43]]]],
            'T' => [[3, 3, 3], 'C' => [[4, 4, 4], 'H' => [[4, 4, 4]]], 'Z' => [[4, 4, 4], 'S' => [[4, 4, 4]]], 'S' => [[4, 4, 4], 'Z' => [[4, 4, 4]], 'H' => [[4, 4, 4]], 'C' => ['H' => [[4, 4, 4]]]], 'T' => ['S' => [[4, 4, 4], 'Z' => [[4, 4, 4]], 'C' => ['H' => [[4, 4, 4]]]], 'C' => ['H' => [[4, 4, 4]]], 'Z' => [[4, 4, 4]]], 'H' => [[3, 3, 3]], 'R' => ['Z' => [[4, 4, 4]], 'S' => [[4, 4, 4]]]],
            'U' => [[0, -1, -1], 'E' => [[0, -1, -1]], 'I' => [[0, 1, -1]], 'J' => [[0, 1, -1]], 'Y' => [[0, 1, -1]]],
            'V' => [[7, 7, 7]],
            'W' => [[7, 7, 7]],
            'X' => [[5, 54, 54]],
            'Y' => [[1, -1, -1]],
            'Z' => [[4, 4, 4], 'D' => [[2, 43, 43], 'Z' => [[2, 4, 4], 'H' => [[2, 4, 4]]]], 'H' => [[4, 4, 4], 'D' => [[2, 43, 43], 'Z' => ['H' => [[2, 4, 4]]]]], 'S' => [[4, 4, 4], 'H' => [[4, 4, 4]], 'C' => ['H' => [[4, 4, 4]]]]],
        ];

    /**
     * @param $string
     *
     * @return string
     */
    public static function translit($string)
    {
        return strtr($string, self::$_tanslitMap);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function metaphoneRu($string)
    {
        // Удалям лишние символы
        $string = preg_replace_callback(
            '~(\w)~ui',
            function ($m) {
                if (mb_stripos(self::METAPHONE_RU_ALPHABET, $m[0]) !== false) {
                    return $m[0];
                }
                return '';
            },
            (string) $string
        );

        // Удалям дубли
        $string = preg_replace_callback(
            '~(\w)\1{1,}~ui',
            fn ($m) => mb_substr((string) $m[0], -1),
            (string) $string
        );

        $string = preg_replace(array_keys(self::$_metaphoneRuReplacePatterns), array_values(self::$_metaphoneRuReplacePatterns), (string) $string);


        while (false !== ($pos = mb_stripos(self::METAPHONE_RU_ZVONKIE, mb_substr((string) $string, -1)))) {
            $string = mb_substr((string) $string, 0, -1) . mb_substr(self::METAPHONE_RU_GLUHIE, $pos, 1);
        }

        foreach (preg_split('~(?<!^)(?!$)~u', self::METAPHONE_RU_ZVONKIE) as $_k => $_ch) {
            while (false !== ($pos = mb_stripos((string) $string, $_ch)) && false !== mb_stripos(self::METAPHONE_RU_SOGLASNIE, mb_substr((string) $string, $pos + 1, 1))) {
                $string = mb_substr((string) $string, 0, $pos) . mb_substr(self::METAPHONE_RU_GLUHIE, $_k, 1) . mb_substr((string) $string, $pos + 1);
            }
        }

        $string = preg_replace('~(тс|дс)~iu', 'ц', (string) $string);

        return $string;
    }

    public static function soundexRuWord($string)
    {
        $length = mb_strlen((string) $string);
        $output = '';
        $i = 0;
        $previous = -1;
        $codes = self::$_soundexRuCodes;

        while ($i < $length) {

            $current = $last = & $codes[mb_substr((string) $string, $i, 1)];

            for ($j = $k = 1; $k < 7; $k++) {

                if ($length - 1 < $i + $k || !isset($current[mb_substr((string) $string, $i + $k, 1)])
                ) {
                    break;
                }

                $current = & $current[mb_substr((string) $string, $i + $k, 1)];
                if (isset($current[0])) {
                    $last = & $current;
                    $j = $k + 1;
                }
            }


            if ($i == 0) {
                $code = $last[0][0];
            } elseif (($length - 1 < $i + $j) || ($codes[mb_substr((string) $string, $i + $j, 1)][0][0] != 0)) {
                $code = isset($last[1]) ? $last[1][2] : $last[0][2];
            } else {
                $code = isset($last[1]) ? $last[1][1] : $last[0][1];
            }


            if (($code != -1) && ($code != $previous)) {
                $output .= $code;
            }

            $previous = $code;
            $i += $j;
        }


        return str_pad(mb_substr($output, 0, 6), 6, '0');

    }


    public static function soundexRu($string)
    {

        $string = self::_soundexRuTranslit($string);

        $string = preg_replace(
            ['#[^\w\s]|\d#iu', '#\b[^\s]{1,3}\b#iu', '#\s{2,}#iu', '#^\s+|\s+$#iu'],
            ['', '', ' '],
            mb_strtoupper((string) $string)
        );


        if (!mb_strlen((string) $string)) {
            return null;
        }

        $matches = explode(' ', (string) $string);

        foreach ($matches as $key => $match) {
            $matches[$key] = self::soundexRuWord($match);
        }

        return implode(' ', $matches);
    }


    protected static function _soundexRuTranslit($string)
    {
        return strtr($string, self::$_soundexRuTranslitMap);
    }

    /**
     * @param $chars
     *
     * @return string
     */
    public static function generateAnagramKey($chars)
    {
        $key = [];
        $word = mb_strtolower(str_replace(' ', '', (string) $chars));
        for ($i = 0; $i < mb_strlen($word); $i++) {
            $chr = mb_substr($word, $i, 1);
            if (!array_key_exists($chr, $key)) {
                $key[$chr] = 0;
            }
            $key[$chr]++;
        }
        uksort($key, fn ($a, $b) => strcmp((string) $a, (string) $b));

        $out = '';
        foreach ($key as $_k => $_v) {
            $out .= ($_v > 1 ? $_v : '') . $_k;
        }
        return $out;
    }
}
