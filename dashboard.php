<?php

#**************************************************************************************************************#

				#***************************************#
				#********* PAGE CONFIGURATION **********#
				#***************************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/form.inc.php');
				require_once('./include/db.inc.php');


				#****************************************#
				#********** ZUGRIFFSCHUTZ****************#
				#****************************************#
				
				//Session Check
				session_name('wwwFannese');
				
				session_start();
/*				
if(DEBUG_A)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)	print_r($_SESSION);					
if(DEBUG_A)	echo "</pre>";	
*/
				#*******************************************#
				#********** VALID LOGIN ÜBERPRÜFEN**********#
				#*******************************************#	

				if($_SESSION['ID']	===	false OR $_SESSION['IPAdress']	!== $_SERVER['REMOTE_ADDR']){
					//Fehlerfall
if(DEBUG) 		echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Login könnte nicht validiert werden <i>(" . basename(__FILE__) . ")</i></p>\n";										
					session_destroy();
					
					//Umleitung 
					header('LOCATION: ./');
					
					// weiter führung des Schript beenden
					exit();
				} else{
					//Erfolgsfall
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login ist erfolggreich <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//sessionID erneuern
					session_regenerate_id(true);
					// Identifizieren des Users anhand der ID in der Session
					$userID = $_SESSION['ID'];
/*					
if(DEBUG_A)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION_ID <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)		print_r($userID );					
if(DEBUG_A)		echo "</pre>";						
*/					
	
				} //END SESSION LOGIN VALID

#**********************************************************************************#				
				
				#*******************************************#
				#**********URL VERARBEITUNG*****************#
				#*******************************************#
				
				//Schritt 1. prüfen ob url Parameter übergeben wurde
				
				if(isset($_GET['action'])	===	true){
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>:URL wurde übergeben <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//Schritt 2. entschärfen, DEBUG ausgabe
					$action	=	sanitizeString($_GET['action']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//Schritt 3. verzweigen
					if($action	===	'logout'){
					
if(DEBUG) 			echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Logout wird durchgeführt..... <i>(" . basename(__FILE__) . ")</i></p>\n";										
						
						//Session löschen
						session_destroy();
						// weiterleiten
						header('LOCATION: ./');
						exit();
			
					} //END  LOGOUT
		
				} //END CHECK URL LOGOUT


#**********************************************************************************#


					#**********************************************#
					#********* PROCESS FORM CATEGORY***************#
					#**********************************************#
					
					
					#*******************************************#
					#**********VARIABLE INITIALISIERUNG*********#
					#*******************************************#
					$category		=  NULL;
					$errorCategory	=	NULL;
					$count			= NULL;
					$catData			= NULL;
				
				
					#********** PREVIEW POST ARRAY **********#
/*				
if(DEBUG_A)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)		print_r( $_POST);					
if(DEBUG_A)		echo "</pre>";						
*/					
					//SCHRITT. 1. Prüfen ob formular abgeschickt wurde
					if(	isset($_POST['formNewCategory'])	===	true)	{
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>:Category Formular wurde abgeschickt.... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						//Schritt 2. Werte entschärfen und DEBUG ausgabe
						$category	=	sanitizeString($_POST['catLabel']);
//if(DEBUG_V) 		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$cateegory: $cateegory <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
						
						//Schritt 4. feld validierung
						
						$errorCategory		=	validateInputString($category);
						
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorCategory: $errorCategory <i>(" . basename(__FILE__) . ")</i></p>\n";
						if($errorCategory !== NULL){
							//fehlerfall
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Category könnte nicht erstellt werden <i>(" . basename(__FILE__) . ")</i></p>\n";										
							$errorCategory = "Dies ist ein Pflichtfeld";
						} else{
							//erfolgsfall
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>:Category kann erstellt werden <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							//SCHRITT 4. Weiterverarbeitung der Formularwerte
						
							#***************************************#
							#*****DATEN IM DATENBANK SPEICHERN *****#
							#***************************************#
							
							#**********DATENBANKOPERATIONEN***************#
							
							//Schritt 1. DB connect und DEBUG 
							$PDO		=	dbConnect('blogprojekt');
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Mit der DB blogprojekt werbunden.... <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							//Statement und Placeholders
							//Prüfen ob Category bereits vorhanden ist
							$sql				= 'SELECT COUNT(catLabel) 
													FROM categories 
													WHERE catLabel = :catLabel';
							$placeholders	= array('catLabel'=>$category);
							
							// Schritt 3 DB: Prepared Statements
							try{
								$PDOStatement	=	$PDO->prepare($sql);
								
								$PDOStatement->execute($placeholders);
							}
							catch(PDOException $error){
if(DEBUG) 					echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
								
							}
							
							$count	= $PDOStatement->fetchColumn();
							if($count !==0){
								//Fehlerfall
if(DEBUG) 					echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: das categories ist bereits vorhanden <i>(" . basename(__FILE__) . ")</i></p>\n";										
								// Fehlermeldung für User
								$errorCategory = 'Category existiert bereits!';
							} else{
								//erfolgsfall
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Category existiert noch nicht in der DB <i>(" . basename(__FILE__) . ")</i></p>\n";
			
															
								//Schritt 2. sql Statement und Placeholders
								$sql				=	'INSERT INTO categories 
															(catLabel) 
															VALUES (:catLabel)';
								$placeholders	=	array('catLabel'=>$category);
								
								// Schritt 3 DB: Prepared Statements
								try{
									$PDOStatement	=	$PDO->prepare($sql);
									
									$PDOStatement->execute($placeholders);
								}
								catch(PDOException $error){
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
									
								}
								// Schritt 4. Daten weiterverarbeiten
								//Schreiberfolg prüfen
								$rowCount = $PDOStatement->rowCount(); 
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

								if($rowCount !==	1){
									//Fehlerfall
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: beim einlegen einer Categories ist ein Fehler aufgetreten <i>(" . basename(__FILE__) . ")</i></p>\n";										
									$errorCategory	= 'beim einlegen einer Categories ist ein Fehler aufgetreten bitte versuch später';
									dbClose($PDO, $PDOStatement);
								} else{
									//Erfolgsfall
									// Last-Insert-ID auslesen
									$newCat		=	$PDO->lastInsertID();
								
														
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Categories erfolgreich unter die ID $newCat gespeichert <i>(" . basename(__FILE__) . ")</i></p>\n";
			
							
								} // END SCHREIBERFOLG
								
							} //END CHECK OB DATA IN DER DATABASE VORHANDEN IST
							
				
						} //END CATEGORY CHECK
	
					} //END FORM ÜBERPRÜFUNG KATEGORY	
					
				#******************************************#
				#*****FETCH CATEGORY DATA FROM DATABASE****#
				#******************************************#
			
				$PDO		=	dbConnect('blogprojekt');
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Mit der DB blogprojekt werbunden.... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								
				//Statement und Placeholders
				//zugriff auf die Category
				$sql				= 'SELECT *
										FROM categories';
										
				$placeholders	= array();
				
				// Schritt 3 DB: Prepared Statements
				try{
					$PDOStatement	=	$PDO->prepare($sql);
					
					$PDOStatement->execute($placeholders);
				}
				catch(PDOException $error){
if(DEBUG) 		echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							
				}	
								
				$catData 	= $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
/*				
if(DEBUG_A)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$catData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)	print_r($catData);					
if(DEBUG_A)	echo "</pre>";	
																
*/					
				// DB-Verbindung schließen
				dbClose($PDO, $PDOStatement);


#**************************************************************************************************************#



				#******************************************#
				#*****PROCESS FORM BLOG********************#
				#******************************************#
				
				#******************************************#
				#*****VARIABLE INITIALISIERUNG*************#
				#******************************************#
				
				$alignArray					= array('1'=>'Links', '2'=>'Rechts');
				$category					=	NULL;
				$ueberschrift				=	NULL;
				$content						=	NULL;
				$errorCategory				=	NULL;
				$errorUeberschrift		=	NULL;
				$errorContent				=	NULL;
				$errorForm 					=	NULL;
				$erfolgForm					=	NULL;
				$align						=	NULL;
				
				//	Schritt 1. überprüfen ob Formblog abgeschickt wurde
				
				if(isset($_POST['formBlog'])	===	true){
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formular Blog ist Erfolgsreich abgeschickt <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//Schritt 2. Werte entschärfen und debug Ausgabe
					$category			=	sanitizeString($_POST['category']);
					$ueberschrift		= sanitizeString($_POST['ueberschrift']);
					$content				=	sanitizeString($_POST['content']);
					$filename			=	sanitizeString($_POST['filename']);
					$align				=	sanitizeString($_POST['align']);
/*					
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$category: $category <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$ueberschrift: $ueberschrift <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$content: $content <i>(" . basename(__FILE__) . ")</i></p>\n";
*/			
					//Schritt 3. Feld Validierung
					//$errorCategory			=	validateInputString($category);
					$errorUeberschrift	=	validateInputString($ueberschrift);
					$errorContent			=	validateInputString($content);
					
			
					//Formblog auf Fehlerfrei überprüfen
					if(	$errorUeberschrift	!==	NULL OR $errorContent	!==	NULL	)	{
						//Fehlerfall
if(DEBUG)			echo "<p class='debug err'>📑<b>Line " . __LINE__ . "</b>: FEHLER: Das Formular Blog enthält noch Fehler <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$errorForm = 'Bitte fühlen sie das Formular aus, Das Formular Blog enthält noch Fehler';
					} else{
						//Erfolgsfall
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formular Blog ist Fehlerfrei <i>(" . basename(__FILE__) . ")</i></p>\n";
						$erfolgForm	=	'Das Formular ist komform Fehlerfrei';


						//Schritt 4. FormBlog weiterverarbeitung
						
						#******************************************#
						#***** DATABASE OPERATION******************#
						#******************************************#
						
						//DB Connect
						$PDO	= dbConnect('blogprojekt');
					
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Erfolgreich mit der DB blogprojekt werbunden.... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
						//SQL-Statement und Placeholder-Array erstellen
						$sql		=	'INSERT INTO blogs 
										(blogHeadline, blogImagePath, blogImageAlignment, blogContent, catID, userID )
										 VALUES 
										 (:blogHeadline, :blogImagePath, :blogImageAlignment, :blogContent, :catID, :userID)';
						
						$placeholders	= array('blogHeadline'=>$ueberschrift,'blogImagePath'=>$filename, 
														'blogImageAlignment'=> $align, 'blogContent'=>$content,
														'catID'=> $category, 'userID'=>$userID);	
						
						// Schritt 3 DB: Prepared Statements
						try{
							$PDOStatement	=	$PDO->prepare($sql);
							
							$PDOStatement->execute($placeholders);
						}
						catch(PDOException $error){
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							
						}	
	
						// Schritt 4. Daten weiterverarbeiten
						//Schreiberfolg prüfen
						$rowCount = $PDOStatement->rowCount(); 
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

						if($rowCount !==	1) {
						//Fehlerfall
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: beim einlegen einer Blog ist ein Fehler aufgetreten <i>(" . basename(__FILE__) . ")</i></p>\n";										
							$errorCategory	= 'beim einlegen einer Blog ist ein Fehler aufgetreten bitte versuch später';
							dbClose($PDO, $PDOStatement);
						} else{
							//Erfolgsfall
							// Last-Insert-ID auslesen
							$newBlog		=	$PDO->lastInsertID();
											
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Blog erfolgreich unter die ID $newBlog gespeichert <i>(" . basename(__FILE__) . ")</i></p>\n";
			
							
							} // END SCHREIBERFOLG
			
					
					} // END FORMBLOG FRHLER ÜBERPRÜFUNG
			
			}// END CHECK SEND FORMBLOG

			// DB-Verbindung schließen
			dbClose($PDO, $PDOStatement);	
				

#**************************************************************************************************************#
?>



<!doctype html>

<html>
	
	<head>	
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Authentication - Login</title>
		
		<link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">
		
		<style>
			main {
				width: 60%;
			}
			aside {
				width: 30%;
				padding: 20px;
				border-left: 1px solid gray;
				opacity: 0.6;
				overflow: hidden;
			}
		</style>
		
	</head>
	
	<body>
		
		<!-- -------- PAGE HEADER START -------- -->
		<br>
		<header class="fright loginheader">
		
				

			<p><a href="index.php">Zur Index Page>></a></p>
			<p><a href="?action=logout"><< Logout</a></p>
			
	
		</header>
		<div class="clearer"></div>
		
		<hr>
		<!-- -------- PAGE HEADER END -------- -->
		
		
		<main class="fleft">
		
			<h1>PHP-Projekt  Blog-System </h1>
			
		
			<!--------ERSTELLEN EINES BLOGEINTRAG-------->
			<div>
			  <form action="" class="container" method="POST">
				  <input type="hidden" name="formBlog">
				  
						 <h1>Neuen Blog eintrag verfassen</h1>
						<br>
						<br>
						<span class='error'><?=$errorForm?>	</span>
						<span class='success'><b><?=$erfolgForm?></b></span><br>
						<br>
						
						<label><b>Kategorie</b></label>
					
						<select name="category">
						<?php  foreach($catData AS $value): ?>
							  <option value="<?=$value['catID'] ?>"><?= $value['catLabel']?></option>
						<?php endforeach?>	  
						</select>
						
						<br>
						<br>
						<br>
						<br>
						
						<label><b>Überschrift</b></label><span class='error'><?=$errorUeberschrift?></span>
						<input type="text" value="<?=$ueberschrift ?>" placeholder="Überberschrift eingeben" name="ueberschrift">
						<br>
						<br>
						<br>
						
						<label><b>Bildhochladen</b></label>
						<input type="file" id="myFile" name="filename">
						<br>
						<br>
						<label><b>Ausrichtung</b></label>
						<select name="align">
						<?php foreach($alignArray AS $index=>$value): ?>
							  <option value="<?=$value ?>"<?php if($align	===	$index) echo 'selected'?>><?=$value ?></option>
					  <?php endforeach?>
						</select>
						
						<br>
						<br>
						
						 
						<label><b>Content</b></label><span class='error'><?=$errorContent?></span><br>
						<textarea name="content" value="<?=$content ?>"></textarea><br>
						<br>
						<br>
						<br>
						
						 <button type="submit" class="btn">Veröffenlichen</button>
			  </form>
			</div>
		</main>
	<!--------END ERSTELLEN EINES BLOGEINTRAG-------->

	
<!--------ANLEGEN VON NEUE KATEGORIE-------->
		
		<aside class="fright">
			<form action="" method="POST">
			
				<input type="hidden" name="formNewCategory">
					<h2>Neuen Kategories anlegen</h2><br>
					<span class='error'><?=$errorCategory ?></span>
					 <input type="text" placeholder="Name der Kategorie" name="catLabel">
					 <br>
					 
				<br>
				<br>
					 <button type="submit" class="btn">Kategorie anlegen</button>
				
			</form>
		
	<!------------END ANLEGEN VON NEUE KATEGORIE----->	
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		
	</body>
	
</html>