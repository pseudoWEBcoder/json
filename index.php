<?php
error_reporting(E_ERROR);

$dir='/sdcard/Download/5bc6505838ded6528397fac1.json';
$dir='C:\Users\nik-msk-win10\Desktop\json';
$dirname=dirname($dir);
if(isset($_REQUEST['file'])) {
	$file =$_REQUEST['file'];
	$format=$_REQUEST['format'];
	if(is_file($dir))
		$str=$format=='json'?execute($dirname.'/'.$file.'.json')
:			execute2($dirname.'/'.$file.'.json');
	} else {
		$str='не найдено';
	}
	$all = scandir($dirname);
	//dump($all);
	//echo '<hr>';
	foreach($all as $k=>$v) {
		if(file_exists($r=realpath($dirname.'/'.$v))&&($inf=pathinfo($v))&&isset($inf['extension'])&&$inf['extension']=='json')
$filtered[filemtime($r)]=$inf['filename'];
	}
	krsort($filtered,SORT_NUMERIC);
	//dump([basename($file,'.json'),$filtered]);

	foreach($filtered as $k=>$v) {
		$checked = ($file==$v)?' selected="selected"':'';
		$options[]="<option value=\"$v\"$checked >".date('d.m.Y h:i:s',$k)." - $v</option>";
	}
	//dump($options);

	function dump ($arr) {
		echo '<pre>';
		var_dump($arr);
		echo '</pre>';
	}
	function execute($path) {
		$c=file_get_contents($path);
		$arr=json_decode($c,true);
		array_walk_recursive($arr, function(&$v,$k) {
			if(is_numeric($v)&&in_array($k,['price','totalSum','sum','cashTotalSum','ecashTotalSum']))
$v=(float)$v/100;
 			//number_format($v/100, 2, '.', ' ');
		}
);
		return json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
	}

	function execute2($path) {
		$c=file_get_contents($path);
		$arr=json_decode($c,true);
 		uksort($arr, 'cmp');
		array_walk_recursive($arr, function(&$v,$k) {
			if(is_numeric($v)&&in_array($k,['price','totalSum','sum','cashTotalSum','ecashTotalSum']))
$v=(float)$v/100;
 			//number_format($v/100, 2, '.', ' ');
		}
);
		$ul ='';
		$icon=[
'before'=>['name'=>'', 'price'=>' ', 'quantity'=>'&times;&nbsp;','sum'=>'='],
'after'=>['name'=>'', 'price'=>'&#x20bd; ', 'quantity'=>'','sum'=>'&#x20bd']];
		foreach($arr as $k=>$v) {
			$r=$v['document']['receipt'];
			$ul.='<li>';
			foreach(['dateTime', 'totalSum', 'user'] as $i)
$ul.=' <span class="'.$i.'">'.get($i, $r).'</span>';
			$ul.='<ol class="items">';
			foreach(get('items', $r) as $I=>$item) {
				$ul.='<li start=1>';
				foreach(['name', 'price', 'quantity','sum'] as $j)
$ul.="\t".'<span class="'.$j.'">'.get($j, $icon['before']).get($j, $item).get($j, $icon['after']).'</span>';
				$ul.='</li>';
			}
			$ul.='</ol>';
			$ul.='</li>';

		}
		return '<ul class="receipts">'.$ul.'</ul>';
	}
	function cmp($a, $b) {
		$date1 = DateTime::createFromFormat
('Y-m-dTh:i:s',$a['dateTime']);
		$date2 = DateTime::createFromFormat('Y-m-dTh:i:s',$b['dateTime']);
		dump(get_defined_vars());
		die(__FILE__);
		return $date1&& $date2? (int)($date2->format('u')- $date1->format('u')):false;
	}

	function get($key,$arr=null,$default=null) {
		$arr=$arr??$_REQUEST;
		return isset($arr[$key])?$arr[$key]:$default;
	}
	?>
		<form method=post>
			<select type="text" requred=true name="file">
		<?php
		echo implode(PHP_EOL,$options);
		?>
			</select>
			<input type="submit" value="отправить ">
			</input>
			<label for="json" >
				<input type="radio" value="json" name="format">
				</input>json
			</label>
			<label for="ul" >
				<input type="radio" value="ul" name="format">
				</input>ol
			</label>
		</form>
			<style>
			ul.receipts>li{font-family:Tahoma; border :1px solid #ccc; font-weight:bolder;color:999;}
			ul.receipts>li>ol>li{ border:none ; font-weight:normal;}
			.items>li{text-transform:lowercase;}
			.items>li>span.name{color:darkgreen;}
			.items>li>span.price{color:darkblue;}
			items>li>span.quantity{color:darkred;}
			</style>
				<style>
					ul.receipts>li
					{
						font-family : Tahoma;
						border : 1px solid #ccc;
						font-weight : bolder;
						color : 999;
					}
					
					ul.receipts>li>ol>li
					{
						border : none;
						font-weight : normal;
					}
					
					.items>li
					{
						text-transform : lowercase;
					}
					
					.items>li>span.name
					{
						color : darkgreen;
					}
					
					.items>li>span.price
					{
						color : darkblue;
					}
					
					items>li>span.quantity
					{
						color : darkred;
					}
						</style>
							</style>
							
							<?php
							echo $format=='json'? '			<pre class="hljs json">
								<code>'.$str.'
								</code>
							</pre>':$str;
							?>
								<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.13.1/build/styles/idea.min.css">
									<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js">
									</script>
										<script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.13.1/build/highlight.min.js">
										</script>
											<script>
											hljs.initHighlighting()
											</script>
												<script>
													hljs.initHighlighting()
														</script>