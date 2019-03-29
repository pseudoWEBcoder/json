<?php
error_reporting(E_ERROR);
require 'vendor/autoload.php';
require 'functions.php';

use RedBeanPHP\R;

R::setup('sqlite:' . realpath($df = dirname(__FILE__) . '/db/db4.db'));
R::setAutoResolve(true);
pre(['datebase'=>$df],false);
RedBeanPHP\R::debug( true,2);
//$queryLogger = RedBeanPHP\Logger\RedBean_Plugin_QueryLogger::getInstanceAndAttach(R::getDatabaseAdapter());

$ReceiptTable = 'receipt';
$itemsTable = 'items';
$commentTable = 'comment';
$statusTable='status';
$categoryTable='category';

$Receipt = R::dispense($ReceiptTable);
$Comments = R::dispense($commentTable);
$Status = R::dispense($statusTable);
$Category = R::dispense($categoryTable);
$allitems = R::findAndExport($itemsTable);
$dir = '/sdcard/Download/';
//$dir = 'C:\Users\nik-msk-win10\Desktop\json';
$dirname = realpath($dir);
$action = $_REQUEST['action'];
if ($action == '')
    $action = 'add';
switch ($action) {
    case 'add':
        echo render(['title' => 'добавление товаров', 'body' => form_add()]);
        break;
    case 'list':
        echo render(['title' => 'список товаров', 'body' => '<table class="table" id="table">' . table() . '</table>']);
        break;
    case 'commit':
        $Receipt = R::load($itemsTable, $_REQUEST['id']);
        $Receipt->commit = time();
        if (R::store($Receipt))
            header('Location:' . $_SERVER['PHP_SELF'] . '?' . http_build_query(['action' => 'list']));
        break;
case 'comment':
$Item= R::load($itemsTable, $_REQUEST['id']);
        $Comments->time= time();
$Comments->text='комментарий';
$Item->ownCommentList[]=$Comments;
        $Status->time= time();
$Status->text='OK';
$Item->statusList[]=$Status;
$Category->time= time();
$Category->text='OK';
$Item->categoryList[]=$Category;        
        if (R::storeAll([$Item,$Status,$Category]))
	
            header('Location:' . $_SERVER['PHP_SELF'] . '?' . http_build_query(['action' => 'list']));
        break;

}

class Items
{
	function td($val )
    {
        return '<td>' .$val.'</td>';
    }
	function render_td_created($row){
		if(is_numeric($row['created']))
			return self::td(date('d.m.Y',$row['created']));
			}
			function render_td_updated($row){
		if(is_numeric($row['updated']))
			return self::td(date('d.m.Y',$row['updated']));
			}
    function render_row($row)
    {
        if (isset($row['commit']))
            return '<tr class="table-success">';
    }

    function render_td_price($row)
    {
        return self::sum($row, 'price');
    }

    function sum($row, $key)
    {
        return '<td>' . number_format((float)$row[$key] / 100, 2, '.', ' ');
    }

}

function table($str = true)
{
	//pre(['pre(class_exists(\'RedBeanPHP\R\').'=>class_exists('RedBeanPHP\R'),'+'=>0,'line'=>__LINE__]);
	 $all = R::findAndExport($GLOBALS['itemsTable'], 'WHERE `commit` is NULL  ORDER BY  id DESC');
 $all = R::find($GLOBALS['ReceiptTable'],' LIMIT 100');
$fields[$GLOBALS['itemsTable']]=$ifields=R::inspect($GLOBALS['itemsTable']);
$fields[$GLOBALS['ReceiptTable']]=$rfields=R::inspect($GLOBALS['itemsTable']);
//pre($all[31]->ownItemsList,false);
//pre(['all'=>$all,  $GLOBALS['itemsTable']=>R::find($GLOBALS['itemsTable'])/*,'sql'=>$GLOBALS['queryLogger']->getLogs()*/]);
    $tr[] = '<tr>';
    $tr[] = '<th>';
    $tr[] = '';
    $tr[] = '</th>';
    foreach (array_keys($ifields) as $i => $td) {
        $tr[] = '<th>';
        $tr[] = $td;
        $tr[] = '</th>';
    }
    $tr[] = '</tr>';
    foreach ($all as $index => $Row) {
	 foreach ($Row->ownItemsList as $index => $row) {
//	ownItemsList
//pre($row);
        if (is_callable([Items, 'render_row']))
            $tr[] = call_user_func_array([Items, 'render_row'], [$row]); else
            $tr[] = '<tr>';
        $tr[] = '<td>';
        $tr[] = '<a href="?' . http_build_query(['action' => 'commit', 'id' => $row['id']]) . '" class="btn btn-dsuccess"><i class="fas fa-check"></i></a>';
       
        $tr[] = '<a href="?' . http_build_query(['action' => 'comment', 'id' => $row['id']]) . '" class="btn btn-success" data-toggle="modal" data-target="#ModalLong"><i class="fas fa-comment"></i>'.(count($row->ownCommentList)).'</a>';
         
$tr[] = '</td>';
        foreach ($row as $i => $td) {
            if (is_callable([Items, $func = 'render_td_' . $i]))
                $tr[] = call_user_func_array([Items, $func], [$row]); else {
                $tr[] = '<td>';
                $tr[] = $td;
                $tr[] = '</td>';
            }
        }
        $tr[] = '</tr>';
    }
}
    return $str ? implode('', $tr) : $tr;
}
function pre($arr,$die=true){

    echo '<pre>';
    var_dump($arr);
    echo '</pre>';if($die)die;
}
function render_select($dirname)
{
    $all = scandir($dirname);

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
    return [$filtered, $options];

}

function addItems()
{
    set_time_limit(0);
    $file = $GLOBALS['dir'] . '/' . $_REQUEST['file'] . '.json';
    if (!file_exists($file))
        throw new Exception($file . ' файл не существует');
    $json = json_decode(file_get_contents($file), 1);
$existscheckbox=get('notexists');
$checkexists=$existscheckbox=='on';
    foreach ($json as $irecipt => $recipt) {
	if($checkexists)
      {  $exists = R::find($GLOBALS['ReceiptTable'], ' `kkt_reg_id`=? AND `fiscal_document_number` =? AND fiscal_drive_number=? AND total_sum=?', [$recipt["document"]["receipt"]["kktRegId"], $recipt["document"]["receipt"]["fiscalDocumentNumber"], $recipt["document"]["receipt"]["fiscalDriveNumber"], $recipt["document"]["receipt"]["totalSum"]]);
       
	if ($exists)
            {continue;}
}
        $RECIPT = R::dispense($GLOBALS['ReceiptTable']);
        $RECIPT->created = time();
        $RECIPT->updated = time();
$RECIPT->commit=null;
        foreach ($recipt["document"]["receipt"] as $index => $Item) {
            if ($index == 'items') {
                foreach ($Item as $inemindex => $item) {
                    $ITEM = R::dispense($GLOBALS['itemsTable']);

                    $ITEM->created = time();
                    $ITEM->updated = time();
                    foreach ($item as $i => $v) {
                        if (!in_array(gettype($v), ['string', 'integer']))
                            $ITEM->{simple($i)} = json_encode($v); else
                            $ITEM->{simple($i)} = $v;
                    }

                    $RECIPT->ownItemsList[] = $ITEM;
                }
            } else {
                if (!in_array(gettype($Item), ['string', 'integer']))
                    $RECIPT->{simple($index)} = json_encode($Item);
                else
                    $RECIPT->{simple($index)} = $Item;
            }

        }
        $result[] = R::store($RECIPT);

    }
    return $result;
}

function simple($str)
{
    return $str;// strtolower(preg_replace('/[^A-Za-z]+/',  '', $str));
}

function form_add()
{
    if (isset($_REQUEST['add']))
	try{
        return pre(addItems());}
catch(Exception $e){
pre($e-getMessage()); 
}
    return '<div><div class="container">
	<form>
		<div class="form-group row">
			<label for="inputName" class="col-sm-6 col-form-label">комментарий</label>
			<div class="col-sm-6">
				<input type="text" class="form-control" name="comment" id="comment" placeholder="">
			</div>
		</div>
			<div class="form-group row">
			<label for="notexists" class="col-sm-6 col-form-label">проверять и пропускать сушествующие</label>
			<div class="col-sm-6">
			<div class=checkbox">
				<input type="checkbox" class="form-control" name="notexists" id="notexists"checked="checked">
			</div>
			</div>
		</div>
		<fieldset class="form-group row">
			<legend class="col-form-legend col-sm-6">добавление выписки</legend>
			<div class="col-sm-1-12">
				<select name="file" id="">' . implode(PHP_EOL, render_select($GLOBALS['dirname'])[1]) . '</select>
			</div>
		</fieldset>
		<div class="form-group row">
			<div class="offset-sm-2 col-sm-6">
				<input name="add" type="submit" class="btn btn-primary">Добавить</input>
			</div>
		</div>
	</form>
</div></div>';

}

function render($args)
{
    extract($args);
    return "<!doctype html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\"
          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    <title>$title</title>
    <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css\"
          integrity=\"sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ\" crossorigin=\"anonymous\">
</head>
<body>
$body"
.
'<!-- Modal --> <div class="modal fade ajaxmodal" id="ModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true"> <div class="modal-dialog" role="document"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5> <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> </div> <div class="modal-body"> ... </div> <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" class="btn btn-primary">Save changes</button> </div> </div> </div> </div>
'."
<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js\"></script>
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js\"></script>
<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js\"
        integrity=\"sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn\"
        crossorigin=\"anonymous\"></script>
<script>jQuery(function(){
$('.modal.ajaxmodal').on('hidden.bs.modal', function (e) { 
alert('закрыто):
})
})</script>
        <link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.6.3/css/all.css\" integrity=\"sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/\" crossorigin=\"anonymous\">
</body>
</html>";
}
