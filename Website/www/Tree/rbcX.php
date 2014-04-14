<html>
  <body>
	<h2>Search</h2> 
	<form  method="post" action="rbcX.php?go"  id="searchform"> 
		Search for: <input type="text" name="find" /> in 
		<Select NAME="field">
			<Option VALUE="Host">Host</option>
			<Option VALUE="Accession">Accession Number</option>
			<Option VALUE="Species">Photobiont</option>
		</Select>
		<input type="hidden" name="searching" value="yes" />
		<input type="submit" name="submit" value="Search">
		
		<?php 
			if(isset($_POST['submit'])){ 
				if(isset($_GET['go'])){ 
				    echo $_POST['find'];
					if(preg_match("/[A-Z | a-z]+/", $_POST['find'])){
						$name=$_POST['find'];
						$field=$_POST['field'];
						//connect  to the database 
						$db=mysql_connect  ("mysql06.iomart.com", "obrimb807",  "f9kn8451") or die ('I cannot connect to the database  because: ' . mysql_error()); 
						//-select  the database to use 
						$mydb=mysql_select_db("obrimb807"); 
						//-query  the database table 
						echo "SELECT Accession FROM Metadata WHERE  " .$field . " LIKE '%" . $name . "%' OR Species LIKE '%" . $name  ."%'" ; 
						$sql="SELECT Accession FROM Metadata WHERE  " .$field . " LIKE '%" . $name . "%' OR Species LIKE '%" . $name  ."%'"; 
						//-run  the query against the mysql query function 
						$result=mysql_query($sql);
						while($row=mysql_fetch_array($result)){ 
							echo $row['Accession']; 
						} 
					} 
					else{ 
						echo  "<p>Search query not recognized</p>"; 
					}
				}
				//else{ 
				//	echo  "<p>Please enter a search query</p>"; 
				//}
			} 
		?>  
	</form>
	<object type="image/svg+xml" data="rbcX.svg", width="100%" height="100%">Your browser does not support SVG</object>
  </body>
</html>
