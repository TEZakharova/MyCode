<?php include "authentification_write.php"; ?>
<!DOCTYPE HTML>
<meta charset="ISO-8859-5">
<HTML>
	<Head>
		<Title>Edit | IT Products</Title>
		<link rel="stylesheet" type="text/css" href="style/edit.css">
		<link rel="shortcut icon" href="resources/favicon.ico" type="image/x-icon">
		<link rel="icon" href="resources/favicon.ico" type="image/x-icon">
	</Head>
	<Body>
		<!----------------------------------->
		<!--Header-->
		<!----------------------------------->
		<?php
			$prod = $_GET["product"];
			$sql_rus_name = "SELECT rus_prod_name FROM Product
						JOIN Publisher ON product.pub_id=Publisher.pub_id
						WHERE Product.prod_name=:product";
			try{
				$stmt_rus_name = $conn->prepare($sql_rus_name);
				$stmt_rus_name->bindParam(':product', $prod);
				//Execute SQL Command
				$stmt_rus_name->execute();

				//Fetch results
				$result_rus_name = $stmt_rus_name->fetchAll(PDO::FETCH_ASSOC);
				$db_rus_prod_name = $result_rus_name[0]['rus_prod_name'];
			}			
			catch(PDOException $e){
				$_SESSION['alert'] = "error";
				$_SESSION['alert_txt'] = "Connection failed: " . $e->getMessage();
				header("Location: list.php");
			}
		?>
		<h1><?php echo $_GET["product"]; if($db_rus_prod_name != ""){echo ' / '.$db_rus_prod_name.'';}?></h1>
		<div id="body">
			<form method="post" action="sql_update.php" enctype="multipart/form-data">
				<table id="ProbNameTable">
<!--------------------------------------------------------------------------------------------------------------------------->
<!--------------------------Database query-------------------------->
<!--------------------------------------------------------------------------------------------------------------------------->
					<?php
						//Get product name
						if(isset($_GET["product"])){
							$prod = $_GET["product"];
						}else if(isset($_POST["product"])){
							$prod = $_POST["product"];
						}

						//SQL Queries
						$sql_gen = 	"SELECT p.*, pb.*, a.* FROM Product AS p
								JOIN Publisher AS pb ON p.pub_id=pb.pub_id
								LEFT JOIN Accounts AS a ON p.account_id = a.acc_id
								WHERE p.prod_name=:product";
						$sql_hdw = 	"SELECT Server.srv_id, os, role, ip, hostname FROM Server
								JOIN Product_Server ON Product_Server.srv_id=Server.srv_id
								JOIN product ON product.prod_name=Product_Server.prod_name
                                WHERE product.prod_name=:product";
                        $sql_soft = "SELECT Soft_Id, Soft_Name, Soft_Address, Soft_Desc
								FROM Product_Software
								INNER JOIN Product ON Product.prod_name = Product_Software.prod_name
								WHERE Product.prod_name = :product"; 
						$sql_con = 	"SELECT file_link, file_id FROM Files
								WHERE prod_name=:product AND file_type='contract'";
						$sql_sys_user = "SELECT sys_user_id, owner, details, comment, belong FROM SystemUser
								JOIN Product ON Product.prod_name=SystemUser.prod_name
								WHERE Product.prod_name=:product"; 
						$sql_activity = "SELECT Id, activity_id FROM Product_Activity
								JOIN Product ON Product.prod_name=Product_Activity.prod_name
								WHERE Product.prod_name=:product"; 
						$sql_private = "SELECT Id, private_id FROM Product_Private
								JOIN Product ON Product.prod_name=Product_Private.prod_name
								WHERE Product.prod_name=:product"; 
						$sql_commercial = "SELECT Id, commercial_id FROM Product_Commercial
								JOIN Product ON Product.prod_name=Product_Commercial.prod_name
								WHERE Product.prod_name=:product"; 
						//References
						$sql_ref_activity = "SELECT Id, Num, Name, D_Del 
								FROM Ref_Private 
								WHERE D_Del is null and Type_Ref = 'act' or Ref_Private.Id IN 
								(SELECT Product_Activity.activity_id From Product_Activity 
								WHERE Product_Activity.prod_name = :product)";
						$sql_ref_private = "SELECT Id, Name, D_Del, Type_Ref
								FROM Ref_Private 
								WHERE D_Del is null and Type_Ref = 'Con' or Ref_Private.Id IN 
								(SELECT Product_Private.private_id From Product_Private 
								WHERE Product_Private.prod_name = :product)";
						$sql_ref_commercial = "SELECT Id, Name, D_Del, Type_Ref
								FROM Ref_Private 
								WHERE D_Del is null and Type_Ref = 'Com' or Ref_Private.Id IN 
								(SELECT Product_Commercial.commercial_id From Product_Commercial 
								WHERE Product_Commercial.prod_name = :product)";
						$sql_pers_data = "SELECT PersonalData, EU_citizens FROM Personal_Data
								WHERE Personal_Data.prod_name = :product;";
						//Product Name
						$sql_prod_name = "SELECT prod_name FROM Product WHERE prod_name <> :product;";
						$sql_acc = "SELECT acc_id, display_name FROM Accounts WHERE D_Del is NULL AND acc_id != 8";
						//---------------------------------------------------------------------------------------------------	
						try{
							//Prepare SELECT Statements
							//Publisher and Product
							$stmt_gen = $conn->prepare($sql_gen);
							$stmt_gen->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_gen->execute();

							//Fetch results
							$result_gen = $stmt_gen->fetchAll(PDO::FETCH_ASSOC);
							$db_prod_name = $result_gen[0]['prod_name'];
							$db_it_system = $result_gen[0]['it_system'];  //-----//
							$db_rus_prod_name = $result_gen[0]['rus_prod_name'];   //-----//
							$db_description = $result_gen[0]['description'];
							$db_introduced_in = $result_gen[0]['introduced_in'];
							$db_preceded_by = $result_gen[0]['preceded_by'];
							$db_pub_name = $result_gen[0]['pub_name'];
							$db_website = $result_gen[0]['website'];
							$db_pub_phone = $result_gen[0]['pub_phone'];
							$db_pub_mail = $result_gen[0]['pub_mail'];
							$db_pub_id = $result_gen[0]['pub_id'];
							$db_display_name = $result_gen[0]['display_name'];
							$db_acc_id = $result_gen[0]['acc_id'];
							$db_availability = $result_gen[0]['availability'];
							$db_email = $result_gen[0]['email'];
							$db_phone_number = $result_gen[0]['phone_number'];
							//------------------------------------------------------------------------------------------------
							$stmt_sys_user = $conn->prepare($sql_sys_user);
							$stmt_sys_user->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_sys_user->execute();

							//Loop Through rows and fetch results
							$result_sys_user = $stmt_sys_user->fetchAll(PDO::FETCH_ASSOC);
							$total_sys_user = count($result_sys_user);
							$old_sys_user = count($result_sys_user);
							for($x = 0; $x < $total_sys_user; $x++){
								$db_sys_user_id[$x] = $result_sys_user[$x]['sys_user_id'];
								$db_owner[$x] = $result_sys_user[$x]['owner'];
								$db_details[$x] = $result_sys_user[$x]['details'];
								$db_comment[$x] = $result_sys_user[$x]['comment'];
							}
							$db_belong = $result_sys_user[0]['belong'];
							//-------------------------------------------
							$stmt_activity = $conn->prepare($sql_activity);
							$stmt_activity->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_activity->execute();

							//Loop Through rows and fetch results
							$result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);
							$total_activity = count($result_activity);
							$old_activity = count($result_activity);
							for($x = 0; $x < $total_activity; $x++){
								$db_activity_id[$x] = $result_activity[$x]['Id'];
								$db_activity[$x] = $result_activity[$x]['activity_id'];
							}
							//-------------------------------------------
							//Confidential information
							$stmt_private = $conn->prepare($sql_private);
							$stmt_private->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_private->execute();

							//Loop Through rows and fetch results
							$result_private = $stmt_private->fetchAll(PDO::FETCH_ASSOC);
							$total_private = count($result_private);
							$old_private = count($result_private);
							for($x = 0; $x < $total_private; $x++){
								$db_private_id[$x] = $result_private[$x]['Id'];
								$db_private[$x] = $result_private[$x]['private_id'];
							}
							//-------------------------------------------
							//Commercial confidentiality
							$stmt_commercial = $conn->prepare($sql_commercial);
							$stmt_commercial->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_commercial->execute();

							//Loop Through rows and fetch results
							$result_commercial = $stmt_commercial->fetchAll(PDO::FETCH_ASSOC);
							$total_commercial = count($result_commercial);
							$old_commercial = count($result_commercial);
							for($x = 0; $x < $total_commercial; $x++){
								$db_commercial_id[$x] = $result_commercial[$x]['Id'];
								$db_commercial[$x] = $result_commercial[$x]['commercial_id'];
							}

							//Ref_Activity
							$stmt_ref_activity = $conn->prepare($sql_ref_activity);
							$stmt_ref_activity->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_ref_activity->execute();
							//Loop Through rows and fetch results
							$result_ref_activity = $stmt_ref_activity->fetchAll(PDO::FETCH_ASSOC);
							$total_ref_activity = count($result_ref_activity);
							for($x = 0; $x < $total_ref_activity; $x++){
								$db_ref_activity_id[$x] = $result_ref_activity[$x]['Id'];
								$db_ref_activity[$x] = $result_ref_activity[$x]['Name'];
								$db_ref_activity_num[$x] = $result_ref_activity[$x]['Num'];
							}
							//Private
							$stmt_ref_private = $conn->prepare($sql_ref_private);
							$stmt_ref_private->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_ref_private->execute();
							//Loop Through rows and fetch results
							$result_ref_private = $stmt_ref_private->fetchAll(PDO::FETCH_ASSOC);
							$total_ref_private = count($result_ref_private);
							for($x = 0; $x < $total_ref_private; $x++){
								$db_ref_private_id[$x] = $result_ref_private[$x]['Id'];
								$db_ref_private[$x] = $result_ref_private[$x]['Name'];
							}
							//commercial
							$stmt_ref_commercial = $conn->prepare($sql_ref_commercial);
							$stmt_ref_commercial->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_ref_commercial->execute();
							//Loop Through rows and fetch results
							$result_ref_commercial = $stmt_ref_commercial->fetchAll(PDO::FETCH_ASSOC);
							$total_ref_commercial = count($result_ref_commercial);
							for($x = 0; $x < $total_ref_commercial; $x++){
								$db_ref_commercial_id[$x] = $result_ref_commercial[$x]['Id'];
								$db_ref_commercial[$x] = $result_ref_commercial[$x]['Name'];
							}
							//Personal data and EU citizens
							$stmt_pers_data = $conn->prepare($sql_pers_data);
							$stmt_pers_data->bindParam(':product', $prod);
							$stmt_pers_data->execute();

							$result_pers_data = $stmt_pers_data->fetchAll(PDO::FETCH_ASSOC);
							$db_pers_data = $result_pers_data[0]['PersonalData'];
							$db_eu_citizens = $result_pers_data[0]['EU_citizens'];
							
							//Product name
							$stmt_prod_name = $conn->prepare($sql_prod_name);
							$stmt_prod_name->bindParam(':product', $prod);
							$stmt_prod_name->execute();
							$result_prod_name = $stmt_prod_name->fetchAll(PDO::FETCH_ASSOC);
							$total_prod_name = count($result_prod_name);
							for($x = 0; $x < $total_prod_name; $x++){
								$db_prod_name_ref[$x] = $result_prod_name[$x]['prod_name'];
							}
							//------------------------------------------------------------------------------------------------
							//ref accounts
							$stmt_acc = $conn->prepare($sql_acc);
							$stmt_acc->execute();
							$result_acc = $stmt_acc->fetchAll(PDO::FETCH_ASSOC);
							$total_acc = count($result_acc);
							for($x = 0; $x < $total_acc; $x++){
								$db_acc_id_ref[$x] = $result_acc[$x]['acc_id'];
								$db_acc_name_ref[$x] = $result_acc[$x]['display_name'];
							}
							//------------------------------------------------------------------------------------------------
							//Software
							$stmt_soft = $conn->prepare($sql_soft);
							$stmt_soft->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_soft->execute();
							//Loop Through rows and fetch results
							$result_soft = $stmt_soft->fetchAll(PDO::FETCH_ASSOC);
							$total_soft = count($result_soft);
							$old_soft = count($result_soft);
							for($x = 0; $x < $total_soft; $x++){
								$db_soft_id[$x] = $result_soft[$x]['Soft_Id'];
								$db_name_soft[$x] = $result_soft[$x]['Soft_Name'];
								$db_address_soft[$x] = $result_soft[$x]['Soft_Address'];
								$db_desc_soft[$x] = $result_soft[$x]['Soft_Desc'];
							}
							//------------------------------------------------------------------------------------------------
							//Hardware
							$stmt_hdw = $conn->prepare($sql_hdw);
							$stmt_hdw->bindParam(':product', $prod);
							//Execute SQL Command
							$stmt_hdw->execute();

							//Loop Through rows and fetch results
							$result_hdw = $stmt_hdw->fetchAll(PDO::FETCH_ASSOC);
							$total_hdw = count($result_hdw);
							$old_hdw = count($result_hdw);
							for($x = 0; $x < $total_hdw; $x++) {
								$db_srv_id[$x] = $result_hdw[$x]['srv_id'];
								$db_os[$x] = $result_hdw[$x]['os'];
								$db_role[$x] = $result_hdw[$x]['role'];
								$db_ip[$x] = $result_hdw[$x]['ip'];
								$db_hostname[$x] = $result_hdw[$x]['hostname'];
							}
							
							if($db_display_name==$db_acc_display_name||$db_display_name==''||$usr_rights=='modify'||$usr_rights=="admin"){
							}else{
								$_SESSION['alert'] = "warning";
								$_SESSION['alert_txt'] = $usr_username.": You are not allowed to modify products of ".$db_display_name."!";
								header("Location: list.php");
							}
							//------------------------------------------------------------------------------------------------
						}
						catch(PDOException $e){
							echo "Connection failed: " . $e->getMessage();
						}
						//Close Database Connection
						$conn = null;
						//----------------------------------------------------------------------------------------------------
					?>

					<!----------------------------------->
					<!--Display product information-->
					<!----------------------------------->
					<tr>
						<th width=24%>Product<br>
						<input type="hidden" name="it_system" value="No">
					</th>
						<td width=24%></td>
						<td width=25%></td>
						<td width=16%></td>
						<td width=36%></td>
					</tr>
					<tr id="stripe">
						<td><input type="text" name="prod_name" placeholder="Product" size="20" maxlength="200" value="<?php echo $db_prod_name;?>" readonly></td>
						<td><input type="text" name="rus_prod_name" placeholder="Russian product name" size="20" maxlength="200" value="<?php echo $db_rus_prod_name;?>"></td>
						<td><input type="date" <?php if($db_introduced_in!="1900-01-01"){ echo 'id="black"';} ?> name="introduced_in" placeholder="Introduced In" value="<?php echo $db_introduced_in;?>" onchange="changeColor(this)"></td>
						<td><input type="text" name="preceded_by" placeholder="Preceded By" size="20" maxlength="200" value="<?php echo $db_preceded_by;?>"></td>
					</tr>
					<tr id="stripe">
						<td class="alignTop"><input type="text" name="pub_name" placeholder="Publisher" size="20" maxlength="200" value="<?php echo $db_pub_name;?>"></td>
						<td class="alignTop"><input type="text" name="website" placeholder="Website" size="20" maxlength="200" value="<?php echo $db_website;?>"></td>
						<td rowspan="2" colspan="2"><textarea name="description" placeholder="Description" rows="3" cols="" maxlength="200" class="SizeTextareaBid" id="desc_prod"><?php echo $db_description;?></textarea></td>
					</tr>
					<tr id="stripe">
						<td class="alignTop"><input type="text" name="pub_phone" size="20" placeholder="Phone" maxlength="200" value="<?php echo $db_pub_phone;?>" pattern="\+[0-9][ 0-9()-]*"></td>
						<td class="alignTop"><input type="text" name="pub_mail" placeholder="Mail" size="20" maxlength="200" value="<?php echo $db_pub_mail;?>" pattern="(.)+@(.)+\.(.)+"></td>
						<input type="hidden" name="pub_id" value="<?php echo $db_pub_id;?>">
					</tr>
					<tr>
						<th>
							<?php
								if ($db_it_system == 'Yes'){ ?>
									<input type="checkbox" name="it_system" title="Is the product an information system?" value = "Yes" onclick="sel(this,'price')" checked>IT System <?php ;
								} else if ($db_it_system == 'No') {?> <input type="checkbox" name="it_system" title="Is the product an information system?" value = "Yes" onclick="sel(this,'price')">IT System <?php ; 
								}
							?>
						</th>
					</tr>
				</table>	
						<?php
							if($total_sys_user > 0 && $db_it_system == 'Yes'){
								echo '<table class="belong" data-price="">
								<tr>
									<th width=373px style="padding-top: 0px;"></th>
									<td width=51%></td>
								</tr>
								<tr>
									<td><input type="text" name="belong" size="20" placeholder="IT System’s owner" maxlength="200" value="'.@$db_belong.'"></td>
									<td></td>
								</tr>
							</table>';
								echo "<table id='ProbName' style='display:block;' data-price=''>
									<th width=24% style='padding-top: 0px;'></th>
									<td width=24%></td>
									<td width=25%></td>
									<td width=16%></td>
									<td width=36%></td>
								";								
								$x = 0;
								while ($x < $total_sys_user) {
									if(!isset($db_sys_user_id[$x])){
										$db_sys_user_id[$x] = $x;
									}
									echo "
										<tr>
											<td><input type='text' name='owner_".$x."' id='owner' placeholder='Data owner' size='20' maxlength='200' value='" . @$db_owner[$x] . "'></td>
											<td><input type='text' name='details_".$x."' id='details' placeholder='Details' size='20' maxlength='200' value='" . @$db_details[$x] . "'></td>
											<td><input type='text' name='comment_".$x."' id='comment' placeholder='Comment' size='20' maxlength='200' value='" . @$db_comment[$x] . "'></td>
											<td><button class='add' type='button' onclick='addSysUser(this)'><b>+</b></button>
											<button class='rem' type='button' onclick='deleteSysUser(this)'><b>x</b></button></td>
											<input type='hidden' name='sys_user_id_".$x."' value='" . $db_sys_user_id[$x] . "'>
										</tr>";
									$x++;
								}							
							}else if($total_sys_user > 0 && $db_it_system == 'No'){
								echo '<table class="belongNo" data-price="">
									<tr>
										<th width=373px style="padding-top: 0px;"></th>
										<td width=51%></td>
									</tr>
									<tr>
										<td><input type="text" name="belong" size="20" placeholder="IT System’s owner" maxlength="200" value="'.@$db_belong.'"></td>
										<td></td>
									</tr>
								</table>';
								echo "<table id='ProbName' style='display:none;' data-price=''>
									<th width=24% style='padding-top: 0px;'></th>
									<td width=24%></td>
									<td width=25%></td>
									<td width=16%></td>
									<td width=36%></td>
								";
								$x = 0;
								while ($x < $total_sys_user) {
									if(!isset($db_sys_user_id[$x])){
										$db_sys_user_id[$x] = $x;
									}
									echo "<tr>
											<td><input type='text' name='owner_".$x."' id='owner' placeholder='Owner' size='20' maxlength='200' value='" . @$db_owner[$x] . "'></td>
											<td><input type='text' name='details_".$x."' id='details' placeholder='Details' size='20' maxlength='200' value='" . @$db_details[$x] . "'></td>
											<td><input type='text' name='comment_".$x."' id='comment' placeholder='Comment' size='20' maxlength='200' value='" . @$db_comment[$x] . "'></td>
											<td><button class='add' type='button' onclick='addSysUser(this)'><b>+</b></button> <button class='rem' type='button' onclick='deleteSysUser(this)' ><b>x</b></button></td>
											<input type='hidden' name='sys_user_id_".$x."' value='" . $db_sys_user_id[$x] . "'>
										</tr>";
									$x++;
								}			
							}
						?>
				</table>
				<?php
					if($db_it_system == 'Yes')
					{ ?>
						<table id="ActiPriv" class="tableStyle" style="display:block;" data-price="">
							<tr>
								<th width=24%></th>
								<td width=24%></td>
								<td width=20%></td>
								<td width=36%></td>
								<td width=30px></td>
							</tr>
							<tr>
								<td>
									<input type="hidden" name="personal_data" value="No">
									<?php
										if ($db_pers_data == 'Yes'){ ?>
											<input type="checkbox" name="personal_data" value = "Yes" checked>Personal data<?php ;
										} else if ($db_pers_data == 'No') {?> <input type="checkbox" name="personal_data" value = "Yes">Personal data<?php ; 
										}
									?>
								</td>
								<td>
									<input type="hidden" name="eu_citizens" value="No">
									<?php
										if ($db_eu_citizens == 'Yes'){ ?>
											<input type="checkbox" name="eu_citizens" value = "Yes" checked>EU citizens<?php ;
										} else if ($db_eu_citizens == 'No') {?> <input type="checkbox" name="eu_citizens" value = "Yes">EU citizens<?php ; 
										}
									?>
								</td>
							</tr>
						</table>
						<table id="TypeOfActivity" class="tableStyle" style="display:block;" data-price="">
							<tr>
								<th width=48%>Type of activity</th>
								<td width=6%></td>
							</tr>
							<?php 
							$x = 0;
							while ($x < $total_activity) {
								if(!isset($db_activity_id[$x])){
									$db_activity_id[$x] = $x;
								}
								echo '<tr>
										<td><select id="black_act" name="activity_'.$x.'" onchange="changeColor(this)">
											<option value=""> </option>';
											for($y = 0; $y < $total_ref_activity; $y++){
												echo '<option ';
												if(isset($db_activity[$x]) && $db_ref_activity_id[$y] == $db_activity[$x]){
													echo 'value="'.$db_ref_activity_id[$y].'" selected>'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';
												}else if($total_activity == 0){
													echo 'value="'.$db_ref_activity_id[$y].'">'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';
												}else echo 'value="'.$db_ref_activity_id[$y].'">'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';								
											}
										echo "</select></td>
										<td><button class='add' type='button' onclick='addActivity(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deleteActivity(this)'><b>x</b></button></td>  
										<input type='hidden' name='activity_id_".$x."' value='" . $db_activity_id[$x] . "'>
								</tr>";
								$x++;
							}?>
						</table>
						<table id="ConInfo" class="tableStyle" style="display:block;" data-price="">
							<tr>
								<th width=48%>Confidential information</th>
								<td width=6%></td>
							</tr>
							<?php
							$x = 0;
							while ($x < $total_private) {
								if(!isset($db_private_id[$x])){
									$db_private_id[$x] = $x;
								}
								echo '<tr>
										<td><select id="black_con" name="private_'.$x.'" onchange="changeColor(this)">
											<option value=""> </option>';
											for($y = 0; $y < $total_ref_private; $y++){
												echo '<option ';
												if(isset($db_private[$x]) && $db_ref_private_id[$y] == $db_private[$x]){
													echo 'value="'.$db_ref_private_id[$y].'" selected>'.$db_ref_private[$y].'</option>';
												}else if($total_private == 0){
													echo 'value="'.$db_ref_private_id[$y].'">'.$db_ref_private[$y].'</option>';
												}else echo 'value="'.$db_ref_private_id[$y].'">'.$db_ref_private[$y].'</option>';								
											}
										echo "</select></td>
										<td><button class='add' type='button' onclick='addPrivate(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deletePrivate(this)'><b>x</b></button></td>  
										<input type='hidden' name='private_id_".$x."' value='" . $db_private_id[$x] . "'>
								</tr>";
								$x++;
							}?>
						</table>
						<table id="ComInfo" class="tableStyle" style="display:block;" data-price="">
							<tr>
								<th width=48%>Commercial confidentiality</th>
								<td width=6%></td>
							</tr>
							<?php
							$x = 0;
							while ($x < $total_commercial) {
								if(!isset($db_commercial_id[$x])){
									$db_commercial_id[$x] = $x;
								}
								echo '<tr>
										<td><select id="black_com" name="commercial_'.$x.'" onchange="changeColor(this)">
											<option value=""> </option>';
											for($y = 0; $y < $total_ref_commercial; $y++){
												echo '<option ';
												if(isset($db_commercial[$x]) && $db_ref_commercial_id[$y] == $db_commercial[$x]){
													echo 'value="'.$db_ref_commercial_id[$y].'" selected>'.$db_ref_commercial[$y].'</option>';
												}else if($total_commercial == 0){
													echo 'value="'.$db_ref_commercial_id[$y].'">'.$db_ref_commercial[$y].'</option>';
												}else echo 'value="'.$db_ref_commercial_id[$y].'">'.$db_ref_commercial[$y].'</option>';								
											}
										echo "</select></td>
										<td><button class='add' type='button' onclick='addCommercial(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deleteCommercial(this)'><b>x</b></button></td>  
										<input type='hidden' name='commercial_id_".$x."' value='" . $db_commercial_id[$x] . "'>
								</tr>";
								$x++;
							}
							?>
						</table>
						<table>
							<br />
						</table><?php						
					}
					if($db_it_system == 'No'){?>
						<table id="ActiPrivNo" class="tableStyle" style="display:none;" data-price="">
							<tr>
								<th width=24%></th>
								<td width=24%></td>
								<td width=20%></td>
								<td width=36%></td>
								<td width=30px></td>
							</tr>
							<tr>
								<td>
									<input type="hidden" name="personal_data" value="No">
									<?php
										if ($db_pers_data == 'Yes'){ ?>
											<input type="checkbox" name="personal_data" value = "Yes" checked>Personal data<?php ;
										} else if ($db_pers_data == 'No') {?> <input type="checkbox" name="personal_data" value = "Yes">Personal data<?php ; 
										}
									?>
								</td>
								<td>
									<input type="hidden" name="eu_citizens" value="No">
									<?php
										if ($db_eu_citizens == 'Yes'){ ?>
											<input type="checkbox" name="eu_citizens" value = "Yes" checked>EU citizens<?php ;
										} else if ($db_eu_citizens == 'No') {?> <input type="checkbox" name="eu_citizens" value = "Yes">EU citizens<?php ; 
										}
									?>
								</td>
							</tr>
						</table>
						<table id="TypeOfActivityNo" class="tableStyle" style="display:none;" data-price="">
							<tr>
								<th width=48%>Type of activity</th>
								<td width=6%></td>
							</tr>
							<?php
							$x = 0;
							while ($x < $total_activity) {
								if(!isset($db_activity_id[$x])){
									$db_activity_id[$x] = $x;
								}	
								echo '<tr>
										<td>
											<select name="activity_'.$x.'" id="black_act" onchange="changeColor(this)">
												<option value=""> </option>';
												for($y = 0; $y < $total_ref_activity; $y++){
													echo '<option ';
													if(isset($db_activity[$x]) && $db_ref_activity_id[$y] == $db_activity[$x]){
														echo 'value="'.$db_ref_activity_id[$y].'" selected>'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';
													}else if($total_activity == 0){
														echo 'value="'.$db_ref_activity_id[$y].'">'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';
													}else echo 'value="'.$db_ref_activity_id[$y].'">'.$db_ref_activity_num[$y].'&nbsp;&nbsp;'.$db_ref_activity[$y].'</option>';								
												}
											echo "</select>
										</td>
									<td><button class='add' type='button' onclick='addActivityNo(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deleteActivityNo(this)'><b>x</b></button></td>  
									<input type='hidden' name='activity_id_".$x."' value='" . $db_activity_id[$x] . "'>
								</tr>";
								$x++;
							}?>
						</table>
						<table id="ConInfoNo" class="tableStyle" style="display:none;" data-price="">
							<tr>
								<th width=48%>Confidential information</th>
								<td width=6%></td>
							</tr>
							<?php
							$x = 0;
							while ($x < $total_private) {
								if(!isset($db_private_id[$x])){
									$db_private_id[$x] = $x;
								}
								echo '<tr>
										<td>
											<select name="private_'.$x.'" id="black_con" onchange="changeColor(this)">
												<option value=""> </option>';
												for($y = 0; $y < $total_ref_private; $y++){
													echo '<option ';
													if(isset($db_private[$x]) && $db_ref_private_id[$y] == $db_private[$x]){
														echo 'value="'.$db_ref_private_id[$y].'" selected>'.$db_ref_private[$y].'</option>';
													}else if($total_private == 0){
														echo 'value="'.$db_ref_private_id[$y].'">'.$db_ref_private[$y].'</option>';
													}else echo 'value="'.$db_ref_private_id[$y].'">'.$db_ref_private[$y].'</option>';								
												}
											echo "</select>";
										echo "</td>";
										echo "<td><button class='add' type='button' onclick='addPrivateNo(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deletePrivateNo(this)'><b>x</b></button></td>  
										<input type='hidden' name='private_id_".$x."' value='" . $db_private_id[$x] . "'>";
								echo "</tr>";
								$x++;
							}?>
						</table>
						<table id="ComInfoNo" class="tableStyle" style="display:none;" data-price="">
							<tr>
								<th width=48%>Commercial confidentiality</th>
								<td width=6%></td>
							</tr>
							<?php
							$x = 0;
							while ($x < $total_commercial) {
								if(!isset($db_commercial_id[$x])){
									$db_commercial_id[$x] = $x;
								}
								echo '<tr>
										<td><select name="commercial_'.$x.'" id="black_com" onchange="changeColor(this)">
											<option value=""> </option>';
											for($y = 0; $y < $total_ref_commercial; $y++){
												echo '<option ';
												if(isset($db_commercial[$x]) && $db_ref_commercial_id[$y] == $db_commercial[$x]){
													echo 'value="'.$db_ref_commercial_id[$y].'" selected>'.$db_ref_commercial[$y].'</option>';
												}else if($total_commercial == 0){
													echo 'value="'.$db_ref_commercial_id[$y].'">'.$db_ref_commercial[$y].'</option>';
												}else echo 'value="'.$db_ref_commercial_id[$y].'">'.$db_ref_commercial[$y].'</option>';								
											}
										echo "</select></td>
										<td><button class='add' type='button' onclick='addCommercialNo(this)' ><b>+</b></button> <button class='rem' type='button' onclick='deleteCommercialNo(this)'><b>x</b></button></td>  
										<input type='hidden' name='commercial_id_".$x."' value='" . $db_commercial_id[$x] . "'>
								</tr>";
								$x++;
							}
							?>
						</table>
						<table>
							<br />
						</table><?php
					}?>	
				<table id="dataTable">
					<!----------------------------------->
					<!--Product responsibility information-->
					<!----------------------------------->
					<tr>
						<th width=24% style="padding-top: 0px;"></th>
						<td width=24%></td>
						<td width=25%></td>
						<td width=16%></td>
						<td width=36%></td>
					</tr>
						<th colspan="2" style="padding-top: 0px;">IT Product Manager</th>
					</tr>
					<tr id="stripe">
						<td>
							<?php echo '
							<select name="sup_name_rsp" id="black_com" onchange="CLearRow(this)" onchange="changeColor(this)">
								<option value=""></option>';
								for($y = 0; $y < $total_acc; $y++){
									echo '<option ';
									if(isset($db_acc_id) && $db_acc_id_ref[$y] == $db_acc_id){
										echo 'value="'.$db_acc_id_ref[$y].'" selected>'.$db_acc_name_ref[$y].'</option>';
									}else if($total_acc == 0){
										echo 'value="'.$db_acc_id_ref[$y].'">'.$db_acc_name_ref[$y].'</option>';
									}else echo 'value="'.$db_acc_id_ref[$y].'">'.$db_acc_name_ref[$y].'</option>';								
								}
							echo "</select>";
							?>
						</td>
						<!--<td><input type="text" name="sup_name_rsp" placeholder="Name/Role" size="20" maxlength="200" value="<?php //echo $db_sup_name_rsp;?>"></td>-->
						<td><input type="text" name="sup_phone_rsp" id="sup_phone" placeholder="Phone" size="20" maxlength="200" value="<?php echo $db_phone_number;?>" pattern="\+[0-9][ 0-9()-]*" readonly></td>
						<td><input type="text" name="sup_mail_rsp" id="sup_mail" placeholder="Mail" size="20" maxlength="200" value="<?php echo $db_email;?>" pattern="(.)+@(.)+\.(.)+" readonly></td>
						<td><select <?php if($db_availability!=""){ echo 'id="black"';} ?> name="sup_hours_rsp" id="sup_hours" onchange="changeColor(this)">
							<option value="" <?php if($db_availability==""){ echo "selected";} ?> hidden>Availability</option>
							<option value=""></option>
							<option value="Weekdays" <?php if($db_availability=="Weekdays"){ echo "selected";} ?>>Weekdays</option>
							<option value="24/7" <?php if($db_availability=="24/7"){ echo "selected";} ?>>24/7</option>
						</select></td>
						<input type="hidden" name="sup_contact_id_rsp" value="<?php echo $db_acc_id;?>">
					</tr>
				</table>
					
						<!----------------------------------->
						<!--Hardware information-->
						<!----------------------------------->
				<table id="dataTableHa">
						<tr>
							<th width=24%>Hardware</th>
							<td width=24%></td>
							<td width=25%></td>
							<td width=16%></td>
							<td width=36%></td>
						</tr>
						<?php
						$x = 0;
						//Display the hardware information for all records(number of hardwares)
						while ($x < $total_hdw) {
							if(!isset($db_srv_id[$x])){
								$db_srv_id[$x] = $x;
							}
							echo "	<tr>
									<td><input type='text' name='role_".$x."' id='role' placeholder='Role' size='20' maxlength='200' value='" . $db_role[$x] . "'></td>
									<td><input type='text' name='os_".$x."' id='os' placeholder='Operating System' size='20' maxlength='200' value='" . $db_os[$x] . "'></td>
									<td><input type='text' name='ip_".$x."' id='ip' placeholder='IP Adress' size='20' maxlength='200' value='" . $db_ip[$x] . "'></td>
									<td><input type='text' name='hostname_".$x."' id='host' placeholder='Host Name' size='10' maxlength='200' value='" . $db_hostname[$x] . "'></td>
									<td><button class='add' type='button' onclick='addSrv(this)'><b>+</b></button> <button class='rem' type='button' onclick='deleteHdw(this)'><b>x</b></button></td>
									<input type='hidden' name='srv_id_".$x."' value='" . $db_srv_id[$x] . "'>
								</tr>";
							$x++;
						} 
					?>
				</table>	
					<!----------------------------------->
					<!--Software information-->
					<!----------------------------------->
				<table id="dataTableSoft">
					<tr>
						<th width=24%>Software</th>
						<td width=24%></td>
						<td width=41.5%></td>
						<td width=16%></td>
						<td></td>
					</tr>
					<?php						
					$x = 0;
					while ($x < $total_soft) {
						if(!isset($db_soft_id[$x])){
							$db_soft_id[$x] = $x;
						}
						echo '
							<tr>
								<td class="alignTop"><input type="text" name="name_soft_'.$x.'" id="name_soft" placeholder="Name" size="20" maxlength="200" value="'.$db_name_soft[$x].'"></td>
								<td class="alignTop"><input type="text" name="address_'.$x.'" id="address_soft" placeholder="Address" size="20" maxlength="200" value="'.$db_address_soft[$x].'"></td>
								<td><textarea name="desc_soft_'.$x.'" id="desc_soft" placeholder="Description" rows="1.5" maxlength="200" class="SizeTextarea">'.$db_desc_soft[$x].'</textarea></td>
								<td><button class="add" type="button" onclick="addSoft(this)"><b>+</b></button> <button class="rem" type="button" onclick="deleteSoft(this)"><b>x</b></button></td>
								<input type="hidden" name="soft_id_'.$x.'" value="'.$db_soft_id[$x].'">
								<td> </td>
							</tr>';
						$x++;
					} 
				?>
				</table>
					<table>
						<tr>
							<td width=90%></td>
							<td><input type="submit" value="Submit"></td>
						<!----------------------------------->
						<!--Hidden Input: Number of records-->
						<!----------------------------------->
						<input type="hidden" id="total_srv" name="total_srv" value="<?php echo $total_hdw;?>">
						<input type="hidden" id="total_sys_user" name="total_sys_user" value="<?php echo $total_sys_user;?>">
						<input type="hidden" id="total_activity" name="total_activity" value="<?php echo $total_activity;?>">
						<input type="hidden" id="total_private" name="total_private" value="<?php echo $total_private;?>">
						<input type="hidden" id="total_commercial" name="total_commercial" value="<?php echo $total_commercial;?>">
						<input type="hidden" id="total_soft" name="total_soft" value="<?php echo $total_soft;?>">
						<!-----------------------------------> 
						<!--Hidden Input: Already existing records-->
						<!----------------------------------->
						<input type="hidden" name="old_srv" value="<?php echo $old_hdw;?>">
						<input type="hidden" name="old_sys_user" value="<?php echo $old_sys_user;?>">
						<input type="hidden" name="old_activity" value="<?php echo $old_activity;?>">
						<input type="hidden" name="old_private" value="<?php echo $old_private;?>">
						<input type="hidden" name="old_commercial" value="<?php echo $old_commercial;?>">
						<input type="hidden" name="old_soft" value="<?php echo $old_soft;?>">
						<!----------------------------------->
						<!--Hidden Input:Records to be deleted-->
						<!----------------------------------->
						<input type="hidden" name="delete_hdw" id="delete_hdw" value="">
						<input type="hidden" name="prod_name_old" value="<?php echo $_GET["product"];?>">
						<input type="hidden" name="delete_sys_user" id="delete_sys_user" value="">
						<input type="hidden" name="delete_activity" id="delete_activity" value="">
						<input type="hidden" name="delete_private" id="delete_private" value="">
						<input type="hidden" name="delete_commercial" id="delete_commercial" value="">
						<input type="hidden" name="delete_soft" id="delete_soft" value="">
			</form>
					<!----------------------------------->
					<!--Cancel Button-->
					<!----------------------------------->
					<form action="details.php">
						<input type="hidden" name="product" value="<?php echo $prod;?>">
						<td><button type="submit">Cancel</button></td>
					</form>
				</tr>
			</table>
		</div>
		
<!--------------------------------------------------------------------------------------------------------------------------->
<!--------------------------Javascript-------------------------->
<!--------------------------------------------------------------------------------------------------------------------------->
		<SCRIPT language="javascript">
			//Add files which should get deleted to hidden input
			//-------------------------
			function deleteActivity(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;	
				var table = document.getElementById("TypeOfActivity");
				var check = String(document.getElementById("TypeOfActivity").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_activity').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_activity').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("TypeOfActivity").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_activity").value == ""){
						document.getElementById("delete_activity").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_activity").value = document.getElementById("delete_activity").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_act').value = "";
				}
			}
			
			//-------------------------
			function deleteActivityNo(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("TypeOfActivityNo");
				var check = String(document.getElementById("TypeOfActivityNo").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_activity').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_activity').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("TypeOfActivityNo").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_activity").value == ""){
						document.getElementById("delete_activity").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_activity").value = document.getElementById("delete_activity").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_act').value = "";
				}
			}
			//-------------------------
			function deletePrivate(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("ConInfo");
				var check = String(document.getElementById("ConInfo").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_private').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_private').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("ConInfo").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_private").value == ""){
						document.getElementById("delete_private").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_private").value = document.getElementById("delete_private").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_con').value = "";
				}
			}
			//-------------------------
			function deletePrivateNo(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("ConInfoNo");
				var check = String(document.getElementById("ConInfoNo").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_private').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_private').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("ConInfoNo").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_private").value == ""){
						document.getElementById("delete_private").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_private").value = document.getElementById("delete_private").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_con').value = "";
				}
			}
			
			//-------------------------
			function deleteCommercial(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("ComInfo");
				var check = String(document.getElementById("ComInfo").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_commercial').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_commercial').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("ComInfo").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_commercial").value == ""){
						document.getElementById("delete_commercial").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_commercial").value = document.getElementById("delete_commercial").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_com').value = "";
				}
			}
			//-------------------------
			function deleteCommercialNo(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("ComInfoNo");
				var check = String(document.getElementById("ComInfoNo").rows[index].childNodes[5]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_commercial').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_commercial').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("ComInfoNo").rows[index].childNodes[5].value;				
					if (document.getElementById("delete_commercial").value == ""){
						document.getElementById("delete_commercial").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_commercial").value = document.getElementById("delete_commercial").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('black_com').value = "";
				}
			}
			
			//Software
			function deleteSoft(x){
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("dataTableSoft");
				//Get position of the cell to be deleted
				/*var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("dataTableSoft");*/
				var check = String(document.getElementById("dataTableSoft").rows[index].childNodes[9]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_soft').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_soft').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("dataTableSoft").rows[index].childNodes[9].value;				
					if (document.getElementById("delete_soft").value == ""){
						document.getElementById("delete_soft").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_soft").value = document.getElementById("delete_soft").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('name_soft').value = "";
					document.getElementById('address_soft').value = "";
					document.getElementById('desc_soft').value = "";
				}
			}
			//Hardware
			function deleteHdw(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("dataTableHa");
				var check = String(document.getElementById("dataTableHa").rows[index].childNodes[11]);
				var rowCount = table.rows.length - 1;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_srv').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_srv').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("dataTableHa").rows[index].childNodes[11].value;				
					if (document.getElementById("delete_hdw").value == ""){
						document.getElementById("delete_hdw").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_hdw").value = document.getElementById("delete_hdw").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('role').value = "";
					document.getElementById('os').value = "";
					document.getElementById('ip').value = "";
					document.getElementById('host').value = "";
				}
			}
			function deleteSysUser(x){
				//Get position of the cell to be deleted
				var index = x.parentNode.parentNode.rowIndex;
				var table = document.getElementById("ProbName");
				var check = String(document.getElementById("ProbName").rows[index].childNodes[9]);
				var rowCount = table.rows.length;
				if (check=="undefined" && rowCount > 1){
					//Delete row
					table.deleteRow(index);
					var value = parseInt(document.getElementById('total_sys_user').value, 10);
					value = isNaN(value) ? 0 : value;
					value--;
					document.getElementById('total_sys_user').value = value;
				}else if (check!="undefined" && rowCount > 1){
					var content = document.getElementById("ProbName").rows[index].childNodes[9].value;				
					if (document.getElementById("delete_sys_user").value == ""){
						document.getElementById("delete_sys_user").value = content;
						//Delete row
						table.deleteRow(index);
					}else{
						document.getElementById("delete_sys_user").value = document.getElementById("delete_sys_user").value + ";" + content;
						//Delete row
						table.deleteRow(index);
					}
				}
				if (rowCount <= 1){
					document.getElementById('owner').value = "";
					document.getElementById('details').value = "";
					document.getElementById('comment').value = "";
				}
			}

			//----------------------------------------------------------------------------------------------------------------		
			function changeColor(sel){
			  sel.style.color = "#000000";              
			}
			//--------------------------------------
			function addActivity(x){
				//Select row position on which to add new row
				var table = document.getElementById("TypeOfActivity");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_activity').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="activity_"+(value);
				value++;
				document.getElementById('total_activity').value = value;
			}
			//--------------------------------------
			function addPrivate(x){
				//Select row position on which to add new row
				var table = document.getElementById("ConInfo");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_private').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="private_"+(value);
				value++;
				document.getElementById('total_private').value = value;
			}
			
			//--------------------------------------
			function addCommercial(x){
				//Select row position on which to add new row
				var table = document.getElementById("ComInfo");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_commercial').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="commercial_"+(value);
				value++;
				document.getElementById('total_commercial').value = value;
			}
			//--------------------------------------
			function addActivityNo(x){
				//Select row position on which to add new row
				var table = document.getElementById("TypeOfActivityNo");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_activity').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="activity_"+(value);
				value++;
				document.getElementById('total_activity').value = value;
			}

			//--------------------------------------
			function addPrivateNo(x){
				//Select row position on which to add new row
				var table = document.getElementById("ConInfoNo");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_private').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="private_"+(value);
				value++;
				document.getElementById('total_private').value = value;
			}
			//--------------------------------------
			function addCommercialNo(x){
				//Select row position on which to add new row
				var table = document.getElementById("ComInfoNo");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Add row at identified position
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++){
				//Insert cell in new row
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_commercial').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="commercial_"+(value);
				value++;
				document.getElementById('total_commercial').value = value;
			}
		//-----------------------------------------------------------------
			//Hardware
			function addSrv(x){
				//Select row position on which to add new row
				var table = document.getElementById("dataTableHa");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Insert cell in new row
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++) {
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_srv').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="role_"+(value);
				table.rows[y].cells[1].childNodes[0].name="os_"+(value);
				table.rows[y].cells[2].childNodes[0].name="ip_"+(value);
				table.rows[y].cells[3].childNodes[0].name="hostname_"+(value);
				value++;
				document.getElementById('total_srv').value = value;
			}
			//Software
			function addSoft(x){
				//Select row position on which to add new row
				var table = document.getElementById("dataTableSoft");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Insert cell in new row
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++) {
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_soft').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="name_soft_"+(value);
				table.rows[y].cells[1].childNodes[0].name="address_"+(value);
				table.rows[y].cells[2].childNodes[0].name="desc_soft_"+(value);
				value++;
				document.getElementById('total_soft').value = value;
			}
			//-------------------------------------------------------------------------------------------------
			function addSysUser(x){
				//Select row position on which to add new row
				var table = document.getElementById("ProbName");
				y = x.parentNode.parentNode.rowIndex + 1;
				//Insert cell in new row
				var row = table.insertRow(y);
				var colCount = table.rows[y-1].cells.length;
				for(var i=0; i<colCount; i++) {
					var newcell	= row.insertCell(i);
					//Copy content of previous row to this cell
					newcell.innerHTML = table.rows[y-1].cells[i].innerHTML;
					table.rows[y].cells[i].childNodes[0].value=""
				}
				var value = parseInt(document.getElementById('total_sys_user').value, 10);
				value = isNaN(value) ? 0 : value;
				table.rows[y].cells[0].childNodes[0].name="owner_"+(value);
				table.rows[y].cells[1].childNodes[0].name="details_"+(value);
				table.rows[y].cells[2].childNodes[0].name="comment_"+(value);
				value++;
				document.getElementById('total_sys_user').value = value;
			}
		//-----------------------------------------------------------------------------------------------------
			function sel(elem,targ){
				var condition = (elem.checked)?'block':'none';
				[].forEach.call(document.querySelectorAll('[data-'+targ+']'), function(elem){
					elem.style.display = condition;
				})
			}

			function CLearRow(x){
				document.getElementById('sup_phone').value = "";
				document.getElementById('sup_mail').value = "";
				document.getElementById('sup_hours').selectedIndex = "";
				
			}
			//-------------------------------------------------------------------------------------------------
			var autota = function (t, c) {
					"use strict";
					var ta; 
					var comp; 
					var crnrs; 
					var modT; 
					var modB; 
					var brdrR;
					var brdrT;
					var brdrB;
					var padT;
					var padB;
					var tare;
					var scrlL;
					var scrlT;
					function noCrnrs() {
						ta.style.borderTopRightRadius = brdrR + "px";
						ta.style.borderBottomRightRadius = brdrR + "px";
					}
					function sharpCrnrs() {
						if(modT) {
							ta.style.borderTopRightRadius = "0px";
						}
						if(modB) {
							ta.style.borderBottomRightRadius = "0px";
						}
					}
					function scrollbar() {
						if(ta.clientHeight !== ta.scrollHeight) {
							if(ta.style.overflowY !== "scroll") {
								if(crnrs) {
									sharpCrnrs();
								}
								ta.style.overflowY = "scroll";
							}
						}else if(ta.style.overflowY === "scroll") {
							if(crnrs) {
								noCrnrs();
							}
							ta.style.overflowY = "hidden";
						}
					}
					function size() {
						scrlL = window.pageXOffset;
						scrlT = window.pageYOffset;
						if(ta.scrollHeight <= ta.clientHeight) {
							ta.style.height = "auto";
						}
						ta.style.height = ta.scrollHeight + tare + "px";
						scrollbar();
						window.scrollTo(scrlL, scrlT);
					}
					function onResize() {
						getTare();
						if(crnrs) {
							noCrnrs();
							if(ta.style.overflowY === "scroll") {
								sharpCrnrs();
							}
						}
						size();
					}
					function addEvnts() {
						window.addEventListener("resize", onResize, false);
						ta.addEventListener("input", size, false);
					}
					function rmvEvnts() {
						window.removeEventListener("resize", onResize, false);
						ta.removeEventListener("input", size, false);
					}
					function fixNan(a) {
						if(isNaN(a)) {
							return 0;
						}
						return a;
					}
					function setTare() {
						brdrT = parseFloat(comp.getPropertyValue("border-top-width"));
						brdrB = parseFloat(comp.getPropertyValue("border-bottom-width"));
						padT = parseFloat(comp.getPropertyValue("padding-top"));
						padB = parseFloat(comp.getPropertyValue("padding-bottom"));
				
						brdrT = fixNan(brdrT);
						brdrB = fixNan(brdrB);
						padT = fixNan(padT);
						padB = fixNan(padB);
					}
					function setCrnrs() {
						brdrR = parseFloat(comp.getPropertyValue("border-top-left-radius"));
						brdrR = fixNan(brdrR);
						modT = (brdrT < brdrR) ? true : false;
						modB = (brdrB < brdrR) ? true : false;
					}
					function getTare() {
						setTare();
						setCrnrs();
						if(comp.getPropertyValue("box-sizing") === "border-box") {
							tare = brdrT + brdrB;
						}else{
							tare = (padT + padB) * -1;
						}
					}
					function modCrnrs(b) {
						if(typeof b === "boolean") {
							if(crnrs && !b) {
								noCrnrs();
							}
							crnrs = b;
						}
					}				
					function styleTa() {
						ta.style.overflow = "hidden";
						ta.style.overflowX = "hidden";
						ta.style.overflowY = "hidden";
						ta.style.wordWrap = "break-word";
						ta.style.position = "relative";
					}
					function cleanUp() {
						if(ta !== undefined) {
							rmvEvnts();
							comp = undefined;
							crnrs = undefined;
							ta = undefined;
						}
					}
					function setup(t, c) {
						if(!t) {
							return;
						}
						if(t.tagName === "TEXTAREA") {
							cleanUp();
				
							ta = t;
							comp = window.getComputedStyle(ta);
				
							modCrnrs(true);
							modCrnrs(c);
				
							styleTa();
							getTare();
							rmvEvnts();
							addEvnts();
							size();
						}
					}
					//initialize on instance
					setup(t, c);
					return {
						//"interface"
						init: function(t, c) {
							setup(t, c);
						},
						setCorners: function(b) {
							modCrnrs(b);
						},
						getComp: function() {
							return comp;
						},
						getText: function() {
							return ta.value;
						},
						destroy: function() {
							cleanUp();
						}
					};
				};
				var ta = autota(document.getElementById("desc_soft"));
				var ta = autota(document.getElementById("desc_prod"));
		</SCRIPT>
	</Body>
</HTML>
