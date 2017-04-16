<?php
// Array with names
//$a['hvb2015spgpl'] = 0;
// $a['hvz16gpfsp'] = 5;
$a['studenthilfe2016'] = 5;
$a['hvzvvwl'] = 5;
//$a['studi2016hvb'] = 10;
$a['hvz17gpfsp'] = 5;


// get the q parameter from URL
$q = $_REQUEST["q"];
$q = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');

$hint = "";

// lookup all hints from array if $q is different from ""
if ($q !== "") {
    $q = strtolower($q);
    $len=strlen($q);
    if (array_key_exists($q, $a)) {
        $hint = $a[$q];
    }
}

// Output "no suggestion" if no hint was found or output correct values
echo $hint === "" ? "0" : $hint;
?>