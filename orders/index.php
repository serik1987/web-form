<?php

	/**************************************************************/
	/*                                                            */
	/*  Order list                                                */
	/*                                                            */
	/* (C) Sergei Kozhukhov, 2016                                 */
	/*                                                            */
	/**************************************************************/


	if ($_GET['id']!="dWuPDQ6QNQ9E5D4C9mb6jwbysrGmPNKTmPyPwuf9UfrAvj3fvPWrWXNYe6aEvLpGrL7xQYhAQyMaGr2N6N4DCttt9tWuPbL6yvdejmEYJgK3yCse2kuepLx8P6svS4rf"){
		exit("Address you entered is incorrect.");
	}
	
	require_once('info.php');
	$mysql = mysql_connect($server,$login,$password);
	if (!$mysql){
		exit("Error in request to database. Reinstall the framework.");
	}
	if (!mysql_select_db($db_name)){
		mysql_close($mysql);
		exit("Error in connect to database");
	}
	$result = mysql_query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 100;");
	if (!$result){
		echo mysql_error();
		mysql_close($mysql);
		exit("Error in SQL query");
	}
	mysql_close($mysql);
?>


<html lang="ru">
	<head>
		<meta charset="utf-8"/>
		<title>rfmebel - список сделанных заказов</title>
		<style>
			td{
				padding: 5px;
			}
			tr:not(:first-child) td:first-child{
				text-align: center;
				font-size: 22pt;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		<header>
			<h1>Список последних заказов</h1>
		</header>
		<article>
			<?php if ($_GET['first']=='yes'): ?>
			
			<div style="color:red;">Не забудьте добавить эту страницу в избранное, либо записать её адрес; иначе Вы не сможете на неё перейти.
			</div>
			
			<?php endif; ?>
		
			<table border="1">
				<tr>
					<td>Номер заказа</td>
					<td>Информация о заказе</td>
				</tr>
				<?php $order_exists=false; ?>
				<?php while ($row=mysql_fetch_assoc($result)):?>
				<?php $order_exists=true; ?>
				<tr>
					<td><?php echo $row['order_id']; ?></td>
					<td>
						<ul>
							<li><strong>Имя клиента:</strong> <?php echo $row['client_name'];?></li>
							<li><strong>Его телефон:</strong> <?php echo $row['client_phone'];?></li>
							<li><strong>Его e-mail:</strong> <?php echo $row['client_mail'];?></li>
							<li><strong>Название товара:</strong> <?php echo $row['good_name'];?></li>
							<li><strong>Размеры:</strong> <?php echo $row['good_size'];?></li>
							<li><strong>Цвет:</strong> <?php echo $row['good_color'];?></li>
							<li><strong>Адрес доставки и комментарии:</strong> <?php echo $row['order_comments'];?></li>
						</ul>
					</td>
				</tr>
				<?php endwhile;?>
				
				<?php if (!$order_exists): ?>
				<tr></tr>
				<td colspan="2">В базе данных нет заказов.</td>
				<?php endif;?>
			</table>
		</article>
	</body>
</html>