<?php
// 2012/04/01 - Updated delete(), -Rn

include_once 'IndexInfo.php';
include_once 'EncodedPstring.php';

/**
 * A place to store our file names, minimal record size, and other file-centric datum.
 *
 * @author profnagy
 */
class IndexedDataInfo extends IndexInfo {
    
    /**
     * Demonstrated Base64 encoding can take as much as 30% of the final record space.
     * If you set isEncoded to TRUE, be sure to adjust your $data_min sizes accordingly!
     * 
     * @var boolean TRUE to use base64 encoding, false to ignore it. 
     */
    var $isEncoded = false;

    /**
     * Minimum Record Size: The record CAN  be larger, but the TOTAL update()ABLE 
     * area will be at LEAST this size. 
     * 
     * @var type The size of the reusable data-area.
     */
    var $data_min = 1024;

    /**
     * The name of your data-file
     * 
     * @var type Data file name
     */
    var $file_data = 'default.data';

    function __construct() {
        $this->setFileNames('data');
    }

    /**
     * Delete the data file.
     * 
     * @return boolean True if removed.
     */
    function deleteData() {
        return unlink($this->file_data);
    }


    /**
     * Remove any index file and  / or data file(s).
     * 
     * @return boolean True if BOTH the data and index files were removed. False otherwise.
     */
    function delete() {
        if (file_exists($this->file_index))
            if ($this->deleteIndex() == false)
                return false;
        if (file_exists($this->file_data))
            if ($this->deleteData() == false)
                return false;
        return true;
    }

    /**
     * Enforce a reasonable, predictable file and index 'sidecar' naming convention.
     * 
     * @param type $filename 
     */
    function setFileNames($filename) {
        $this->file_data = $filename;
        $this->file_index = $filename . '.index';
    }

    /**
     * See if a string SHOULD fit into the database. The calculation is used 
     * by the framework for the update()-operation ONLY (i.e. appending() 
     * oversized records is okay!) Provided here for your use.
     * 
     * @param type $string What you plan to write.
     * @return boolean TRUE if the item will fit within the default size. 
     * False otherwise. 
     */
    function checksize($string) {
        $pas = new EPString();
        $record = $pas->AsString($string, $this->isEncoded);
        return (strlen($record) < $this->data_min);
    }

    /**
     * Return the EXACT cost of our Pascal + Base64 encoding for a string.
     * 
     * @param type $string What is to be written.
     * @return type What it will cost to 'enrecord, in bytes.
     */
    function getOverhead($string) {
        $pas = new EPString();
        $record = $pas->AsString($string, $this->isEncoded);
        return strlen($record) - strlen($string);
    }
    

}

?>
