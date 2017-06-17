<?php
// 2012/04/01 - Fixed stat in index_tally, -Rn

/**
 * An index file is an ASCII heap of fixed-length strings.
 *
 * @author profnagy
 */
include_once 'IndexInfo.php';

class IndexFile {

    /**
     * Count the number of items in the index-file.
     * 
     * @param type $info IndexData
     * @return type The number of items in the index-file. Gigo.
     */
    function index_tally($info) {
        if (file_exists($info->file_index) == false || $info->index_max == 0) {
            return 0;
        }
        $fp = fopen($info->file_index, 'r');
        $stat = fstat($fp);
        fclose($fp);
        $filesize = $stat['size'];
        return ($filesize / $info->index_max);
    }

    /**
     * Append a NEW data-file-offset to the index-file.
     * 
     * @param type $info IndexData
     * @param type $pos The offset into the <b>DATA</b> file to append
     * @return boolean TRUE on success, FALSE on error.
     */
    function index_append($info, $pos) {
        $record = str_pad($pos, $info->index_max);
        $result = file_put_contents($info->file_index, $record, FILE_APPEND | LOCK_EX);
        if ($result !== false)
            return true;
        return false;
    }

    /**
     * Read an INDEX record from the INDEX file. Results will be what was put there by index_append() 
     * 
     * @param type $info IndexData
     * @param type $ss The LOGICAL offset into this <b>INDEX</b> file.
     * @return boolean  TRUE on success, FALSE on failure.
     */
    function index_read($info, $ss) {
        if (file_exists($info->file_index) == false)
            return false;
        $fp = fopen($info->file_index, 'r');
        if ($fp === false)
            return false;
        $pos = $ss * $info->index_max;
        $br = fseek($fp, $pos);
        if ($br !== false) {
            $result = fgets($fp, $info->index_max);
        }
        fclose($fp);
        return $result;
    }

}

?>
