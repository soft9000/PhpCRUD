<?php

// 2012/04/01 - Removed compile-time warning in PeekPrefix.

/**
 * Base-64 encoded PString.
 *
 * @author profnagy
 */
// EPString manages the IO of a string using a modern version the PASCAL (as opposed to ASCIIZ) format.
// 2008/11/22: (PString) Class created. -Rn
// 2012/02/08: (PString) Updated to return lengths over void. -Rn
// 2012/02/08: (EPString) Added base64 encoding to prevent newline expansions (etc.) Renamed EPString -Rn
// 2012/02/08: (EPString) Added AsString() & PeekPrefix() for final-length calc -Rn
// 2012/02/08: (EPString) Retired WriteLine() & ReadLine() -Rn
class EPString {

    public function WriteString($handle, $record, $encode = true) {
        $string = EPString::AsString($record, $encode);
        return fwrite($handle, $string);
    }

    public function AsString($record, $encode = true) {
        if ($encode == true)
            $string = base64_encode($record);
        else
            $string = $record;
        return sprintf("%d\t%s", strlen($string) + 1, $string);
    }

    public function PeekPrefix($handle, $peek = true) {
        if ($peek == true) {
            $pos = ftell($handle);
            if ($pos === false) {
                return false;
            }
        }
        $char = null;
        while ($char != "\t") {
            if (feof($handle))
                return false;
            $char = fgetc($handle);
            if ($char == "\t")
                continue;
            $len = $len . $char;
        }
        if ($peek == true)
            if (fseek($handle, $pos) == -1)
                return false;
        // echo "Pascal Prefix = $len\r\n<br>";
        return $len;
    }

    public function ReadString($handle, $encode = true) {
        $len = EPString::PeekPrefix($handle, false);
        if ($len === false)
            return false;
        $string = fgets($handle, $len);
        if ($encode == false)
            return $string;
        $record = base64_decode($string);
        return $record;
    }

}

// PFastFetch manages a file of PStrings is such a way as to allow for a FAST, non-sequential, logical integral enumeraiton / access.
// 2008/11/22: Class created, . Nagy
//
class PFastFetch {

    public function CalcIndexName($file) {
        $result = $file . '.index';
        return $result;
    }

    public function Index($file) {
        $fHandle = fopen($file, 'r');

        $index = PFastFetch::CalcIndexName($file);
        $iHandle = fopen($index, 'w');
        do {
            $pos = ftell($fHandle);
            $string = EPString::ReadString($fHandle);
            if ($string != null) {
                fprintf($iHandle, "%10d\n", $pos);
            }
        } while ($string != null);
    }

    public function Get($file, $iPos) {
        $index = PFastFetch::CalcIndexName($file);
        $iHandle = fopen($index, 'r');
        if ($iHandle == false)
            return null;
        $ixpos = $iPos * 11;
        fseek($iHandle, $ixpos);
        $ifpos = fgets($iHandle);
        fclose($iHandle);
        $fHandle = fopen($file, 'r');
        fseek($fHandle, $ifpos);
        $string = EPString::ReadString($fHandle);
        return $string;
    }

}

?>
