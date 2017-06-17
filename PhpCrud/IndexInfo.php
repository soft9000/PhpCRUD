<?php
/**
 * What is required to manage &amp; maintain an ASCII index
 *
 * @author profnagy
 */
class IndexInfo {
    var $file_index = 'default.index';
    var $index_max = 20;
    
    function deleteIndex() {
        return unlink($this->file_index);
    }    
}

?>
