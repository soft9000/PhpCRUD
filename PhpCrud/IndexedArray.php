<?php

// 2013/02/25 Class created to support a new version of the ClassIO Code Generator. -Rn

include_once 'IndexedData.php';

/**
 * PHPCrud stores array-data as a string. Use IndexedArray to encode, decode, & index same.
 *
 * @author profnagy
 */
class IndexedArray extends IndexedData {

    var $code;

    /**
     * Create the code we wil use to encode / decode arrays 
     */
    public function __construct() {
        $this->code = sprintf("%c", 0xfe);
    }

    /**
     * Will come in handy for field / record detection
     * @return type 
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Encode / Implode a string / array of strings into a single string.
     * 
     * @param type $array $Array is EITHER a stirng, OR an array of strings.
     * @return type As single, encoded, string.
     */
    public function arrayToString($array) {
        $result = '';
        $tot = count($array);
        if ($tot == 1)
            return $array; // already a string
        for ($ss = 0; $ss < $tot; $ss++) {
            $result .= $array[$ss];
            if ($ss != ($tot - 1))
                $result .= $this->code;
        }
        return $result;
    }

    /**
     * Decode / Explore an arrayToString-encoded-string into an array of same.
     * 
     * @param type $string A EITHER as string, OR a self-encoded array-string.
     * 
     * @return type The original array of strings.
     */
    public function stringToArray($string) {
        if (strstr($string, $this->code) === FALSE)
            return $string;
        return explode($this->code, $string);
    }

}
/*
// TEST NEW FEATURES, ONLY.
// TODO: Might want to move these to a test framework some day ...
$test = true;
if ($test === TRUE) {
    $coder = new IndexedArray();

    $str = "booys fooya";
    $string = $coder->arrayToString($str);
    if (strcmp($string, $str) != 0)
        echo "<br>ERROR 101<br>\r\n";
    $string = $coder->stringToArray($str);
    if (strcmp($string, $str) != 0)
        echo "<br>ERROR 201<br>\r\n";

    $pizza = array();
    for ($ss = 0; $ss < 10; $ss++) {
        $pizza[$ss] = 'slice' . $ss;
    }

    $string = $coder->arrayToString($pizza);
    echo "PASS: $string<br><br>\r\n";
    $array = $coder->stringToArray($string);

    foreach ($array as $str)
        echo "Got: $str<br>\r\n";
}
 */
?>
