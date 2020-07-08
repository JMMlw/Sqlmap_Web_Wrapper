<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="panel.css">
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
			if($_GET["act"] == "delete") {
				$flag = false;
			}
		}
		if($flag == true) {
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
                    <div class="feed-widget__date">2</div>
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
            <button class="btn" >View state</button>
            <button class="btn" >View datas</button>
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
            <h3 class="panel-title">Processes</h3>
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
						<div class=\"software\">
						<p>target table</p>
						<code>coupons</code>
						<code>users</code>
						</div>
					";
				}
				else {
					echo "<p>You can now perform actions on the target using above buttons list, results will be display in this box.</p>";
				}
				if(isset($_GET["act"])) {
					if($_GET["act"] == 'logs') {
						foreach ($logs['log'] as $log){
							foreach ($log as $key => $value){
								echo $key . " | " . $value . "<br>";
								if($key == "time")
									echo "<br>##########################################<br>";
							}
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