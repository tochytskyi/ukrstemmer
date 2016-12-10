<?php


/**
 * Ukrainian stemmer
 */
class UkrStemmer
{

    private $tokens = [];

    /* http://uk.wikipedia.org/wiki/Голосний_звук */
    static $VOWEL = '/аеиоуюяіїє/u';
    static $PERFECTIVEGROUND = '/((ив|ивши|ившись))$/u';
    // static $PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись((?<=[ая])(в|вши|вшись)))$/u';
    // http://uk.wikipedia.org/wiki/Рефлексивне_дієслово
    static $REFLEXIVE = '/(с[яьи])$/u';
    //http://uk.wikipedia.org/wiki/Прикметник + http://wapedia.mobi/uk/Прикметник
    static $ADJECTIVE = '/(ими|ій|ий|а|е|ова|ове|ів|є|їй|єє|еє|я|ім|ем|им|ім|их|іх|ою|йми|іми|у|ю|ого|ому|ої)$/u';
    //http://uk.wikipedia.org/wiki/Дієприкметник
    static $PARTICIPLE = '/(ий|ого|ому|им|ім|а|ій|у|ою|ій|і|их|йми|их)$/u';
    //http://uk.wikipedia.org/wiki/Дієслово
    static $VERB = '/(сь|ся|ив|ать|ять|у|ю|ав|али|учи|ячи|вши|ши|е|ме|ати|яти|є)$/u';
    //http://uk.wikipedia.org/wiki/Іменник
    static $NOUN = '/(а|ев|ов|е|ями|ами|еи|и|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я|і|ові|ї|ею|єю|ою|є|еві|ем|єм|ів|їв|\'ю)$/u';
    static $RVRE = '/^(.*?[аеиоуюяіїє])(.*)$/u';
    static $DERIVATIONAL = '/[^аеиоуюяіїє][аеиоуюяіїє]+[^аеиоуюяіїє]+[аеиоуюяіїє].*сть?$/u';

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param string $word
     * @return string
     */
    public static function stemWord(string $word): string
    {
        $stem = mb_strtolower($word);
        do {
            //init
            preg_match_all(self::$RVRE, $stem, $p);
            if (!$p) {
                break;
            }

            if (empty($p[2]) || empty($p[1][0])) {
                break;
            }
            $start = $p[1][0];
            $RV = $p[2][0];

            //STEP 1
            $m = preg_replace(self::$PERFECTIVEGROUND, '', $RV);
            if (strcmp($m, $RV) === 0) {
                $RV = preg_replace(self::$REFLEXIVE, '', $RV);
                $m = preg_replace(self::$ADJECTIVE, '', $RV);
                if (strcmp($m, $RV) === 0) {
                    $RV = preg_replace(self::$PARTICIPLE, '', $RV);
                } else {
                    $RV = $m;
                    $m = preg_replace(self::$VERB, '', $RV);
                    if (strcmp($m, $RV) === 0) {
                        $RV = preg_replace(self::$NOUN, '', $RV);
                    } else {
                        $RV = $m;
                    }
                }
            } else {
                $RV = $m;
            }

            //STEP 2
            $RV = preg_replace('/и$/u', '', $RV);

            //STEP 3
            if (preg_match(self::$DERIVATIONAL, $RV)) {
                $RV = preg_replace('/ость?$/u', '', $RV);
            }

            //STEP 4
            $m = preg_replace('/ь?$/u', '', $RV);
            if (strcmp($m, $RV) === 0) {
                $RV = preg_replace('/ейше?/u', '', $RV);
                $RV = preg_replace('/нн$/u', 'н', $RV);
            } else {
                $RV = $m;
            }

            $stem = $start . $RV;

        } while (false);

        return $stem;
    }

    /**
     * Stem Array
     * @param array $tokens
     * @return array
     */
    public static function stemArray(array $tokens): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return self::stemWord($value);
            }
        }, $tokens);
    }

}
