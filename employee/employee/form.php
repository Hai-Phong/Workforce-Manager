<?php

include("connection.php");

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset = "utf-8">
	<meta name = "viewport" content = "width = device - width, initial - scale = 1">
	<title>Software Dev</title>

<link rel = "stylesheet" type = "text/css" href = "style.css">

</head>
<body>
	<div class="center">

		<form action ="#" method="POST"> 
		<h1>Employee Data Entry Software</h1>
		<div class="form">
			<input type="text" name="id" class="textfield" placeholder=" ID">
			<input type="text" name="name" class="textfield" placeholder=" Employee Name">

			<select class="textfield" name="department">
				<option>Department</option>
				<option>Executive</option>
				<option>Human Resources</option>
				<option>Accounting</option>
				<option>R&D</option>
				<option>IT</option>
				<option>Sales</option>
				<option>Marketing</option>
				<option>Customer Support</option>
			</select>

			<input type="text" name="email" class="textfield" placeholder=" Email">

			<select class="textfield" name="gender">
				<option>Gender</option>
				<option>Male</option>
				<option>Female</option>
				<option>Other</option>
			</select>
			
			<textarea placeholder="Address" name="address"></textarea>

			<input type = "submit" name="save" value="SAVE" name="" class="btn">

		</div>
	</form>
	</div>
</body>
</html>

<?php
if(isset($_POST['save']))
{
	$id 		= $_POST['id'];
	$name 		= $_POST['name'];
	$department = $_POST['department'];
	$email 		= $_POST['email'];
	$gender 	= $_POST['gender'];
	$address 	= $_POST['address'];

	$query = "INSERT INTO employees (emp_id, emp_name, emp_department, emp_email, emp_gender, emp_address) VALUES('$id','$name','$department','$email','$gender','$address')";

	$data = mysqli_query($conn,$query);
	//cmd to insert info from the register table into the database

	if($data)
	{
		echo "Data saved successfully";
	}
	else
	{
		echo "Failed to save data: " . mysqli_error($data);
	}
}
?>


