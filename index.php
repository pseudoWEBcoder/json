<?php

use RedBeanPHP\R;

error_reporting(E_ERROR);
require 'vendor/autoload.php';
R::setup('sqlite:' . realpath($df = dirname(__FILE__) . '/db/db.db'));
$goods = R::dispense('goods');
$dir = '/sdcard/Download/';
$dir = 'C:\Users\nik-msk-win10\Desktop\json';
$dirname = realpath($dir);
if (isset($_REQUEST['file'])) {
    $file = $_REQUEST['file'];
    $format = $_REQUEST['format'];
    if (is_dir($dir))
        $str = $format == 'json' ? execute($dirname . '/' . $file . '.json')
            : execute2($dirname . '/' . $file . '.json');
} else {
    $str = 'не найдено';
}
$all = scandir($dirname);
//dump($all);
//echo '<hr>';
foreach ($all as $k => $v) {
    if (file_exists($r = realpath($dirname . '/' . $v)) && ($inf = pathinfo($v)) && isset($inf['extension']) && $inf['extension'] == 'json')
        $filtered[] = ['name' => $inf['filename'], 'time' => filemtime($r), 'size' => format_filesize(filesize($r))];
}
uksort($arr, function ($a, $b) use ($filtered) {

    return $filtered[$b]['time'] - $filtered[$a]['time'];
});
//dump([basename($file,'.json'),$filtered]);

foreach ($filtered as $k => $v) {
    $checked = ($file == $v['name']) ? ' selected="selected"' : '';
    $options[] = "<option value=\"{$v['name']}\"$checked >" . date('d.m.Y h:i:s', $v['time']) . " - {$v['name']} - {$v['size']}</option>";
}
//dump($options);
function format_filesize($B, $D = 2)
{
    $S = 'kMGTPEZY';
    $F = floor((strlen($B) - 1) / 3);
    return sprintf("%.{$D}f", $B / pow(1024, $F)) . ' ' . @$S[$F - 1] . 'B';
}
function dump($arr)
{
    echo '<pre>';
    var_dump($arr);
    echo '</pre>';
}

function execute($path)
{
    $c = file_get_contents($path);
    $arr = json_decode($c, true);
    array_walk_recursive($arr, function (&$v, $k) {
        if (is_numeric($v) && in_array($k, ['price', 'totalSum', 'sum', 'cashTotalSum', 'ecashTotalSum']))
            $v = (float)$v / 100;
        //number_format($v/100, 2, '.', ' ');
    }
    );
    return json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function save($arr)
{
    global $goods;
    foreach ($arr as $index => $item) {
        if ($item["document"]["receipt"]["dateTime"] == $_REQUEST['date'])
            break;
    }
    foreach ($item["document"]["receipt"] as $field => $value) {
        if ($field !== 'items')
            $goods->$field = $value;
    }
    R::store($goods);


}
function execute2($path)
{
    $c = file_get_contents($path);
    $arr = json_decode($c, true);
    uksort($arr, function ($a, $b) use ($arr) {
        $v1 = $arr[$a]["document"]["receipt"]["dateTime"] = $arr[$a]["document"]["receipt"]["dateTime"];
        $v2 = $arr[$b]["document"]["receipt"]["dateTime"];
        $f = 'Y-m-d\TH:i:s';
        $date1 = DateTime::createFromFormat($f, $v1);
        $date2 = DateTime::createFromFormat($f, $v2);
        return $date1 && $date2 ? (int)($date2->format('U') - $date1->format('U')) : 0;
    });
    if (isset($_REQUEST['action']))
        save($arr);
    array_walk_recursive($arr, function (&$v, $k) {
        if (is_numeric($v) && in_array($k, ['price', 'totalSum', 'sum', 'cashTotalSum', 'ecashTotalSum']))
            $v = (float)$v / 100;
        //number_format($v/100, 2, '.', ' ');
    }
    );
    $ul = '';
    $icon = [
        'before' => ['dateTime' => '&#128357;', 'name' => '', 'price' => ' ', 'quantity' => '&times;&nbsp;', 'sum' => '=', 'retailPlaceAddress' => '&#128506; '],
        'after' => ['name' => '', 'price' => '&#x20bd; ', 'quantity' => '', 'sum' => '&#x20bd', 'totalSum' => '&#x20bd; '],
        'actions' => ['commit' => '&#10004;']];
    foreach ($arr as $k => $v) {
        $r = $v['document']['receipt'];
        $ul .= '<li>';
        foreach (['dateTime', 'totalSum', 'user', 'retailPlaceAddress'] as $i) {
            $val = ($i == 'dateTime' ? dateFormat($r['dateTime']) : get($i, $r));
            if ($val)
                $ul .= ' <span class="' . $i . '">' . '<span class="icon">' . get($i, $icon['before']) . '</span>' . $val . '<span class="icon">' . get($i, $icon['after']) . '</span>' . '</span>';
        }
        $ul .= '<ol class="items" start="1">';
        foreach (get('items', $r) as $I => $item) {
            $ul .= '<li>';
            foreach (['name', 'price', 'quantity', 'sum'] as $j)
                $ul .= "\t" . '<span class="' . $j . '">' . '<span class="icon">' . get($j, $icon['before']) . '</span>' . get($j, $item) . '<span class="icon">' . get($j, $icon['after']) . '</span>' . '</span>';
            $ul .= '<ul class="actions">';
            foreach (['commit'] as $ak => $av) {
                $ul .= '<li>';
                $ul .= '<a href="?' . http_build_query(['action' => $av, 'number' => $I, 'date' => $r['dateTime']]) . '">' . get($av, $icon['actions']) . '</a>';

                $ul .= '</li>';
            }
            $ul .= '</ul>';///---
            $ul .= '</li>';
        }
        $ul .= '</ol>';
        $ul .= '</li>';

    }
    return '<ul class="receipts">' . $ul . '</ul>';
}

function dateFormat($v, $f2 = 'd.m.Y H:i:s', $f1 = 'Y-m-d\TH:i:s')
{

    $date1 = DateTime::createFromFormat($f1, $v);
    return $date1 ? $date1->format($f2) : $v;

}

function cmp($a, $b)
{
    $date1  = DateTime::createFromFormat('Y-m-dTh:i:s', $a['dateTime']);
    $date2  = DateTime::createFromFormat('Y-m-dTh:i:s', $b['dateTime']);


    return $date1 && $date2 ? (int)($date2->format('u') - $date1->format('u')) : 0;
}

function get($key, $arr = null, $default = null)
{
    $arr = $arr ?? $_REQUEST;
    return isset($arr[$key]) ? $arr[$key] : $default;
}

?>
<form method=post>
    <select type="text" requred=true name="file">
        <?php
        echo implode(PHP_EOL, $options);
        ?>
    </select>
    <input type="submit" value="отправить ">
    </input>
    <label for="json">
        <input type="radio" value="json" name="format">
        </input>json
    </label>
    <label for="ul">
        <input type="radio" value="ul" name="format">
        </input>ol
    </label>
</form>
<style>
    ul.receipts > li {
        font-family: Tahoma;
        border: 1px solid #ccc;
        font-weight: bolder;
        color: 999;
    }

    ul.receipts > li > ol > li {
        border: none;
        font-weight: normal;
    }

    .items > li {
        text-transform: lowercase;
    }

    .items > li > span.name {
        color: darkgreen;
    }

    .items > li > span.price, span.sum, span.totalSum {
        color: darkred;
    }

    items > li > span.quantity {
        color: darkblue;
    }

    span.icon {
        color: #555555;
    }

    ul.actions, ul.actions li {
        display: inline-block;
        margin: 0;
        padding: 0;
    }
</style>
<style>
    ul.receipts > li {
        font-family: Tahoma;
        border: 1px solid #ccc;
        font-weight: bolder;
        color: 999;
    }

    ul.receipts > li > ol > li {
        border: none;
        font-weight: normal;
    }

    .items > li {
        text-transform: lowercase;
    }

    .items > li > span.name {
        color: darkgreen;
    }

    .items > li > span.price {
        color: darkblue;
    }

    items > li > span.quantity {
        color: darkred;
    }

    span.retailPlaceAddress {
        font-style: italic;
        font-weight: normal;
    }
</style>


<?php
echo $format == 'json' ? '			<pre class="hljs json">
    <code>' . $str . '
    </code>
</pre>' : $str;
?>
<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.13.1/build/styles/idea.min.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.13.1/build/highlight.min.js"></script>
<script>hljs.initHighlighting()</script>
