<?php
include 'util.php';
require_once "config.php";
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
else {
	$callback = util::curl($_SESSION["endpoint"] . "/admin/list");
	$response = json_decode($callback,true); //because of true, it's in an array
	$tasks_count =  $response['tasks_num'];
	$tasks_list = $response['tasks'];
	
	
	// ==================== TO TEST ======================
	if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["newtask_target"])) {
		echo "<script>alert('" . $_POST["newtask_target"] . "')</script>";
		$params = json_encode(['url'=>$_POST["newtask_target"]]);
		$callback_newtask = util::curl($_SESSION["endpoint"] . "/task/new");
		$newtask_id = json_decode($callback_newtask,true)['taskid'];
		echo $callback_newtask;
		echo "<script>console.log('task created with id " . $newtask_id . "')</script>";
		
		$callback_newtask_scan = util::curl_post($_SESSION["endpoint"] . "/scan/" . $newtask_id . "/start", $params);
		$scan_status = json_decode($callback_newtask_scan,true);

		
		$sql = "INSERT INTO scans (id, ownerid, target, action, started) VALUES (?, ?, ?, ?, NOW())";
		 
		if($stmt = mysqli_prepare($link, $sql)){
			// Bind variables to the prepared statement as parameters
			mysqli_stmt_bind_param($stmt, "ssss", $param_id, $param_ownerid, $param_target, $param_action);
			
			// Set parameters
			$param_id = $newtask_id;
			$param_ownerid = $_SESSION["userId"];
			$param_target = $_POST["newtask_target"];
			$param_action = 'Scan';
			
			// Attempt to execute the prepared statement
			if(mysqli_stmt_execute($stmt)){
				// Redirect to login page
				header("location: dashboard.php");
			} else{
				echo "<script>console.log('error occured while executing sql statement')</script>";
			}

			// Close statement
			mysqli_stmt_close($stmt);
		}
		
		
		
	}
	else {
		echo "no param";
	}
	
	if($_SESSION["userId"]){
		$wrapper_actions = [];
		$wrapper_target = [];
		$wrapper_dates = [];
			// Prepare a select statement
			$sql = "SELECT id, ownerid, target, action, started FROM scans WHERE ownerid = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_ownerid);
				
				// Set parameters
				$param_ownerid = $_SESSION["userId"];
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					// Store result
					mysqli_stmt_store_result($stmt);
					
					if(mysqli_stmt_num_rows($stmt) > 0){                    
						// Bind result variables
						mysqli_stmt_bind_result($stmt, $id, $owner_id, $target, $action, $date);
						
						while(mysqli_stmt_fetch($stmt)){
							$arr = ['id' => $id, 'target' => $target, 'action' => $action, 'date' => $date];
							$arr_action = [$id => $action];
							$arr_target = [$id => $target];
							$arr_date = [$id => $date];
							$wrapper_actions = $wrapper_actions + $arr_action;
							$wrapper_target = $wrapper_target + $arr_target;
							$wrapper_dates = $wrapper_dates + $arr_date;
							
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
		
		// Close connection
		mysqli_close($link);
	
}
?>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="panel.css">

<title>Sql injection orchestrator v0.1</title>

<div id="hacker-bar">
  <div class="container">

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default" id="serverinfo">
          <div class="panel-heading">
            <a class="panel-title" href="/logout.php" style="color:red;">Disconnect</a>
          </div>
          <div class="panel-body">
            
            <div class="row">
              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico">
                    <i class="fa fa-user-secret"></i>
                  </div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Username</a></b></div>
                    <div class="feed-widget__date"><?php echo htmlspecialchars($_SESSION["username"]); ?></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="feed-widget__item">
                  <div class="feed-widget__ico"><i class="fa fa-tasks"></i></div>
                  <div class="feed-widget__info">
                    <div class="feed-widget__text"><b><a href="#">Number of tasks</a></b> </div>
                    <div class="feed-widget__date"><?php echo $tasks_count; ?></div>
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
            <h3 class="panel-title">Tasks</h3>
          </div>
          <div class="panel-body">
				<div class="card">
					<div class="card-header"><h4 class="card-title">Simple Table</h4></div>
					<div class="card-body">
						<div class="table-responsive ps">
							<table class="tablesorter table">
								<thead class="text-primary">
									<tr>
										<th>Id</th>
										<th>Target</th>
										<th>Action</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
									// curl task list
									// generate list like below
									
									foreach ($tasks_list as $key=>&$val) {
										$act = '';
										foreach($wrapper_actions as $ida => $ac) {
											if($ida == $key) {
												$act = $ac;
											}
										}
										$trg = '';
										foreach($wrapper_target as $idt => $ta) {
											if($idt == $key) {
												$trg = $ta;
											}
										}
										$dt = '';
										foreach($wrapper_dates as $idd => $da) {
											if($idd == $key) {
												$dt = $da;
											}
										}
										echo "
											<tr onclick=\"document.location.href='/panel.php?id=" . $key . "'\">
											<td>" . $key . "</td>
											<td>" . $trg . "</td>
											<td>" . $act . "</td>
											<td>" . $val . "</td>
											</tr>
										";
									}
									
									?>
								</tbody>
							</table>
							<div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div>
							<div class="ps__rail-y" style="top: 0px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div></div>
						</div>
					</div>
				</div>
          </div>
        </div>
      </div>
    </div>
	
	
	<div class="row" id="newtask">
      <div class="col-md-12">
        <div class="panel panel-default" id="serverinfo">
          <div class="panel-heading">
            <h3 class="panel-title">Task creation</h3>
          </div>
          <div class="panel-body">
            
			<form action="dashboard.php" method="post" role="form">
			  <div class="form-row">
				<div class="input-group col-md-6">
				  <label for="newtask_target">Target url</label>
				  <input class="form-control" name="newtask_target">
				</div>
			  </div>
			 
			  <div class="form-row">
				<div class="input-group col-md-1">
				  <label for="inputState">Risk</label>
				  <select id="inputState" class="form-control">
					<option selected>1</option>
					<option>2</option>
					<option selected>3</option>
				  </select>
				</div>
				<div class="input-group col-md-1">
				  <label for="inputState">Level</label>
				  <select id="inputState" class="form-control">
					<option>1</option>
					<option>2</option>
					<option>3</option>
					<option>4</option>
					<option selected>5</option>
				  </select>
				</div>
			  </div>
			  <button type="submit" class="btn btn-primary col-md-3" id="bouttonnewtask">Create task</button>
			</form>
			
            
          </div>
		  
        </div>
      </div>
    </div>

    

  </div>
</div>