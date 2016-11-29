<?php


/**************************************************************/
/*                                                            */
/*  Order form                                                */
/*                                                            */
/* (C) Sergei Kozhukhov, 2016                                 */
/*                                                            */
/**************************************************************/







header('Content-Type: application/json');


$result = update_db();

function update_db(){
	
	global $data;

	$_GET['id'] = "dWuPDQ6QNQ9E5D4C9mb6jwbysrGmPNKTmPyPwuf9UfrAvj3fvPWrWXNYe6aEvLpGrL7xQYhAQyMaGr2N6N4DCttt9tWuPbL6yvdejmEYJgK3yCse2kuepLx8P6svS4rf";
	require_once('info.php');
	$mysql = mysql_connect($server,$login,$password);
	if (!$mysql){
		$result['code'] = 1;
		$result['message'] = "Заказ не добавлен, так как случилась ошибка доступа к базе данных";
		return $result;
	}
	if (!mysql_select_db($db_name)){
		$result['code'] = 1;
		$result['message'] = "Заказ не добавлен, так как случилась ошибка доступа к базе данных";
		return $result;
	}

	$data['client_name'] = mysql_real_escape_string($_POST['client_name']);
	$data['client_phone'] = mysql_real_escape_string($_POST['client_phone']);
	$data['client_mail'] = mysql_real_escape_string($_POST['client_mail']);
	$data['good_name'] = mysql_real_escape_string($_POST['good_name']);
	$data['good_size'] = mysql_real_escape_string($_POST['good_size']);
	$data['good_color'] = mysql_real_escape_string($_POST['good_color']);
	$data['order_comments'] = mysql_real_escape_string($_POST['order_comments']);
	
	$client_mail = $data['client_mail'];

	$query = "INSERT INTO orders VALUES (NULL,'".$data['client_name']."','".$data['client_phone']."','".
		$data['client_mail']."','".$data['good_name']."','".$data['good_size']."','".
		$data['good_color']."','".$data['order_comments']."');";

	$sql_result = mysql_query($query);
	if (!$sql_result){
		$result['code'] = 1;
		$result['message'] = "В запросе ".$query." возникла ошибка ".mysql_error();
		return $result;
	}
	
	$sql_result = mysql_query("SELECT LAST_INSERT_ID();");
	if (!$sql_result){
		$result['code'] = 1;
		$result['message'] = "В запросе ".$query." возникла ошибка ".mysql_error();
		return $result;
	}

	$row = mysql_fetch_array($sql_result);
	$result['code'] = 0;
	$result['message'] = "Ваш заказ № ".$row[0]." сделан успешно";
	$data['order_id'] = $row[0];


	mysql_close($mysql);
	
	
	$msg = <<<MSG
	
Добрый день!

Только что был сделан заказ номер {$data['order_id']}.

Имя заказчика: {$_POST['client_name']}
Телефон заказчика: {$_POST['client_phone']}
E-mail заказчика: {$_POST['client_mail']}

Название товара: {$_POST['good_name']}
Требуемые размеры: {$_POST['good_size']}
Цвет: {$_POST['good_color']}

Адрес и дополнительная информация:
{$data['order_comments']}

С наилучшими пожеланиями,
Автоматическая система регистрации заказов rfmebel.ru
	
MSG;
	mail($user_mail,"Сделан заказ № ".$data['order_id'],$msg);
	

	return $result;
}

/*   Sending e-mail confirmation to the client mail  */

if ($data['client_mail']!=''):

$msg = <<<MSG

Глубокоуважаемый {$_POST['client_name']}!

Вы только что сделали заказ на изготовление мебели
"{$_POST['good_name']}",
цвет {$_POST['good_color']}, размеры {$_POST['good_size']}.

Номер Вашего заказа: {$data['order_id']}.

В ближайшее время мы с Вами свяжемся.

C уважением,
Компания "РФ Мебель"

MSG;

mail($data['client_mail'],"Заказ на доставку мебели №".$data['order_id'],$msg);

endif;

ob_clean();
echo json_encode($result);


?>