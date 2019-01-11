<?php
/*--DB CONNECT--*/
/*---DATA BASE CONFIGS---*/
	define('DB_NAME', 'nightmar_maxburs');
	/** MySQL database username */
	define('DB_USER', 'nightmar_maxburs');
	/** MySQL database password */
	define('DB_PASSWORD', '8*@C4ruPs9');
	/** MySQL hostname */
	define('DB_HOST', 'nightmar.mysql.tools');
	/** Database Charset to use in creating database tables. */
	define('DB_CHARSET', 'utf8');
	/** The Database Collate type. Don't change this if in doubt. */
	define('DB_COLLATE', '');
/*---DATA BASE CONFIGS---*/


$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD);
if (!$link) {
	echo "<br>Error: Unable to connect to MySQL." . PHP_EOL;
	echo "<br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	echo "<br>Debugging error: " . mysqli_connect_error() . PHP_EOL;
	exit;
}else{
	$db_res = mysqli_select_db($link, DB_NAME);
	if($db_res === false){
		echo "<br>Cann't choose DB ".DB_NAME;
		exit;
	}else {
		mysqli_set_charset($link, "utf8");			
	}
}
/*--DB CONNECT--*/



/*----AJAX_handler_for_get_aria----*/
if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_GET['get_city'] == 1){	
	$ress_html = '';
	if(isset($_POST['curren_obl_id'])){
		$id = $_POST['curren_obl_id'];
	}else {
		exit('error: ID is missing');
	}	
	
	$all_arias = mysqli_query($link, "SELECT ter_name,ter_id,ter_level,ter_type_id FROM t_koatuu_tree WHERE reg_id = $id AND ter_level > 1 AND ter_type_id != 3") or die(mysqli_error($link));//GET BY reg_id = 01
	
	
	$ress_html .= "<option>";
	$ress_html .=  'Выберите город';
	$ress_html .=  "</option>";
	
	foreach($all_arias as $row){
		$ress_html .= "<option data-ter_level='".$row['ter_level']."' data-ter_type_id='".$row['ter_type_id']."' ter_id='".$row['ter_id']."' >";
			$ress_html .=  $row["ter_name"];
		$ress_html .=  "</option>";
	}
			
	echo $ress_html;
	exit();
}
/*----AJAX_handler_for_get_aria----*/


/*----AJAX_handler_for_get_city----*/
if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_GET['get_city_areas'] == 1){	
	$ress_html = '';
	if(isset($_POST['curren_obl_id'])){
		$id = $_POST['curren_obl_id'];
	}else {
		exit('error: ID is missing');
	}
	if(isset($_POST['city_name'])){
		$city_name = $_POST['city_name'];
	}
	
	
	$all_city_arias = mysqli_query($link, "SELECT ter_name,ter_address,ter_id,ter_level,ter_type_id FROM t_koatuu_tree 
	WHERE reg_id = $id AND ter_level = 3 AND ter_type_id = 3 AND ter_address LIKE '%$city_name%' ") or die(mysqli_error($link));//GET BY reg_id = 01 
	
	
	if($all_city_arias->num_rows !== 0){
		$ress_html .= "<option>";
		$ress_html .=  'Выберите район';
		$ress_html .=  "</option>";
		
		foreach($all_city_arias as $row){
			$ress_html .= "<option ter_id='".$row['ter_id']."' >";
				$ress_html .=  $row["ter_name"];
			$ress_html .=  "</option>";
		}
	}else{
		$ress_html .=  "<option>Нет районов для данного города.</option>";
	}
			
	echo $ress_html;
	exit();
}
/*----AJAX_handler_for_get_city----*/


/*----AJAX_handler_for_user_registration----*/
	if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_GET['reg_user'] == 1){	
		$ress_html = '';
	
		$sql_check_table = "SHOW TABLES LIKE 'reg_users'";
		$check_table = mysqli_query($link, $sql_check_table)  or die(mysqli_error($link));
				
		function validate_data($data){
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = strip_tags($data);
		  $data = htmlspecialchars($data);
		  return $data;    
		}		
		$name = validate_data($_POST['user_name']);
		$email = validate_data($_POST['user_email']);
		$territory = validate_data($_POST['address_id']);
	
		if($check_table->num_rows > 0){	
			$check_email_sql = "SELECT * FROM `reg_users` WHERE UEMAIL LIKE '%$email%'";			
			$new_user_sql = "INSERT INTO `reg_users` (UNAME, UEMAIL, TERRITORY) VALUES ('$name', '$email', '$territory')";
			$check_email = mysqli_query($link, $check_email_sql)  or die(mysqli_error($link));			
			
			if($check_email->num_rows > 0){//WE ALLREADY HAVE USER WITH THIS EMAIL
				$ress_html .= 'Уже есть пользователь с таким Емаил<br>';
				foreach($check_email as $user){
					$ress_html .= "ID: ".$user['ID']."<br>";
					$ress_html .= "Имя: ".$user['UNAME']."<br>";
					$ress_html .= "Емаил: ".$user['UEMAIL']."<br>";
					$ress_html .= "Ид адресса( лень дрегать из БД строчку, а харнить лучше ID ): ".$user['TERRITORY']."<br>";
				}				
				
			}else{//ADD NEW USER
				$new_user_ress = mysqli_query($link, $new_user_sql) or die(mysqli_error($link)) ;			
				$ress_html .= 'Новый пользователь добавлен';
			}
			
		}else{			
			$create_table_sql = "CREATE TABLE IF NOT EXISTS reg_users (
				ID INT AUTO_INCREMENT,
				PRIMARY KEY(ID),
				`UNAME` VARCHAR(30),
				`UEMAIL` VARCHAR(30),
				`TERRITORY` VARCHAR(30)			
			)";
			
			mysqli_query($link, $create_table_sql) or die(mysqli_error($link));			
			$ress_html .= 'DB TABLE WAS CREATED';	
		}
		echo $ress_html;
		exit();		
	}
/*----AJAX_handler_for_user_registration----*/
?>