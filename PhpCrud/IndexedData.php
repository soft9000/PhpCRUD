<?php

// 2012/04/01 - Fixed dataFileSize(). Added 'b' options for Microsoft interop, -Rn
// 2013/02/25 - Created + added EncodeArray - Rn

/**
 * IndexedData manages an indexed set of fixed-length data-records.
 * While any size data can be written / read, updates are limited
 * to the user-specified size.
 * 
 * Data are retrieved by a LOGICAL ordinal (0, 1, 2, 3 ...)
 *
 * @author profnagy
 */
include_once 'IndexedDataInfo.php';
include_once 'IndexFile.php';

class IndexedData extends IndexFile {

    /**
     * A convenience function. Simply returns the number of items in the index.
     * 
     * @param type $info IndexedDataInfo
     * @return type The number of items in the index.
     */
    function tally($info) {
        return IndexedData::index_tally($info);
    }

    /**
     * Common re-use. Save an EPString to the file.
     * 
     * @param type $info IndexedDataInfo
     * @param type $fp Where to write it
     * @param type $rec What to write there
     * @param boolean $limit (optional) TRUE to enforce the size-limitaiton. False to allow record overflow.
     * @return boolean True if the record was written, FALSE if the record was not. 
     * The use of $limit implies different failure-meanings, yet the same result (i.e not written.)
     */
    private function write_payload($info, $fp, $rec, $limit = false) {
        $pas = new EPString();
        if ($limit == true) {
            $string = $pas->AsString($rec, $info->isEncoded);
            if (strlen($string) >= $info->data_min) {
                return false; // too large
            } else {
                $len = fwrite($fp, $string);
            }
        } else {
            $len = $pas->WriteString($fp, $rec, $info->isEncoded);
            if ($len >= $info->data_min)
                return true;
        }
        $remainder = $info->data_min - $len;
        $pad = str_pad("", $remainder, '~');
        $br = fputs($fp, $pad);
        return $br;
    }

    /**
     * Append a new record to the record-list-on-a-disk. Is <b>NOT</b> constrained by $info->data_min.
     * 
     * @param type $info IndexedDataInfo
     * @param type $record The record to append to the end of the data file.
     * 
     * @return boolean False on error, else the LOGICAL number of the record appended.
     */
    function append($info, $record) {
        $number = IndexedData::index_tally($info);
        $fp = fopen($info->file_data, 'a');
        if ($fp === false)
            return false;
        $br = false;
        $pos = IndexedData::dataFileSize($info);
        // $pos = ftell($fp); - will not work with append mode -
        $br = IndexedData::write_payload($info, $fp, $record, false); // no limit
        if ($br !== 0) {
            $br = IndexedData::index_append($info, $pos);
        }
        fclose($fp);
        if ($br !== false)
            return $number;
        return false;
    }

    /**
     * Read a LOGICAL (index) record from the database.
     * 
     * @param type $info IndexedDataInfo
     * @param type $ss The LOGICAL (0, 1, 2, 3 ...) record identifier
     * @return boolean The data read. FALSE on error.
     */
    function read($info, $ss) {
        $pos = IndexedData::index_read($info, $ss);
        if ($pos == false)
            return false;
        $fp = fopen($info->file_data, 'rb');
        if ($fp === false)
            return false;

        $br = fseek($fp, $pos);
        if ($br !== false) {
            //$result = fgets($fp, $info->data_max);
            $pas = new EPString();
            $result = $pas->ReadString($fp, $info->isEncoded);
            if ($result === false)
                $br = false;
        }
        fclose($fp);
        if ($br === false)
            return false;
        return $result;
    }

    /**
     * Update the fixed-length record in the file. For safety reasons (i.e do not 
     * want to over-write the previous) this <b>IS ALWAYS</b> constrained by 
     * $info->data_min.
     * 
     * @param type $info IndexedDataInfo
     * @param type $ss The LOGICAL (0, 1, 2, 3 ...) record identifier
     * @param type $record The record to over-write the data WITH.
     * @return boolean True on success. False on error.
     */
    function update($info, $ss, $record) {
        $pos = IndexedData::index_read($info, $ss);
        if ($pos == false)
            return false;
        $fp = fopen($info->file_data, 'r+b');
        if ($fp === false)
            return false;
        $br = fseek($fp, $pos);
        if ($br !== false) {
            $br = IndexedData::write_payload($info, $fp, $record, true); // yes - limit
        }
        fclose($fp);
        if ($br === false)
            return false;
        return true;
    }

    /**
     * Over-write data with an empty record. Length of read will be zero.
     * 
     * @param type $info IndexedDataInfo
     * @param type $ss The LOGICAL (0, 1, 2, 3 ...) record identifier
     * @return boolean True on success. False on error.
     */
    function delete($info, $ss) {
        return IndexedData::update($info, $ss, '');
    }

    /**
     * Get the size of the DATA file. -Required to get around various and funky
     * problems with fseek() and ftell() when trying to APPEND data (there are
     * problems with just about every fopen() mode - OR across platforms - in 5.x
     * (to date, caveat developer for more modes than just 'a' or 'a+'!))
     * 
     * @param type $info IndexedDataInfo 
     * @return int The size of the file. Zero if empty, or not found.
     */
    function dataFileSize($info) {
        if (file_exists($info->file_data) == false || $info->index_max == 0) {
            return 0;
        }
        $fp = fopen($info->file_data, 'r');
        $stat = fstat($fp);
        fclose($fp);
        $filesize = $stat['size'];
        return $filesize;
    }

}

?>
