<?php
#********************************************************************************************#
				#********************************************#
				#********** GLOBAL CONFIGURATION **********#
				#********************************************#

				#********** DATABASE CONFIGURATION **********#
				define('DB_SYSTEM', 	'mysql'); 	 
				define('DB_HOST', 		'localhost'); 	
				define('DB_NAME', 		'blogprojekt'); 	
				define('DB_USER', 		'root'); 	
				define('DB_PWD', 		''); 	

				#********** EXTERNAL STRING VALIDATION CONFIGURATION **********#

				define('INPUT_STRING_MANDATORY', true);
				define('INPUT_STRING_MAX_LENGTH', 255);
				define('INPUT_STRING_MIN_LENGTH', 0);
				
				#********** DEBUGGING CONFIGURATION **********#

				// const DEBUG_V = true;
				// das ist auch eine Deklaration von einer Konstante
				define('DEBUG', 	false); 	// -> Debugging for MAIN DOCUMENTS
				define('DEBUG_V', 	false); 	// -> Debugging for VALUES
				define('DEBUG_F', 	false); 	// -> Debugging for FUNCTIONS
				define('DEBUG_DB', 	false); 	// -> Debugging for DATABASE



	
#********************************************************************************************#
?>
