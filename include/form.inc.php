<?php
#************************************************************************************************#
				
				
				#*************************************#
				#********** SANITIZE STRING **********#
				#*************************************#
				
				/**
				
				*	@params		String	$value		Die zu bereinigende Zeichenkette
				*
				*	@return		String					Die bereinigte Zeichenkette
				*
				*/
				function sanitizeString($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug sanitizeString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, double_encode:false);
					
					
					$value = trim($value);
					
					if( $value === '' ) {
						$value = NULL;
					}
					
					return $value;
					#********** LOCAL SCOPE END **********#
				}
				

#************************************************************************************************#
				
				
				#*******************************************#
				#********** VALIDATE INPUT STRING **********#
				#*******************************************#
				
				/**
				*
				*
				*	@param	String|NULL	$value									Der zu validierende String
				*	@param	Boolean		$mandatory=INPUT_MANDATORY			Angabe zu Pflichteingabe
				*	@param	Integer		$maxLength=INPUT_MAX_LENGTH		Die zu prüfende Maximallänge
				*	@param	Integer		$minLength=INPUT_MIN_LENGTH		Die zu prüfende Mindestlänge															
				*
				*	@return	String|NULL												Fehlermeldung | ansonsten NULL
				*
				*/
				function validateInputString($value, $mandatory=INPUT_MANDATORY, $maxLength=INPUT_MAX_LENGTH, $minLength=INPUT_MIN_LENGTH) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateInputString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' | mandatory:$mandatory | [$minLength|$maxLength] ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $mandatory === true AND $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}		
					
					
					#********** MAXIMUM LENGTH CHECK **********#
					
					if( $value !== NULL AND mb_strlen($value) > $maxLength ) {
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein!";
					}
					
					
					#********** MINIMUM LENGTH CHECK **********#
					
					if( $value !== NULL AND mb_strlen($value) < $minLength ) {
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein!";
					}
					
					
					#********** NO ERROR **********#
					return NULL;
					#********** LOCAL SCOPE END **********#
				}
				
				
#************************************************************************************************#

				
				#********************************************#
				#********** VALIDATE EMAIL ADDRESS **********#
				#********************************************#
				
				/**
				*
				*	@param	String|NULL	$value						Der zu übergebende String
				*
				*	@return	String|NULL									Fehlermeldung | ansonsten NULL
				*
				*/
				function validateEmail($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateEmail'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}
					
					
					if( filter_var($value, FILTER_VALIDATE_EMAIL) === false ) {
						// Fehlerfall
						return 'Dies ist keine gültige Email-Adresse!';
					}
					
					
					#********** NO ERROR **********#
					return NULL;
					#********** LOCAL SCOPE END **********#
				}


#************************************************************************************************#

				
				#*******************************************#
				#********** VALIDATE IMAGE UPLOAD **********#
				#*******************************************#
				
				/**
				*
				*	Validiert ein auf den Server hochgeladenes Bild auf korrekten MIME-Type, auf Bildtyp, 
				*	Bildgröße in Pixeln, Dateigröße in Bytes sowie den Header auf Plausibilität.
				*	Generiert einen unique Dateinamen sowie eine sichere Dateiendung und verschiebt das Bild 
				*	in das Zielverzeichnis.
				*
				*	@param	String	$fileTemp															Der temporäre Pfad zum hochgeladenen Bild im Quarantäneverzeichnis
				*	@param	Integer	$imageMaxWidth				=IMAGE_MAX_WIDTH					Die maximal erlaubte Bildbreite in Pixeln				
				*	@param	Integer	$imageMaxHeight			=IMAGE_MAX_HEIGHT					Die maximal erlaubte Bildhöhe in Pixeln
				*	@param	Integer	$imageMaxSize				=IMAGE_MAX_SIZE					Die maximal erlaubte Dateigröße in Bytes
				*	@param	String	$imageUploadPath			=IMAGE_UPLOAD_PATH				Das Zielverzeichnis
				*	@param	Integer	$imageMinSize				=IMAGE_MIN_SIZE					Die minimal erlaubte Dateigröße in Bytes
				*	@param	Array		$imageAllowedMimeTypes	=IMAGE_ALLOWED_MIME_TYPES		Whitelist der zulässigen MIME-Types mit den zugehörigen Dateiendungen
				*
				*	@return	Array		{'imagePath'	=>	String|NULL, 								Bei Erfolg der Speicherpfad zur Datei im Zielverzeichnis | bei Fehler NULL
				*							 'imageError'	=>	String|NULL}								Bei Fehler Fehlermeldung | Bei Erfolg NULL
				*
				*/
				function validateImageUpload( $fileTemp,
														$imageMaxWidth 			= IMAGE_MAX_WIDTH,
														$imageMaxHeight 			= IMAGE_MAX_HEIGHT,
														$imageMaxSize 				= IMAGE_MAX_SIZE,
														$imageUploadPath 			= IMAGE_UPLOAD_PATH,
														$imageMinSize 				= IMAGE_MIN_SIZE,
														$imageAllowedMimeTypes 	= IMAGE_ALLOWED_MIME_TYPES )
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateImageUpload'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$fileTemp' ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#**************************************************************************#
					#********** 1. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER **********#
					#**************************************************************************#
					
					/*
						FILE HEADER
						
						Die Informationen, die immer in jedem Bildheader oder Dateiheader eines Bildes vorhanden sind, können 
						je nach dem spezifischen Bildformat variieren. Es gibt jedoch einige grundlegende Informationen, die in 
						den meisten gängigen Bildformaten vorkommen und als Pflichtangaben angesehen werden. 
						Zu den typischen Pflichtangaben gehören:

						- Dateisignatur  (MIME TYPE): Jedes Bildformat hat eine eindeutige Dateisignatur, die am Anfang der Datei steht und 
						  auf das Format hinweist. Die Dateisignatur ist entscheidend, um das Dateiformat zu identifizieren.

						- Dateigröße: Die Größe der Bilddatei in Bytes oder Kilobytes ist in den meisten Dateiheadern enthalten. 
						  Dies ist wichtig für die Speicherplatzverwaltung und das Einlesen der Datei.

						- Bildabmessungen: Informationen über die Breite und Höhe des Bildes in Pixeln sind entscheidend, um die 
						  richtige Darstellung des Bildes zu gewährleisten. Diese Informationen sind nahezu immer im Dateiheader vorhanden.

						- Farbtiefe: Die Farbtiefe gibt an, wie viele Farben pro Pixel im Bild dargestellt werden können. 
						  Bei RGB-Bildern beträgt die übliche Farbtiefe 24 Bit (8 Bit pro Kanal), was 16,7 Millionen Farben entspricht. 
						  Dies ist eine grundlegende Information im Header.
											  
						  Diese Angaben sind in den meisten gängigen Bildformaten zu finden und gelten als grundlegende Pflichtangaben im 
						  Dateiheader. 
					*/
					/*
						Die Funktion getimagesize() liest den Dateiheader einer Bilddatei aus und 
						liefert bei gültigem MIME Type ('image/...') ein gemischtes Array zurück:
						
						[0] 				Bildbreite in PX (Bildabmessungen)
						[1] 				Bildhöhe in PX  (Bildabmessungen)
						[3] 				Einen für das HTML <img>-Tag vorbereiteten String (width="480" height="532") 
						['bits']			Anzahl der Bits pro Kanal (Farbtiefe)
						['channels']	Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) 
						['mime'] 		MIME Type
						
						Bei ungültigem MIME Type (also nicht 'image/...') liefert getimagesize() false zurück
					*/
					$imageDataArray = getimagesize($fileTemp);
/*					
if(DEBUG_F)		echo "<pre class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageDataArray <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_F)		print_r($imageDataArray);					
if(DEBUG_F)		echo "</pre>";		
*/
	
					#********** CHECK FOR VALID MIME TYPE **********#
					if( $imageDataArray === false ) {
						// Fehlerfall (MIME TYPE IS NO VALID IMAGE TYPE)
						return array( 'imagePath' => NULL, 'imageError' => 'Dies ist keine gültige Bilddatei!' );
						
					} elseif( is_array($imageDataArray) === true ) {
						// Erfolgsfall
						
						/*
							SONDERFALL NUMBER (NUMERIC STRINGS):
							Da wir aus Formularen und anderen Usereingaben alle Werte immer
							als Datentyp String erhalten, macht eine Prüfung auf einen konkreten
							numerischen Datentyp in PHP nur selten Sinn.
							
							Anstatt mittels is_int() direkt auf den Datentyp Integer zu prüfen,
							ist es besser, einen empfangenen String auf sein inhaltliches Format 
							zu prüfen: Ist der String numerisch und entspricht sein Wert einem Integer?

							Die Funktion filter_var() kann mittels eines regulären Ausdrucks, der über
							eine Konstante gesteuert wird, auch einen String auf den Inhalt 'Integer' oder
							'Float' überprüfen.

							Entspricht der mittels filter_var() geprüfte Wert dem zu prüfenden Datenformat,
							nimmt filter_var automatisch eine Typumwandlung vor und liefert den umgewandelten 
							Wert zurück.
						*/
						$imageWidth 	= filter_var($imageDataArray[0], FILTER_VALIDATE_INT);
						$imageHeight 	= filter_var($imageDataArray[1], FILTER_VALIDATE_INT);
						$imageMimeType = sanitizeString($imageDataArray['mime']);
						$fileSize		= fileSize($fileTemp);
if(DEBUG_F)			echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageWidth: $imageWidth px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageHeight: $imageHeight px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileSize: " . round($fileSize/1024, 1) . " kB <i>(" . basename(__FILE__) . ")</i></p>\n";

					} // 1. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER END
					#**************************************************************************#
					
					
					#*****************************************#
					#********** 2. IMAGE VALIDATION **********#
					#*****************************************#
					
					#********** VALIDATE PLAUSIBILITY OF FILE HEADER **********#
					/*
						Diese Prüfung setzt darauf, dass ein maniplulierter Dateiheader nicht konsequent
						gefälscht wurde:
						Ein Hacker ändert den MimeType einer Textdatei mit Schadcode aud 'image/jpg', vergisst
						aber beispielsweise, zusätzlich weitere Einträge wie 'imageWidth' oder 'imageHeight' 
						hinzuzufügen.
						
						Da wir den Datentyp eines im Dateiheader fehlenden Wertes nicht kennen (NULL, '', 0), 
						wird an dieser Stelle ausdrücklich nicht typsicher, sondern auf 'falsy' geprüft.
						Ein ! ('NOT') vor einem Wert oder einer Funktion negiert die Auswertung: Die Bedingung 
						ist erfüllt, wenn die Auswertung false ergibt.
					*/
					if( !$imageWidth OR !$imageHeight OR !$imageMimeType OR $fileSize < $imageMinSize  ) {
						// 1. Fehlerfall (verdächtiger Dateiheader)
						return array( 'imagePath' => NULL, 'imageError' => 'Verdächtiger Dateiheader!' );
					}
					
					
					#********** VALIDATE IMAGE MIME TYPE **********#
					// Whitelist mit erlaubten MIME TYPES
					// $imageAllowedMimeTypes = array('image/jpg' => '.jpg', 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');
					
					/*
						- Die Funktion in_array() prüft, ob eine übergebene Needle einem Wert (value) innerhalb 
						  eines zu übergebenden Arrays entspricht.
						  
						- Die Funktion array_key_exists() prüft, ob eine übergebene Needle einem Index (key) innerhalb 
						  eines zu übergebenden Arrays entspricht.
					*/
					if( array_key_exists($imageMimeType, $imageAllowedMimeTypes) === false ) {
						// 2. Fehlerfall (unerlaubter Bildtyp)
						return array( 'imagePath' => NULL, 'imageError' => 'Dies ist kein erlaubter Bildtyp!' ); 
					}
					
					
					#********** VALIDATE IMAGE WIDTH **********#
					if( $imageWidth > $imageMaxWidth ) {
						// 3. Fehlerfall (Bildbreite zu groß)
						return array( 'imagePath' => NULL, 'imageError' => "Die Bildbreite darf maximal $imageMaxWidth Pixel betragen!" );
					}
					
					
					#********** VALIDATE IMAGE HEIGHT **********#
					if( $imageHeight > $imageMaxHeight ) {
						// 4. Fehlerfall (Bildhöhe zu groß)
						return array( 'imagePath' => NULL, 'imageError' => "Die Bildhöhe darf maximal $imageMaxHeight Pixel betragen!" );
					}
					
					
					#********** VALIDATE FILE SIZE **********#
					if( $fileSize > $imageMaxSize ) {
						// 5. Fehlerfall (Datei zu groß)
						return array( 'imagePath' => NULL, 'imageError' => "Die Dateigröße darf maximal " . $imageMaxSize/1024 . "kB betragen!" );
					
					} // VALIDATE PLAUSIBILITY OF FILE HEADER END
					#**************************************************************************#
					
					
					#*************************************************************#
					#********** 3. PREPARE IMAGE FOR PERSISTANT STORAGE **********#
					#*************************************************************#
					
					#********** GENERATE UNIQUE FILE NAME **********#
					/*
						Da der Dateiname selbst Schadcode in Form von ungültigen oder versteckten Zeichen,
						doppelte Dateiendungen (dateiname.exe.jpg) etc. beinhalten kann, darüberhinaus ohnehin 
						sämtliche, nicht in einer URL erlaubten Sonderzeichen und Umlaute entfernt werden müssten 
						sollte der Dateiname aus Sicherheitsgründen komplett neu generiert werden.
						
						Hierbei muss außerdem bedacht werden, dass die jeweils generierten Dateinamen unique
						sein müssen, damit die Dateien sich bei gleichem Dateinamen nicht gegenseitig überschreiben.
					*/
					
					/*
						- 	mt_rand() stellt die verbesserte Version der Funktion rand() dar und generiert 
							Zufallszahlen mit einer gleichmäßigeren Verteilung über das Wertesprektrum. Ohne zusätzliche
							Parameter werden Zahlenwerte zwischen 0 und dem höchstmöglichem von mt_rand() verarbeitbaren 
							Zahlenwert erzeugt.
							
						- 	str_shuffle() mischt die Zeichen eines übergebenen Strings zufällig durcheinander.
						
						- 	microtime() liefert einen Timestamp mit Millionstel Sekunden zurück (z.B. '0.57914300 163433596'),
							aus dem für eine URL-konforme Darstellung der Dezimaltrenner und das Leerzeichen entfernt werden.
					*/
					$fileName = mt_rand() . '_' . str_shuffle('0123456789_abcdefghijklmnopqrstuvwxyz_0123456789') . '_' . str_replace('.', '', microtime(true));				
// if(DEBUG_F)		echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileName: <i>'$fileName'</i> <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** GENERATE FILE EXTENSION **********#
					/*
						Aus Sicherheitsgründen wird nicht die ursprüngliche Dateinamenerweiterung aus dem
						Dateinamen verwendet, sondern eine vorgenerierte Dateiendung aus dem Array der 
						erlaubten MIME Types.
						Die Dateiendung wird anhand des ausgelesenen MIME Types [key] ausgewählt.
					*/
					$fileExtension = $imageAllowedMimeTypes[$imageMimeType];
// if(DEBUG_F)		echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileExtension: <i>'$fileExtension'</i> <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** GENERATE FILE TARGET **********#
					/*
						Endgültigen Speicherpfad auf dem Server generieren:
						'destinationPath/fileName.fileExtension'
					*/
					$fileTarget = $imageUploadPath . $fileName . $fileExtension;
if(DEBUG_F)		echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileTarget: <i>'$fileTarget'</i> <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debug validateImageUpload value'><b>Line " . __LINE__ . "</b>: Pfadlänge: <i>" . strlen($fileTarget) . "</i> <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					// 3. PREPARE IMAGE FOR PERSISTANT STORAGE END
					#**************************************************************************#
					
					
					#********************************************************#
					#********** 4. MOVE IMAGE TO FINAL DESTINATION **********#
					#********************************************************#
					
					/*
						move_uploaded_file() verschiebt eine hochgeladene Datei an einen 
						neuen Speicherort und benennt die Datei um
					*/
					if( @move_uploaded_file($fileTemp, $fileTarget) === false ) {
						// 6. Fehlerfall (Bild kann nicht verschoben werden)
if(DEBUG_F)			echo "<p class='debug err validateImageUpload'><b>Line " . __LINE__ . "</b>: FEHLER beim Verschieben des Bildes nach <i>'$fileTarget'</i>! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						// TODO: Entrag in ErrorLog / Email an Sysadmin
						return array( 'imagePath' => NULL, 'imageError' => 'Es ist ein Fehler aufgetreten! Bitte kontaktieren Sie unseren Support.' );
						
					} else {
						// Erfolgsfall
if(DEBUG_F)			echo "<p class='debug ok validateImageUpload'><b>Line " . __LINE__ . "</b>: Bild erfolgreich nach <i>'$fileTarget'</i> verschoben. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						return array( 'imagePath' => $fileTarget, 'imageError' => NULL);
					}
					// 4. MOVE IMAGE TO FINAL DESTINATION END
					#**************************************************************************#
									
					
					#********** LOCAL SCOPE END **********#
				}


#************************************************************************************************#
?>