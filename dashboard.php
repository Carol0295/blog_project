<?php
#**************************************************************************************#


				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#

				require_once('./include/config.inc.php');
				require_once('./include/form.inc.php');
				require_once('./include/db.inc.php');
				
#**************************************************************************************#
                #********** INITIALIZE VARIABLES **********#
                $catID               = NULL;
                $catLabel            = NULL;
                $blogHeadline        = NULL;
                $blogContent         = NULL;

                $errorCategory          = NULL;
                $errorBlogHeadline      = NULL;
                $errorBlogContent       = NULL;


                #******************************************#

#**************************************************************************************#

				#********************************************#
				#********** START/CONTINUE SESSION **********#
				#********************************************#

                #********** PREPARE SESSION **********#
                session_name("blogprojekt");

                #********** START/CONTINUE SESSION **********#
                session_start();

                #*******************************************#
                #********** CHECK FOR VALID LOGIN **********#
                #*******************************************#

                if( isset($_SESSION["ID"]) === false || $_SESSION["IPADDRESS"] !== $_SERVER['REMOTE_ADDR'] ){
                    //Fehlerfall (Benutzer ist NICHT eingeloggt)
if(DEBUG)	        echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Login konnte nicht validiert werden. <i>(" . basename(__FILE__) . ")</i></p>\n";

                    #********** DENY PAGE ACCESS **********#
                    // 1. Leere Session Datei löschen
                    session_destroy();

                    // 2. User auf öffentliche Seite umleiten
                    header("LOCATION: ./");

                    // 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
                    exit();
                } else {
                    // Erfolgsfall (Benutzer ist eingeloggt)
if(DEBUG)	        echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Login wurde erfolgreich validiert. <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $userID = $_SESSION['ID'];
if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userID: $userID <i>(" . basename(__FILE__) . ")</i></p>\n";
                    session_regenerate_id();

                }

#**************************************************************************************#

				#*******************************************#
				#********** PROCESS URL PARAMETERS *********#
				#*******************************************#  

                // Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde	
                if( isset($_GET['action']) === true ){
if(DEBUG)	        echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgaben
                    $action = sanitizeString($_GET['action']);

                    #********** PROCESS LOGOUT **********#
                    
                    if( $action === 'logout' ){

                        // 1. Leere Session Datei löschen
                        session_destroy();

                        // 2. User auf öffentliche Seite umleiten
                        header("LOCATION: ./");

                        // 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
                        exit();
                    } // END PROCESS LOGOUT

                } // PROCESS URL PARAMETERS END


#**************************************************************************************#

				#********************************************#
				#********** FETCH USER DATA FROM DB *********#
				#********************************************# 

                // Schritt 1 DB: DB-Verbindung herstellen
                $PDO = dbConnect('blogprojekt');


                // Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
                $sql = "SELECT userFirstName, userLastName 
                        FROM users 
                        WHERE userID = :userID";

                $placeholders = [
                    'userID' => $userID
                ];

                // Schritt 3 DB: Prepared Statements
                try {

                    // Prepare: SQL-Statement vorbereiten
                    $PDOStatement = $PDO->prepare($sql);
                    
                    // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                    $PDOStatement->execute($placeholders);
                    // showQuery($PDOStatement);
                    
                } catch(PDOException $error) {
if(DEBUG) 			echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                }

                // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                $userData = $PDOStatement->fetch(PDO::FETCH_ASSOC);
// if(DEBUG_V)	    echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userData<br>". print_r($userData, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

                dbClose( $PDO, $PDOStatement );

#**************************************************************************************#

				#**********************************************#
				#********** PROCESS FORM NEW CATEGORY *********#
				#**********************************************# 

				#********** PREVIEW POST ARRAY **********#
// if(DEBUG_V)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST<br>". print_r($_POST, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
				#****************************************#

                // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
                if( isset( $_POST['categoryForm']) === true ){
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'categoryForm' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";                   

                    // Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
                    $catLabel   = sanitizeString($_POST['f1']);

if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$catLabel: $catLabel <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 3 FORM: Feld validieren
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    /*
						[x] Validieren der Feldwerte (Feldprüfungen)
						[x] Ausgabe der Fehlermeldungen
						[-] Vorbelegung der Formularfelder für den Fehlerfall 
						[x] Abschließende Prüfung, ob das Formular insgesamt fehlerfrei ist
					*/
                    $errorCategory     = validateInputString($catLabel);

                    #********** FINAL FORM VALIDATION (FIELDS VALIDATION) **********#
                    if( $errorCategory !== NULL ){
                        // Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";
                    } else {
                        // Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular hat keine Fehler <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 4 FORM: Weiterverarbeitung der Formularwerte
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        #***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#
						
						#********** 1. INSERT NEW CATEGORY INTO DB **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Neue Kategorie wird in die DB geschrieben... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 1 DB: DB-Verbindung herstellen
                        $PDO = dbConnect('blogprojekt');

					    #********** 1. CHECK IF CATEGORY IS ALREADY SAVED **********#
if(DEBUG)			    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob Kategorie bereits eingetragen ist... <i>(" . basename(__FILE__) . ")</i></p>\n";
                        
                        // Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
                        $sql = "SELECT COUNT(catLabel) FROM categories WHERE catLabel = :catLabel";

                        $placeholders = [
                            'catLabel' => $catLabel
                        ];

                        // Schritt 3 DB: Prepared Statements
                        try {
                            // Prepare: SQL-Statement vorbereiten
                            $PDOStatement = $PDO->prepare($sql);
                            
                            // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                            $PDOStatement->execute($placeholders);
                            // showQuery($PDOStatement);
                            
                        } catch(PDOException $error) {
if(DEBUG) 				    echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                        }

                        // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                        $count = $PDOStatement->fetchColumn();
if(DEBUG_V)			    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";
                        if( $count !== 0 ){
                            // Fehlerfall
if(DEBUG)	                echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: Die Kategorie ist bereits eingetragen <i>(" . basename(__FILE__) . ")</i></p>\n";

                            $error = "Die Kategorie '$catLabel' existiert bereits.";
                        } else {
                            // Erfolgsfall
                            // Schritt 2 DB: SQL und placeholders 
                            $sql = "INSERT INTO categories 
                                    (catLabel) VALUES (:catLabel)";

                            $placeholders = [
                                'catLabel' => $catLabel
                            ];

                            // Schritt 3 DB: Prepared Statements
                            try {
                                // Prepare: SQL-Statement vorbereiten
                                $PDOStatement = $PDO->prepare($sql);

                                // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                                $PDOStatement->execute($placeholders);
                            } catch (PDOException $error) {
if(DEBUG) 					    echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";

                            }

                            // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                            $rowCount = $PDOStatement->rowCount();
if(DEBUG_V)				    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
                            
                            if( $rowCount !== 1 ){
                                // Fehlerfall
if(DEBUG)	                    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: beim Speichern der neue Kategorie <i>(" . basename(__FILE__) . ")</i></p>\n";
                                $error = "Es ist ein Fehler aufgetreten. Versuchen Sie es bitte später nochmal.";
                            } else {
                                // Erfolgsfall
if(DEBUG)	                    echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neue Kategorie erfolgreich gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";            

                                $success = "Das neue Kategorie ist erfolgreich gespeichert.";

                                $catLabel = NULL;
                                
                            }

                        } // FINAL SAVE NEW CATEGORY INTO DB
                        #*********** CLOSE DB OPERATIONS **********#
                        dbClose($PDOStatement, $PDO);

                    } // FINAL FORM VALIDATION (FIELDS VALIDATION)

                } // FINAL PROCESS FORM NEW CATEGORY



#**************************************************************************************#

				#*********************************************#
				#********** FETCH CATEGORIES FROM DB *********#
				#*********************************************# 

                // Schritt 1 DB: DB-Verbindung herstellen
                $PDO = dbConnect('blogprojekt');

                // Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
                $sql = "SELECT * FROM categories";

                $placeholders = [];

                // Schritt 3 DB: Prepared Statements
                try {
                    // Prepare: SQL-Statement vorbereiten
                    $PDOStatement = $PDO->prepare($sql);
                    
                    // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                    $PDOStatement->execute($placeholders);
                    // showQuery($PDOStatement);
                    
                } catch(PDOException $error) {
if(DEBUG) 			echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                }

                // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                $categoriesArr = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
// if(DEBUG_V)	    echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$categoriesArr<br>". print_r($categoriesArr, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

                dbClose( $PDO, $PDOStatement );


#**************************************************************************************#

				#**********************************************#
				#********** PROCESS FORM NEW PUBLISH *********#
				#**********************************************# 

                #********** PREVIEW POST ARRAY **********#
// if(DEBUG_V)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST<br>". print_r($_POST, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
				#****************************************#

                // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde 
                if( isset($_POST['formPublish']) === true) {
if(DEBUG)	        echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formular 'formPublish' wurde abgeschickt <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
                    $catID           = sanitizeString($_POST['f2']);
                    $blogHeadline    = sanitizeString($_POST['f3']);
                    $blogContent     = sanitizeString($_POST['f4']);

if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$catID: $catID <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogHeadline: $blogHeadline <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogContent: $blogContent <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 3 FORM: Feld validieren
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    /*
						[x] Validieren der Feldwerte (Feldprüfungen)
						[x] Ausgabe der Fehlermeldungen
						[x] Vorbelegung der Formularfelder für den Fehlerfall 
						[x] Abschließende Prüfung, ob das Formular insgesamt fehlerfrei ist
					*/

                    $errorCatID         = validateInputString($catID);
                    $errorBlogHeadline  = validateInputString($blogHeadline);
                    $errorBlogContent   = validateInputString($blogContent, maxLength:5000);

                    #********** FINAL FORM VALIDATION (FIELDS VALIDATION) **********#
                    if( $errorCatID !== NULL || $errorBlogHeadline !== NULL || $errorBlogContent !== NULL ){
                        // Fehlerfall
if(DEBUG)	            echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";

                    } else {
                        // Erfolgsfall
if(DEBUG)	            echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular ist fehlerfei <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 4 FORM: Werte verarbeiten
if(DEBUG)			    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";
                        
                        #************ INSERT PUBLISH INTO DB ************#

                        #***********************************#
                        #********** DB OPERATIONS **********#
                        #***********************************#
if(DEBUG)			    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Neue Beitrag wird in die DB geschrieben... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 1 DB: DB-Verbindung herstellen
                        $PDO = dbConnect('blogprojekt');

                        // Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
                        $sql = "INSERT INTO blogs 
                                    (blogHeadline, blogContent, catID, userID)
                                VALUES (:blogHeadline, :blogContent, :catID, :userID)";

                        $placeholders = [
                            'blogHeadline'  => $blogHeadline,
                            'blogContent'   => $blogContent,
                            'catID'         => $catID,
                            'userID'        => $userID,
                        ];

                        // Schritt 3 DB: Prepared Statements
                        try {
                            // Prepare: SQL-Statement vorbereiten
                            $PDOStatement = $PDO->prepare($sql);
                            
                            // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                            $PDOStatement->execute($placeholders);
                            // showQuery($PDOStatement);
                            
                        } catch(PDOException $error) {
if(DEBUG) 				    echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                        }

                        // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                        $rowCount = $PDOStatement->rowCount();
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

                        if( $rowCount !== 1 ){
                            // Fehlerfall
if(DEBUG)	                echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: beim Speichern des Beitrags <i>(" . basename(__FILE__) . ")</i></p>\n";
                            $error = "Ein Fehler ist aufgetreten. Versuchen Sie es bitte später nochmal.";
                        } else {
                            // Fehlerfall
if(DEBUG)	                echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Beitrag wurde erfolgreich in die DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";
                            
                            $success = "Beitrag wurde erfolgreich gespeichert. Um den Beitrag zu schauen, gehen Sie bitte auf die Frontend-Seite";

                            $blogHeadline   = NULL;
                            $blogContent    = NULL;
                            $catID          = NULL;
                            $userID         = NULL;
                            
                        } // FINAL SAVE NEW PUBLISH INTO DB

                        #*********** CLOSE DB OPERATIONS **********#
                        dbClose($PDO, $PDOStatement);
                     
                    } // FINAL FORM VALIDATION (FIELDS VALIDATION)
                } // FINAL PROCESS FORM NEW PUBLISH

#**************************************************************************************#
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">

        <title>Dashboard</title>
    </head>
    <body>
        <div class="login-form">
            <p><a href="?action=logout">Logout</a></p>
            <br>
            <p><a href="./">Zum Frontend</a></p>
        </div>

        <h1>PHP-Projekt Blog - Dashboard</h1>
        <label for="activeUser">Aktiver Benutzer: <?php echo  $userData['userFirstName'] . " ". $userData['userLastName']; ?></label>
        <br>

        <!-- -------- USER MESSAGES START -------- -->
        <?php if( isset($error) === true ): ?>
            <h3 class="error"><?= $error ?></h3>
        <?php elseif( isset($success) === true ): ?>
            <h3 class="success"><?= $success ?></h3>
        <?php endif ?>
        <!-- -------- USER MESSAGES END -------- -->

        <div class="form-wrapper">
            <div class="blog-form">
                <h3>Neuen Blog-Eintrag verfassen</h3>
                <br>
                <form action="" method="POST">
                    <input type="hidden" name="formPublish">

                    <select name="f2">
                        <?php foreach($categoriesArr AS $category): ?>
                            <option value="<?= $category['catID'];?>" <?php if($category['catID'] == $catID ) echo 'selected' ?>>
                                <?= $category['catLabel'] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <br>
                    <span class="error"><?= $errorBlogHeadline; ?></span><br>
                    <input type="text" name="f3" placeholder="Überschrift" value="<?= $blogHeadline ?>">
                    <br>
                    <span class="error"><?= $errorBlogContent; ?></span><br>
                    <textarea name="f4" placeholder="Text"><?= $blogContent ?></textarea>

                    <input type="submit" value="Veröffentlichen">
                </form> 
            </div>

            <div class="category-form">
                <h3>Neue Kategorie anlegen</h3>
                <br>
                <form action="" method="POST">
                    <input type="hidden" name="categoryForm">

                    <span class="error"><?= $errorCategory; ?></span><br>
                    <input type="text" name=f1 value="<?= $catLabel; ?>">
                    <input type="submit" value="Neue Kategorie anlegen">
                </form>
            </div>
            
        </div>
    </body>
</html>