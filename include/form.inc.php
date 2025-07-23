<?php
#********************************************************************************************#
				#********************************************#
				#********** GLOBAL CONFIGURATION **********#
				#********************************************#

				#**********************************************#
				#********** SANITIZING STRINGS **********#
				#**********************************************#


				/**
				*
				* Ersetzt potentiell gefährliche Steuerzeichen durch HTML-Entities
				* Entfernt vor und nach einem String Whitespaces
				*
				* @params String $value 	Die zu bereinigende Zeichenkette
				*
				* @return String	 		Die bereinigte Zeichenkette
				*
				*/
				function sanitizeString( $value ){
					#********** LOCAL SCOPE START **********#

if(DEBUG_F)			echo "<p class='debug sanitizeString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false );

					$value = trim( $value );

					/*
					Leerstrings aus dem Formular in NULL umwandeln, damit in der DB vorhandene
					NULL-Werte nicht mit Leerstrings überschrieben werden.
					*/
					if( $value === '' ) $value = NULL;

					// Entschärften und getrimmten Wert zurückgeben
					return $value;


					#********** LOCAL SCOPE END **********#


				}
				#*******************************************************#


				#**********************************************#
				#********** VALIDATE INPUT STRINGS ************#
				#**********************************************#	

				/**
				*
				* Prüft einen übergebenen String auf Maximallänge sowie optional 
				* auf Mindestlänge und Pflichtangabe.
				* Generiert Fehlermeldung bei Leerstring und gleichzeitiger Pflichtangabe 
				* oder bei ungültiger Länge.
				*
				* @param String 	$value 									Der zu validierende String
				* @param Boolean 	$mandatory=INPUT_STRING_MANDATORY 		Angabe zu Pflichteingabe
				* @param Integer 	$maxLength=INPUT_STRING_MAX_LENGTH 		Die zu prüfende Maximallänge
				* @param Integer 	$minLength=INPUT_STRING_MIN_LENGTH 		Die zu prüfende Mindestlänge 
				*
				* @return String|NULL 										Fehlermeldung | ansonsten NULL
				*
				*/
				function validateInputString( 
					$value, 
					$mandatory = INPUT_STRING_MANDATORY, 
					$maxLength = INPUT_STRING_MAX_LENGTH,
					$minLength = INPUT_STRING_MIN_LENGTH){ 
					#********** LOCAL SCOPE START **********#

if(DEBUG_F)			echo "<p class='debug validateInputString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "( '$value' [$minLength | $maxLength]  ) <i>(" . basename(__FILE__) . ")</i></p>\n";

					#************ MANDATORY CHECK *************#

					if( $mandatory === true && $value === NULL ){
						// Fehlerfall
						return "Field is required";
					}


					#********** MAXIMUM LENGTH CHECK **********#

					/*
					Da die Übergabe von NULL an PHP-eigene Funktionen in künftigen PHP-Versionen 
					nicht mehr erlaubt ist, muss vor jedem Aufruf einer PHP-Funktion sichergestellt 
					werden, dass der zu übergebende Wert nicht NULL ist.
					*/	
					if( $value !== NULL && mb_strlen($value) > $maxLength ){
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein! ";
					}


					#********** MINIMUM LENGTH CHECK **********#


					if( $value !== NULL && mb_strlen($value) < $minLength ){
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein! ";
					}

					return NULL;

					#********** NO ERROR **********#
					#********** LOCAL SCOPE END **********#
				}
	
#********************************************************************************************#


				#**********************************************#
				#********** VALIDATE EMAIL ADDRESS ************#
				#**********************************************#

				/**
				*
				* Prüft einen übergebenen String auf eine valide Email-Adresse und auf Leerstring.
				* Generiert Fehlermeldung bei ungültiger Email-Adresse oder Leerstring
				*
				* @param String $value Der zu übergebende String
				*
				* @return String|NULL Fehlermeldung | ansonsten NULL
				*
				*/
				function validateEmail( 
					$value, 
					$mandatory = INPUT_STRING_MANDATORY){ 
					#********** LOCAL SCOPE START **********#

if(DEBUG_F)			echo "<p class='debug validateInputString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "(  ) <i>(" . basename(__FILE__) . ")</i></p>\n";

					#************ MANDATORY CHECK *************#

					if( $mandatory === true && $value === NULL ){
						// Fehlerfall
						return "Field is required";
					}

					#************ VALIDATE EMAIL ADDRESS FORMAT *************#

					if( filter_var( $value, FILTER_VALIDATE_EMAIL ) === false ) {
						return 'Email is not valid';
					}

					return NULL;

					#********** NO ERROR **********#
					#********** LOCAL SCOPE END **********#
				}
	
#********************************************************************************************#
?>
