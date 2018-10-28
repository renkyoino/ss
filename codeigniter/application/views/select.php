<html lang="en">
<head>
	
</head>
<body>

<div id="container">
	<h1>請求書を出力</h1>

	<div id="body">

	<form action="./exceloutput/index" method="post">

	<select name="CustomerId">
		<option value=""></option>
		<?php foreach($CustomerName as $id=>$name): 

			echo "<option value=".$id.">".$name."</option>";
		
		endforeach; ?>

	</select>

	<select name="ClosedYear">
		<option value=""></option>
		<?php foreach($ClosedYear as $id=>$year): 

			echo "<option value=".$id.">".$year."</option>";
		
		endforeach; ?>

	</select>年


	<select name="ClosedMonth">
		<option value=""></option>
		<?php foreach($ClosedMonth as $id=>$month): 

			echo "<option value=".$id.">".$month."</option>";
		
		endforeach; ?>

	</select>月

	<input type="submit" value="出力">

		<p><a href="./exceloutput/index">出力</a></p>
		<p><a href="./htmloutput/index">html出力</a></p>


	</div>
</div>

</body>
</html>