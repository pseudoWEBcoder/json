<?php

function format_filesize($B, $D = 2)
{
$S = 'kMGTPEZY';
$F = floor((strlen($B) - 1) / 3);
return sprintf("%.{$D}f", $B / pow(1024, $F)) . ' ' . @$S[$F - 1] . 'B';
}
function get($key,$arr=null){$arr=$arr??$_REQUEST; return isset($arr[$key])?$arr[$key]:false;}