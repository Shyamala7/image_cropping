<?php
public function getImageScale($x, $y, $longestLength, $allowDistort = false) {
        //Set the default NEW values to be the old, in case it doesn't even need scaling
        list($nx, $ny) = array($x, $y);
            if ($x > $y) {
                if ($longestLength > $x && !$allowDistort) {
                    return array($x, $y);
                }
                $r = $x / $longestLength;
                return array($longestLength, $y / $r);
            } else {
                if ($longestLength > $x && !$allowDistort) {
                    return array($x, $y);
                }
                $r = $y / $longestLength;
                return array($x / $r, $longestLength);
            }
        return array($nx, $ny);
    }


    $originalImageX = 480;
$originalImageY = 260;
$scale = new Scale();
var_dump($scale->getImageScale($originalImageX,$originalImageY,120 ) );  
?>
