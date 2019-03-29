<?php
$path= '../goods/yii2-app-advanced';
$realpath=realpath('../goods/yii2-app-advanced');
$goods=scandir($realpath);
dump(get_defined_vars());
	function dump($var){
echo '<pre>';
var_dump($var);
echo '</pre>';
}
?>