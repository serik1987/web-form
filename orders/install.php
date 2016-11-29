<?php

function read_all_file($filename){
	$handle = fopen($filename,'r');
	if (!$handle){
		global $error_msg;
		$error_msg="Ошибка чтения файла.";
		return "";
	}
	$content="";
	while ($line=fgets($handle)){
		$content.=$line;
	}
	fclose($handle);
	
	return $content;
}

function insert_piece($filename,$position,$piece){
	$content = read_all_file("..".$filename);
	if ($content==""){return;}
	if ($position=="----END----"){
		$content.="\n".$piece;
	} else {
		$content=str_replace($position,$piece."\n".$position,$content);
	}
	
	
	$handle = fopen("..".$filename,'w');
	if (!$handle){
		global $error_msg;
		$error_msg="Ошибка записи в файл ".$filename;
		return false;
	}
	if (!fwrite($handle,$content)){
		global $error_msg;
		$error_msg="Ошибка записи в файл ".$filename;
		return false;
	}
	fclose($handle);
	
	return true;
}


function set_sql_options(){
	$server = $_POST['server'];
	$login = $_POST['login'];
	$password = $_POST['password'];
	$db_name = $_POST['db_name'];
	
	$mysql = mysql_connect($server,$login,$password);
	if (!$mysql){
		global $error_msg;
		$error_msg="Ошибка! Не удалось подключиться к MySQL-серверу";
		return;
	}
	if (!mysql_select_db($db_name,$mysql)){
		global $error_msg;
		$error_msg="Ошибка! Не удалось загрузить нужную базу данных";
		return;
	}
	
	$query = read_all_file("install.sql");
	if ($query=="") {return;}
	
	$result = mysql_query($query);
	if (!$result){
		global $error_msg;
		$error_msg="Ошибка выполнения запроса: ".mysql_error();
	}
	
	mysql_close($mysql);
	
	
	
	$handle = fopen("info.php",'a');
	if (!$handle){
		global $error_msg;
		$error_msg="Ошибка! Не удалось открыть файл info.php";
		return;
	}
	$write_result = fwrite($handle,"\n") &&
	fwrite($handle,"\$server=\"".$server."\";\n") &&
	fwrite($handle,"\$login=\"".$login."\";\n") &&
	fwrite($handle,"\$password=\"".$password."\";\n") &&
	fwrite($handle,"\$db_name=\"".$db_name."\";\n");
	if (!$write_result){
		global $error_msg;
		$error_msg="Ошибка записи в файл info.php";
		return;
	}
	fclose($handle);
	
}


function set_info_php(){
	$handle = fopen("info.php",'a');
	if (!$handle){
		global $error_msg;
		$error_msg="Ошибка! Не удалось открыть файл info.php";
		return;
	}
	$write_result = fwrite($handle,"\$user_mail=\"".$_POST['mail']."\";\n") &&
	fwrite($handle,"\n") &&
	fwrite($handle,"\n") &&
	fwrite($handle,"?>\n");
	if (!$write_result){
		global $error_msg;
		$error_msg="Ошибка записи в файл info.php";
		return;
	}
	fclose($handle);
}



function set_template_modification(){
	$piece1 = read_all_file("index.php.insertion");
	if ($piece1=="") return;
	if (!insert_piece($_POST["index_php"],"</body>",$piece1)) return;
	
	$piece2 = read_all_file("template.css.insertion");
	if ($piece2=="") return;
	if (!insert_piece($_POST["template_css"],"----END----",$piece2)) return;
}





if ($_GET['id']!='t1509735'){
	exit("Resource not found.");
}

if (isset($_GET['action'])){
	switch ($_GET['action']){
		case '1':
			set_sql_options();
			if (!isset($error_msg)){
				$_GET['page']=2;
			} else $_GET['page']=1;
			break;
		case '2':
			set_info_php();
			if (!isset($error_msg)){
				$_GET['page']=3;
			} else $_GET['page']=2;
			break;
		case '3':
			set_template_modification();
			if (!isset($error_msg)){
				$_GET['page']=4;
			} else $_GET['page']=3;
			break;
			
	}
	
}

if (!isset($_GET['page'])){
	$_GET['page']=1;
}

?>

<html>
	<head>
		<title>Установка формы заказа для rfmebel.ru</title>
		<meta charset="utf-8"/>
		<style>
			body{
				padding: 10px;
				width: 600px;
			}
			p{
				text-align: justify;
			}
			
			label{
				min-width: 200px;
				display: inline-block;
			}
		</style>
	</head>
	<body>
	
		<?php
			
			if (isset($error_msg)){
				echo "<strong style=\"color: red;\">{$error_msg}</strong>";
			}
		
		?>
		
		
		<?php if ($_GET['page']==1): ?>
		<form action="install.php?id=<?php echo $_GET['id']; ?>&action=1" method="post">
			<h1>Шаг 1. Характеристики MySQL-сервера</h1>
		
			<p>Сейчас Вам надо ввести параметры Вашего MySQL-сервера. На нём будут храниться данные обо всех заказах. 
			Вы можете узнать эти параметры от Вашего хостинг-провайдера. В случае, если Вы пользуетесь услугами хостинга JINO,
			то Вы можете получить эти сведения, пройдя по ссылке: <a href="https://hosting.jino.ru/price/mysql/"></a></p>
			<label>Адрес MySQL-сервера:</label> <input type="text" required value="localhost:3306" name="server"/><br/>
			<label>Логин к MySQL-серверу:</label> <input type="text" required name="login"/><br/>
			<label>Пароль к MySQL-серверу:</label> <input type="password" name="password"/><br/>
			<label>Название базы данных, в которую следует помещать заказы:</label><br/>
			<input type="text" required name="db_name" size="50"/><br/>
			<p>Мы рекомендуем создать отдельную базу данных для размещения заказов.</p>
			<input type="submit" value="Далее >"/>
		</form>
		
		<?php elseif ($_GET['page']==2): ?>
		
		<h1>Шаг 2. Ваш e-mail.</h1>
		<form action="install.php?id=<?php echo $_GET['id']; ?>&action=2" method="post">
		<p>Введите Ваш e-mail. В случае правильной настройки Вашего Web-сервера на него будут высылаться уведомления
		о сделанных заказах</p>
			<label>Ваш e-mail: </label> <input type="email" name="mail" required/><br/>
			<p></p>
			<input type="submit" value="Далее >"/>
		</form>
		
		<?php elseif ($_GET['page']==3): ?>
		
		<h1>Шаг 3. Задать параметры шаблона.</h1>
		<form action="install.php?id=<?php echo $_GET['id']; ?>&action=3" method="post">
		<p>Теперь нам надо ввести небольшие изменения в файлы Вашего шаблона для того, чтобы кнопки "Сделать заказ" отображались
		под каждым Вашим товаром. Для этого нам нужны пути к двум файлам из шаблона.</p>
		<label>Путь к файлу index.php</label><input type="text" required value="/templates/zelgot/index.php" size="50" name="index_php"/><br/>
		<label>Путь к файлу template.css</label><input type="text" required value="/templates/zelgot/css/template.css" size="50" name="template_css"/><br/>
		<p>Введите путь к этим файлам, либо убедитесь в том, что этот путь введён корректно. Оба файла должны находиться в папке templates.
		Файлы, которые там не находятся, нас пока не интересуют.</p>
		<input type="submit" value="Далее>"/>
		</form>
		
		<?php elseif ($_GET['page']==4): ?>
		
		<h1>Шаг 4. Установка завершена.</h1>
		<p>Поздравляем. Вы только что установили новый плагин. Теперь Вы можете зайти по адресу:</p>
		<p><a href="http://rfmebel.com">http://rfmebel.com</a></p>
		<p>и убедиться, что всё работает</p>
		<p>Для того, чтобы просмотреть список всех сделанных заказов, обратитесь по адресу:</p>
		<p><a href="http://rfmebel.com/orders/index.php?id=dWuPDQ6QNQ9E5D4C9mb6jwbysrGmPNKTmPyPwuf9UfrAvj3fvPWrWXNYe6aEvLpGrL7xQYhAQyMaGr2N6N4DCttt9tWuPbL6yvdejmEYJgK3yCse2kuepLx8P6svS4rf">
		http://rfmebel.com/orders/index.php?id=dWuPDQ6QNQ9E5D4C9mb6jwbysrGmPNKTmPyPwuf9UfrAvj3fvPWrWXNYe6aEvLpGrL7xQYhAQyMaGr2N6N4DCttt9tWuPbL6yvdejmEYJgK3yCse2kuepLx8P6svS4rf</a></p>
		
		<?php endif; ?>
		
		
	</body>
</html>