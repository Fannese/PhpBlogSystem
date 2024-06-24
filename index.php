<?php 
#**************************************************************************************************************#
				#***************************************#
				#********* PAGE CONFIGURATION **********#
				#***************************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/form.inc.php');
				require_once('./include/db.inc.php');

				#***************************************#
				#*********VARIABLE INITIALISIERUNG *****#
				#***************************************#
				$userEmail			=	NULL;
				$userPassword		=	NULL;
				$errorEmail			=  NULL;
				$errorPassword		=  NULL;
				$errorLogin			=	NULL;
				$filterID		 	=	NULL;
				$filterAllBlog		=	NULL;
				$msgAllCategory	=	NULL;
				$userFirstName		=	NULL;
				$userLastName		=	NULL;
				
				#***************************************#
				#*********FORMULARVERARBEITUNG *********#
				#***************************************#
				
				
				#**********AUSGABE DER FORMWERTE IN POST ARRAY*****************************#
/*				
if(DEBUG_V) echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($_POST);					
if(DEBUG_V)	echo "</pre>";
*/				
				
				//SCHRITT. 1. Prüfen ob formular abgeschickt wurde
				
				
				if(	isset($_POST['formLogin'])	===	true) {
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formular Login wird gestartet... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
					//SCHRITT. 2. FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
					
					$userEmail		= sanitizeString($_POST['f1']);
					$userPassword	= sanitizeString($_POST['f2']);
					
					//$passwordHash 	= password_hash($userPassword,PASSWORD_DEFAULT);
				
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userEmail: $userEmail <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userPassword: $userPassword <i>(" . basename(__FILE__) . ")</i></p>\n";
			
					//SCHRITT 3. Feld Validierung
					$errorEmail		= validateEmail($userEmail);
					$errorPassword	=	validateInputString($userPassword, minLength:4, maxLength:8);
					
					
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorEmail: $errorEmail <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorPassword: Password $errorPassword <i>(" . basename(__FILE__) . ")</i></p>\n";
					
									
					if(	$errorEmail	!==NULL OR	$errorPassword	!== NULL){
						//Fehlerfall
if(DEBUG)			echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Login enthält noch Fehler... <i>(" . basename(__FILE__) . ")</i></p>\n";
						$errorLogin	="Login enthält noch Fehler";
				
					} else{
						//Erfolgsfall
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login ist Fehlerfrei... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
						//SCHRITT 4. Weiterverarbeitung der Formularwerte
						
						#***************************************#
						#*****DATEN IM DATENBANK SPEICHERN *****#
						#***************************************#
						
						#**********DATENBANKOPERATIONEN***************#
						
						//Schritt 1. DB connect und DEBUG 
						
						$PDO	= dbConnect('blogprojekt');
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: datenbank Verbindung wurde aufgebaut... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						//Schritt 2. sql statement mit placeholder
						$sql				=	'SELECT userID, userFirstName, userPassword FROM users
												WHERE userEmail = :userEmail';
						$placeholders	=	array('userEmail'=>$userEmail);
						
						// Schritt 3 DB: Prepared Statements
						try{
							$PDOStatement	=	$PDO->prepare($sql);
							
							$PDOStatement->execute($placeholders);
						}
						catch(PDOException $error){
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							
						}
						// Schritt 4. Daten weiterverarbeiten
						$userData = $PDOStatement->fetch(PDO::FETCH_ASSOC);
						
						//DB verbindung abschliessen
						dbClose($PDO, $PDOStatement);
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Datenbank verbindung wurde getrennt <i>(" . basename(__FILE__) . ")</i></p>\n";
/*					
if(DEBUG_A)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)			print_r($userData);					
if(DEBUG_A)			echo "</pre>";
*/
						#**********VALIDATE PASSWORD***************************************#
						
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: PASSWORD VALIDIEREN ... <i>(" . basename(__FILE__) . ")</i></p>\n";

						
						//prüfen ob User in der DB vorhanden ist
						if($userData === false){
							//Fehlerfall
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Es gibt keinen User mit dieser Email in der DB <i>(" . basename(__FILE__) . ")</i></p>\n";										
							
						} else{
							//Erfolgsfall
if(DEBUG_A)				echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)				print_r($userData);					
if(DEBUG_A)				echo "</pre>";	
							
							// Prüfen ob Passwort mit Password_hash übereinstimmen
							if(password_verify($userPassword, $userData['userPassword']) === false){
								//Fehlerfall
if(DEBUG) 					echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Password stimmt nicht mit der password_hash überein <i>(" . basename(__FILE__) . ")</i></p>\n";										
								$errorLogin = 'Diese Logindaten sind ungültig!';
							} else{
								//Erfolsfall
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Passwort stimmt mit Passwort_hash<i>(" . basename(__FILE__) . ")</i></p>\n";
								
								
								#***************************************#
								#*****SESSION STARTEN UND USER-ID  *****#
								#*****IN DIE SESSION SCHREIBEN**********#
								#***************************************#
								
								//Schritt 1. LOGIN DÜRCHFÜHREN
								
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								session_name('wwwFannese');
								if(session_start() === false){
									//fehlerfall
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: Session kann nicht gestartet werden <i>(" . basename(__FILE__) . ")</i></p>\n";										
									$errorLogin = 'Der Login ist nicht möglich! 
															Bitte überprüfen Sie, ob Cookies aktiviert ist.';
								} else{
									//erfolgsfall
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Session wurde gestartet... <i>(" . basename(__FILE__) . ")</i></p>\n";

									//Schritt2. User Daten in der Session speichern
									$_SESSION['ID']			=	$userData['userID'];
									$_SESSION['IPAdress']	=	$_SERVER['SERVER_ADDR'];
								
if(DEBUG_A)						echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)						print_r($_SESSION);					
if(DEBUG_A)						echo "</pre>";		

									//Schritt3. Weiterleitung in der dashboard.php
									header('LOCATION: ./dashboard.php');
								
								
								
								} // END LOGIN SESSION
					
								
							} //END PASSWORD VERIFY

						
						}//END VALIDATE USER
		
						
					}//END FORM VALIDATE
					
	
				}//FORM LOGIN END
				
				#***************************************#
				#*****URLVERARBEITUNG  *****************#
				#*****LOGOUT****************************#
				#***************************************#			
/*				//url überprufen 
if(DEBUG_A)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_GET <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)			print_r($_GET);					
if(DEBUG_A)			echo "</pre>";		*/			
				if(isset($_GET['action'])	=== true){
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Action wurde übergeben... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//Schritt 2. entschärfen und Debug ausgabe

					$action = sanitizeString($_GET['action']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
			
					// Schritt 3 URL: Je nach erlaubtem Parameterwert verzweigen
					
					
					#********** SHOW CATEGORY **********#
					if( $action === 'filter' ) {
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Zeige Category... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Schritt 4 URL: Werte weiterverarbeiten
						
						//$showContentURLData	= true;
						
						
						$filterID		=	sanitizeString($_GET['catID']);
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$filterID	: $filterID	 <i>(" . basename(__FILE__) . ")</i></p>\n";

					}
					
			
										
					#********** SHOW ALL BLOG **********#
					elseif( $action === '' ) {
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Zeige alle Blogbeiträge... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						$msgAllCategory	= 'Alle Category';
						header('LOCATION: ./');
						exit();

					}

					//verzweigen LOGOUT
					
					elseif($action === 'logout'){
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action du bist abgemeldet <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						session_destroy();
						header('LOCATION: ./');
						exit();
					}
		
				} //END URL ÜBERPRUFUNG
				
#**************************************************************************************************************#				
				
				#***************************************#
				#*****FETCH CATEGORIE FROM *************#
				#*****DATABASE**************************#
				#***************************************#
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Category Einträge werden verarbeitet <i>(" . basename(__FILE__) . ")</i></p>\n";
				
							
				$PDO		=	dbConnect('blogprojekt');
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Mit der DB blogprojekt verbunden.... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								
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
				
				#***************************************#
				#*****FETCH BLOGS FROM *************#
				#*****DATABASE**************************#
				#***************************************#
				
							
				$PDO		=	dbConnect('blogprojekt');
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Mit der DB blogprojekt werbunden.... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
				if($filterID	===	NULL	)	{
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Alle Blogeinträge werden ausgelesen <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//
					//Statement und Placeholders
					//zugriff auf die Blogs
					$sql				= 'SELECT * FROM blogs INNER JOIN users';
											
					$placeholders	= array();
			
				} else{
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Blogeinträge werden nach Category gefiltert <i>(" . basename(__FILE__) . ")</i></p>\n";
	
					//
					//Statement und Placeholders
					//zugriff auf die Blogs und catID
					$sql				= 'SELECT * FROM blogs INNER JOIN users WHERE catID = :catID' ;
											
					$placeholders	= array('catID'=>$filterID);
					
				}		
				
				// Schritt 3 DB: Prepared Statements
				try{
					$PDOStatement	=	$PDO->prepare($sql);
					
					$PDOStatement->execute($placeholders);
				}
				catch(PDOException $error){
if(DEBUG) 		echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							
				}	
								
				$blogData 	= $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
				//$user			= $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
				
if(DEBUG_A)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$blogData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)	print_r($blogData);					
if(DEBUG_A)	echo "</pre>";	
															
				
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
		
			<!-- -------- LOGIN FORM START -------- -->
			<form action="" method="POST">
				<input type="hidden" name="formLogin">
				<fieldset>
					<legend>Login</legend>					
					<span class='error'><?=$errorEmail ?><br><?=$errorLogin ?><br><?=$errorPassword ?></span><br>
					<input class="short" type="text" name="f1" placeholder="Email-Adresse...">
					<input class="short" type="password" name="f2" placeholder="Passwort...">
					<input class="short" type="submit" value="Anmelden">
				</fieldset>
			</form>
			<!-- -------- LOGIN FORM END -------- -->		

			<p><a href="dashboard.php">Dashboard >></a></p>
			<p><a href="?action=logout"><< Logout</a></p>
			
	
		</header>
		<div class="clearer"></div>
		
		<hr>
		<!-- -------- PAGE HEADER END -------- -->
		
		
		<main class="fleft">
		
			<h1>PHP-Projekt  Blog-System </h1>
	
		<br>
		
		<!-- ---------- CONTENT OUTPUT CATID ---------- -->

		<br>
		<hr>
			
	
			 <a href="?action=">Zeige alle Category</a>
			 <h2><?=$msgAllCategory?></h2>
			<?php foreach($blogData AS $blog): ?>
			<hr>
			<h4>Hallo <?=$blog['userFirstName'] ?> <?=$blog['userLastName'] ?> du hast dieses Beitrag am  <?=$blog['blogDate'] ?>verfasst</h4>
			<h3><?=$blog['blogHeadline'] ?></h3>
			<p><?=$blog['blogContent'] ?></p>
			<img src="<?=$blog['blogImagePath'] ?>" alt="Girl in a jacket" width="200" height="300">
		
			<?php endforeach?>
		</main>
	<!-- -------- FETCH CATEGORY -------- -->		
		<aside class="fright">
		
			<h2>ALLE Kategories</h2>
			<nav>
				<?php foreach($catData AS $category): ?>
				 <br>
				 <br>
				  
				 <a href="?action=filter&catID=<?=$category['catID'] ?>"><?=$category['catLabel']?></a>
				
				 <?php endforeach?> 
			</nav>
	
	<!-- -------- FETCH CATEGORY END -------- -->	
		
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		
	<p>
</p>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		
	</body>
	
</html>