<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>PHP CRUD Public Test Case</title>
    </head>
    <body>
        <?php
        include_once 'IndexedData.php';

        $control = new IndexedData();
        $info = new IndexedDataInfo();

        $info->delete();
        $info->data_min = 4096;

        $filetest = "$foofile.temp";
        if (file_put_contents($filetest, time()) == false) {
            echo 'Error: Unable to perform simple file access. Please be sure your server permits file writing?<br>';
            exit(-1);
        }
        if (unlink($filetest) == false) {
            echo 'Error: Unable to perform file removal. Please be sure your server permits file deletion?<br>';
            exit(-1);
        }

        $oflow = str_pad(" ", $info->data_min * 2);
        if ($info->checksize($oflow) == true) {
            echo 'Error 001: Support function regression.<br>';
        }
        $oflow = str_pad(" ", $info->data_min / 4);
        if ($info->checksize($oflow) == false) {
            echo 'Error 002: Support function regression.<br>';
        }

        $set = array();

        // Creation Test
        $set[0] = 'one';
        $set[1] = 'two';
        $set[2] = 'three';
        $ss = 0;
        foreach ($set as $line) {
            $logical = $control->append($info, $line);
            if ($logical != $ss++) {
                echo "Error: 101, got [$logical] for [$line]<br>";
            }
            $val = $control->read($info, $logical);
            if (strcmp($val, $line) != 0) {
                echo "Error: 102, got [$val], not [$line]<br>";
            }
        }

        // Update Test
        $set[0] = '4';
        $set[1] = 'Five';
        $set[2] = '6';

        $ss = 0;

        foreach ($set as $line) {
            if ($control->update($info, $ss, $line) == false) {
                echo "Error: 201, got [$logical] for [$line]<br>";
            }
            $val = $control->read($info, $ss);
            if (strcmp($val, $line) != 0) {
                echo "Error: 202, got [$val], not [$line]<br>";
            } else {
                // echo "Got $val<br>";
            }
            $ss++;
        }

        // Delete Test
        $ss = 0;
        foreach ($set as $line) {
            if ($control->delete($info, $ss) == false) {
                echo "Error: 301, got [$logical] for [$line]<br>";
            }
            $val = $control->read($info, $ss);
            $len = strlen($val);
            if ($len != 0) {
                echo "Error: 302, got [$val] - size is $len, not blank?<br>";
            } else {
                // echo "Got $val<br>";
            }
            $ss++;
        }

        // Deletion Area Re-Use        
        $set[0] = 'one';
        $set[1] = 'two';
        $set[2] = 'three';
        $ss = 0;
        foreach ($set as $line) {
            if ($control->update($info, $ss, $line) == false) {
                echo "Error: 401, got [$logical] for [$line]<br>";
            }
            $val = $control->read($info, $ss);
            if (strcmp($val, $line) != 0) {
                echo "Error: 402, got [$val], not [$line]<br>";
            } else {
                // echo "Got $val<br>";
            }
            $ss++;
        }

        // Create some more, stressing a few of the more probable edge conditions -
        $info->isEncoded = true;
        $set[0] = "\tWe want to test the\n\n";
        $set[1] = "\n\tVeRry\nBest!";
        $set[2] = "$Chuck\r\n\tthe rest!\t\n\r";
        $ss = sizeof($set);
        foreach ($set as $line) {
            $logical = $control->append($info, $line);
            if ($logical != $ss++) {
                echo "Error: 501, got [$logical] for [$line]<br>";
            }
            $val = $control->read($info, $logical);
            if (strcmp($val, $line) != 0) {
                echo "Error: 501, got [$val], not [$line]<br>";
            }
        }
        
        // FINALLY
        // =======
        // Show some metrics, and test a constrained update
        $bogus = str_pad(':-(', $info->data_min);
        $info->isEncoded = true;
        $cost = $info->getOverhead($bogus);
        echo "Pascal + Base64 overhead is $cost bytes for a $info->data_min-byte string.<br>";
        $info->isEncoded = false;
        $cost = $info->getOverhead($bogus);
        echo "Pascal (ONLY) overhead is <b>$cost bytes</b> for a $info->data_min-byte string.<br>";
        if($control->update($info, 0, $bogus) !== false) {
            echo 'Error 601: Overflow update did not fail.<br>';
        }

        echo "neoj<br>";
        ?>
    </body>
</html>
