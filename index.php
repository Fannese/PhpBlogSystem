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
				
				#***************************************#
				#*********FORMULARVERARBEITUNG *********#
				#***************************************#
				
				
				#**********AUSGABE DER FORMWERTE IN POST ARRAY*****************************#
				
if(DEBUG_V) echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($_POST);					
if(DEBUG_V)	echo "</pre>";
				
				
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
					
if(DEBUG_A)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userData <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_A)			print_r($userData);					
if(DEBUG_A)			echo "</pre>";

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
								#*****IN DIE SESSION SCHREIBE***********#
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
				//url überprufen
				
				if(isset($_GET['action'])	=== true){
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: logout gestartet... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//Schritt 2. entschärfen und Debug ausgabe

					$action = sanitizeString($_GET['action']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					//verzweigen
					
					if($action === 'logout'){
//if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action du bist abgemeldet <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						session_destroy();
						header('LOCATION: ./');
						exit();
					}
					
				} //END URL ÜBERPRUFUNG
				







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
			
			<!-- -------- USER MESSAGES START -------- -->

			<!-- -------- USER MESSAGES END -------- -->
			
			
			
		</main>
		
		<aside class="fright">
		
			<h2>ALLE BLOGS</h2>
			
		
		
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