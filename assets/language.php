<?php 

class Language{

    public $trans;

    public static $instance;

    public function __construct($langjson){

        $this->trans = $langjson;

        self::$instance = $this;

    }

    static function translate($key){

        $txt = self::$instance->trans[$key];

        if (strpos($txt, "%n") !== false) {

            $text = str_replace("%n", "\n", $txt);

        } else {

            $text = $txt;

        }

        return $text;

    }

}

function loadLang($languageCode = false) {

    if (!$languageCode) {

        $languageCode = 'nl_NL';

    }

    $filePath = __DIR__ . "/languages/{$languageCode}.json";
    if (!file_exists($filePath)) {
        $filePath = __DIR__ . "/languages/nl_NL.json"; // Fallback to Dutch if file not found
    }
    $langJson = json_decode(file_get_contents($filePath), true);

    new Language($langJson);

}
?>