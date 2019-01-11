<!doctype html>
<head>
  <meta charset="utf-8">
  <title>test work</title>
  <link rel="stylesheet" href="chosen/chosen.css">
</head>
<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<?php
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

/*---DB CONNECT---*/
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
		
		$all_obls = mysqli_query($link, 'SELECT ter_name,reg_id FROM t_koatuu_tree WHERE ter_level = 1');		
	}
}
/*---DB CONNECT---*/
?>
<form>
	<label for="name">ФИО:</label>
	<input type="text" id="name" name="name" size="10">
	<br><br>
	<label for="email">Email:</label>
	<input type="text" id="email" name="email" size="10">
	<br><br>
	<label for="reg_obl">Область:</label>
	<select name="reg_obl">
		<?php 
			foreach($all_obls as $row){
				if($row["reg_id"] !== '80' AND $row["reg_id"] !== '85'){
					echo "<option data-ter_id='".$row["reg_id"]."' >";
						echo $row["ter_name"];
					echo "</option>";			
				}				
			}
		?>
	</select>
	<br><br>
	<label for="reg_city">Город:</label>
	<select name="reg_city">
		<option>Выберите сначала область<option>		
	</select>
	<br><br>	
	
	<label for="reg_aria">Район:</label>
	<select name="reg_aria">
		<option>Выберите сначала город<option>
	</select>
	<br><br>
	<button data-action="Register">Register</button>
</form>
<br><br>
<div id='ress_msg' style='padding: 5px; border: 2px solid black;'></div>




<script src="chosen/chosen.jquery.js" type="text/javascript"></script>
<script>
	jQuery( document ).ready(function() {
		jQuery("select").chosen();
		
		
		/*---GET_CITY---*/
		jQuery('select[name="reg_obl"]').change(function(){
			jQuery('select[name="reg_city"]').html('<option>Сначала выберите область.</option>');
			jQuery('select[name="reg_aria"]').html('<option>Сначала выберите город.</option>');			
			
			var curren_obl_id = jQuery(this).find(':selected').attr('data-ter_id');			
			
			var request = jQuery.ajax({
			  url: "/ajax.php?get_city=1",
			  type: "POST",
			  data: {curren_obl_id : curren_obl_id},
			  dataType: "html"
			});			

			request.done(function(msg) {
			  jQuery('select[name="reg_city"]').html(msg);
			  jQuery("select").trigger("chosen:updated");
			});
			
			request.fail(function(msg) {
			  alert( "Request failed!");
			});			
		});		
		/*---GET_CITY---*/
		
		/*---GET_C_ARIA---*/
		jQuery('select[name="reg_city"]').change(function(){			
			var ter_level = jQuery(this).find(':selected').attr('data-ter_level');	
			var ter_type_id = jQuery(this).find(':selected').attr('data-ter_type_id');	
			var city_name = jQuery(this).find(':selected').val();	
			var curren_obl_id = jQuery('select[name="reg_obl"]').find(':selected').attr('data-ter_id');	
		
		
			if(ter_level == 2 && ter_type_id == 1){			
				var request = jQuery.ajax({
				  url: "/ajax.php?get_city_areas=1",
				  type: "POST",
				  data: {curren_obl_id : curren_obl_id, city_name: city_name},
				  dataType: "html"
				});			

				request.done(function(msg) {
				  jQuery('select[name="reg_aria"]').html(msg);		
				  jQuery("select").trigger("chosen:updated"); 
				});
				
				request.fail(function(msg) {
				  alert( "Request failed!");
				});
			}else{
				jQuery('select[name="reg_aria"]').html("<option>Нет районов для данного города.</option>");	
				jQuery("select").trigger("chosen:updated"); 				
			}	
		});
		/*---GET_C_ARIA---*/
		
		/*---REGISTER NEW USER---*/
		jQuery('button[data-action="Register"]').click(function(ev){
			ev.preventDefault();
			jQuery('#ress_msg').html('');

			/*-----VALIDATION----*/
			var user_name  = jQuery('#name').val();//GET VALUE			
			var user_email = jQuery('#email').val();//GET VALUE			
			
			
			var arias_avalibale = jQuery("select[name='reg_aria'] option").length; 
			
			
			if(arias_avalibale > 1){
				var address_id = jQuery('select[name="reg_aria"]').find(':selected').attr('ter_id');//GET VALUE
			}else{
				var address_id = jQuery('select[name="reg_city"]').find(':selected').attr('ter_id');//GET VALUE
			}	
			
			
			var ress_msg = '';
			if(user_name.length < 6){
				ress_msg += "ФИО слишком короткое<br>";
			}			
			var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;			
			if(!regex.test(user_email)){
				ress_msg += "Email не верный<br>";
			}	
			if(!address_id){
				ress_msg += "Адрес не заполнен<br>";
			}			
			
			jQuery('#ress_msg').html(ress_msg);
			/*-----VALIDATION----*/
			
			/*---REGISTER AJAX---*/
			if(ress_msg === ''){
				var request = jQuery.ajax({
				  url: "/ajax.php?reg_user=1",
				  type: "POST", 
				  data: {user_name: user_name, user_email: user_email, address_id: address_id}, 
				  dataType: "html"
				});

				request.done(function(msg) {
				  jQuery('#ress_msg').html(msg);			  
				});
				
				request.fail(function(msg) {
				  alert( "Request failed!");
				});
			}
			/*---REGISTER AJAX---*/
		});		
		/*---REGISTER NEW USER---*/
	});
</script>
</body>