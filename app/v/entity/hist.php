<table>
<?php
    $i = 0;
    foreach ($hist as $fileInfo) {
        // Get the next file information.  This is the crucial part.
        if ($fileInfo['name'] == $hist[count($hist)-1]['name']) { // Last element
            $nextfileInfo['name'] = null; // No next file
        } else {
            $nextfileInfo = $hist[$i + 1]; // i+1 is the next element

        }
        $i++;
        // Build the HTML row
        echo '<tr><td><a href="vhist/'.$fileInfo['name'].'vs'.$nextfileInfo['name'].'" >修改时间: ' . date('Y-m-d H:i:s', $fileInfo['time']) . '</a></td></tr>';
    }

?>
</table>