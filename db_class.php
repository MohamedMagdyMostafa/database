<?php 

	class db_class
	{		

		 private $db;

		  public function __construct(){
				
				   // PDO CONNECTION

				 
				  $option = array (

				  	PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
				  	// PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
				  	
				  	);

				  try {

				  	$this->db = $con = new PDO(DSN ,DB_USER ,DB_PASS ,$option);
				  	$con -> setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
				  	$con->exec("set names utf8");




				  } catch (Exception $e) {

				  	echo "Failed To Connect" . $e->getMessage();
				  	
				  }

				 	
					    


		  }

		public function importSql(){

						$filename = '../database.sql'; 
						$op_data = '';
						$lines = file($filename);
						if (!empty($lines)) {
						
							foreach ($lines as $line)
							{
							    if (substr($line, 0, 2) == '--' || $line == '')//This IF Remove Comment Inside SQL FILE
							    {
							        continue;
							    }
							    $op_data .= $line;
							    if (substr(trim($line), -1, 1) == ';')//Breack Line Upto ';' NEW QUERY
							    {
							        $this->db->query($op_data);
							        $op_data = '';
							    }
							}
						}
		}

		public function exportSheet($table_name = ''){

			try {

			    $stmt = $this->db->prepare("SELECT * FROM $table_name");
			    $stmt->execute();
 				$rows = $stmt->fetchAll();

 			 

				$filename     = 'export-'.date('Y-m-d H.i.s').'.xls';


				header('Content-Encoding: UTF-8');
				header('charset=utf-8;');
				header("Content-type: application/csv;charset=utf-8;");
				header("Content-Disposition: attachment;filename=$filename");
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');


				$data = fopen($filename, 'w');

				echo '<html lang="ar" xmlns:x="urn:schemas-microsoft-com:office:excel">';
				echo '<head><meta charset="utf-8"><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
				echo '<x:Name></x:Name>';
				echo '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
				echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';

				echo "
						<table id='myTable' class='table table-striped table-bordered'>
				      		<tbody>";
				 
							 $array_keys = array_keys($rows[0]);
								echo '<tr>';
								foreach ($array_keys as $array_key) {
									
									if (!is_numeric($array_key)) {
									
									echo '<td>'.$array_key.'</td>';
									}

								}
								echo '</tr>';

								foreach ($rows as $row) {
									
									echo '<tr>';
									$c = 1 ;
									foreach ($row as $value) {
										if($c%2 == 0){
											echo '<td>';
											print_r($value);
											echo '</td>';
										}
										$c++;
									}
									echo '</tr>';
									

									
								}
				    
		     	echo "
		     		</tbody>
		    	  </table>
		    	  </body>
		    	 </html>
		     	

		     	";
				

				echo "\r\n";

				fclose($data);
			}catch (Exception $e){

				$filename     = 'export-'.date('Y-m-d H.i.s').'.xls';
			  	header('Content-Encoding: UTF-8');
				header('charset=utf-8;');
				header("Content-type: application/csv;charset=utf-8;");
				header("Content-Disposition: attachment;filename=$filename");
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			  	
			 }
						
						   

		}

		 public function search($tableName , $col , $keyword=''){


		  	 try {

		      $stmt = $this->db->prepare("SELECT * FROM $tableName WHERE $col LIKE '%".$keyword."%'  ");
		      $stmt->execute();
		      $rows = $stmt->fetchAll();


		     return $rows;

		     } catch (Exception $e) {

		     	return $emptyArray = array();
		     }

		  }

		  
		  public function selectAll($tableName , $extra=''){


		  	 try {

		      $stmt = $this->db->prepare("SELECT * FROM $tableName $extra");
		      $stmt->execute();
		      $rows = $stmt->fetchAll();


		     return $rows;

		     } catch (Exception $e) {

		     	return $emptyArray = array();
		     }

		  }




		  public function select($tableName , $extra=''){

		  	 try {

			    $stmt = $this->db->prepare("SELECT * FROM $tableName $extra");
			    $stmt->execute();
			    $row = $stmt->fetch();


			     return $row;
			 } catch (Exception $e) {

		     	return $emptyArray = array();
		     }

		  }

		  public function count($tableName , $extra=''){

		  	try {
	   
			    $stmt = $this->db->prepare("SELECT * FROM $tableName $extra");
			    $stmt->execute();
			    $row = $stmt->fetch();
			    $count = $stmt->rowCount();


			     return $count;
			 } catch (Exception $e) {

		     	return $emptyCount = 0;
		     }

		  }

		  public function insert($tableName , $data) {

		     try {

		        if (is_array($data)) {

		        
		              
		              foreach ($data as $key => $value) {
		                  
		                  $keys[] = $key ;
		                  $values[]  = $value;

		              }
		                  $tblkeys =  implode($keys , ',');
		                  $tblvalues  =  "'".implode($values , "', '")."'";

		                  $stmt = $this->db->prepare("INSERT INTO $tableName($tblkeys)  VALUES ($tblvalues)  ");
		                  
		              if ( $stmt->execute() ) {
		                
		                return $this->db->lastInsertId();


		              }else {

		                return false;

		              }


		        }else {
		          return false;
		        }

		    } catch (Exception $e) {

		     	return $emptyId = 0;
		     }

		  }



		  public function update($tableName , $data , $extra='') {

		    try {

		        if (is_array($data)) {
		              
		              foreach ($data as $key => $value) {
		                  
		                  $keys[] =  "$key='$value' ";

		              }
		                  $tblkeys =  implode($keys , ',');
		                  

		                  $stmt = $this->db->prepare("UPDATE $tableName SET $tblkeys $extra  ");
		                  
		              if ( $stmt->execute() ) {
		                
		                return true;
		                
		              }else {
		                return false;
		              }


		        }else {
		          return false;
		        }

		    } catch (Exception $e) {

		     	return false;
		     }


		  }

		  public function rank($AdminId , $secId) {
		  	

		  	 try {

			    $stmt = $this->db->prepare("SELECT * FROM rank WHERE user_id = $AdminId AND sec_id = $secId");
			    $stmt->execute();
			    $row = $stmt->fetch();


			     return $row['rank_id'];
			 } catch (Exception $e) {

		     	return $emptyArray = array();
		     }


		  }

		  public function delete($tableName , $extra=''){
     
		  		

		    		 $stmt = $this->db->prepare("DELETE FROM $tableName $extra ");

		             $stmt->execute();

		       

		  }
		  

		 
	}


 ?>

