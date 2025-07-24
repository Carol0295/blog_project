<?php
#********************************************************************************************#


				#********************************************#
				#************ PAGE CONFIGURATION ************#
				#********************************************#

				require_once("./include/config.inc.php");
				require_once("./include/form.inc.php");
				require_once("./include/db.inc.php");
				require_once("./include/dateTime.inc.php");

                #********** INITIALIZE VARIABLES **********#

                $loggedIn       = false;
                $userFirstName  = NULL;
                $userLastName   = NULL;

                $filterCategoryID   = NULL;

                $errorLogin     = NULL;

                #******************************************#

#**************************************************************************************#

				#********************************************#
				#********** START/CONTINUE SESSION **********#
				#********************************************#

                #********** PREPARE SESSION **********#
                session_name("blogprojekt");

                #********** START/CONTINUE SESSION **********#
                session_start();

// if(DEBUG_V)	    echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION<br>". print_r($_SESSION, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

                #*******************************************#
                #********** CHECK FOR VALID LOGIN **********#
                #*******************************************#

                if( isset($_SESSION['ID']) === false || $_SESSION['IPADDRESS'] !== $_SERVER['REMOTE_ADDR'] ){
                    // Fehlerfall (Benutzer ist NICHT eingeloggt)
if(DEBUG)	        echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: Benutzer ist nicht eingeloggt <i>(" . basename(__FILE__) . ")</i></p>\n";
                    session_destroy();
                    $loggedIn = false;
                    
                } else {
                    // Erfolgsfall (Benutzer ist eingeloggt)
if(DEBUG)           echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Benutzer ist eingeloggt <i>(" . basename(__FILE__) . ")</i></p>\n";
                    session_regenerate_id(true);
                    $userID = $_SESSION['ID'];

if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: Eingeloggt mit \$userID: $userID <i>(" . basename(__FILE__) . ")</i></p>\n";
                    $loggedIn = true;


                } // END CHECK FOR VALID LOGIN


#**************************************************************************************#

				#*********************************************#
				#********** FETCH CATEGORIES FROM DB *********#
				#*********************************************# 
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Fetching Kategorien aus der DB <i>(" . basename(__FILE__) . ")</i></p>\n";

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

                $categoriesArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
// if(DEBUG_V)	    echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$categoriesArray<br>". print_r($categoriesArray, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";


                // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                dbClose( $PDO, $PDOStatement );

                
#**************************************************************************************#

				#*******************************************#
				#********** PROCESS URL PARAMETERS *********#
				#*******************************************#  

                // Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde	
                if( isset($_GET['action']) === true ){
if(DEBUG)	        echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben <i>(" . basename(__FILE__) . ")</i></p>\n";
// if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_GET<br>". print_r($_GET, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
                    // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgaben
                    $action = sanitizeString($_GET['action']);

                    #********** PROCESS LOGOUT **********#
                    
                    if( $action === 'logout' ){
if(DEBUG)	            echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Processing logout... <i>(" . basename(__FILE__) . ")</i></p>\n";
                        // 1. Leere Session Datei löschen
                        session_destroy();

                        // 2. User auf öffentliche Seite umleiten
                        header("LOCATION: ./");

                        // 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
                        exit();

                    } elseif( $action === 'showCategory' ){

                        // PROCESS CATEGORY PARAMETER
                        if( isset( $_GET['id'] ) === true ){
if(DEBUG)	                echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Kategorie filter wird verarbeitet...  <i>(" . basename(__FILE__) . ")</i></p>\n";

if(DEBUG)		            echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
                            // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgaben
                            $filterCategoryID = sanitizeString($_GET['id']);
if (DEBUG)                  echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Kategorie-ID übergeben: $filterCategoryID</p>\n";
                        } // END PROCESS CATEGORY PARATEMER

                        #*********** EDIT/DELETE POST PROCESS **********#
                        // Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
                    } elseif( ($action === 'edit' || $action === 'delete') && $loggedIn === true ) {

                        if( isset($_GET['postId']) === true ){
                            // Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgaben
                            $blogIDToProcess = sanitizeString($_GET['postId']);
if(DEBUG_V)	                echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogIDToProcess: $blogIDToProcess <i>(" . basename(__FILE__) . ")</i></p>\n";
                            
                            #*********** DELETE POST PROCESS START **********#
                            if( $action === 'delete' ){
if(DEBUG)	                    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: DELETE process wird gestartet... <i>(" . basename(__FILE__) . ")</i></p>\n";
                                $PDO = dbConnect('blogprojekt');

                                $sql = "DELETE FROM blogs WHERE blogID = :blogID";
                                
                                $placeholders= [
                                    'blogID' => $blogIDToProcess
                                ];

                                try{
                                    $PDOStatement = $PDO->prepare($sql);

                                    $PDOStatement->execute($placeholders);
                                } catch (PDOException $error) {
if(DEBUG) 			                echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                                }

                                $rowCount = $PDOStatement->rowCount();
if(DEBUG_V)				        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

                                if( $rowCount === 0 ){
                                    // Fehlerfall Kein Datensatz wurde gelöscht.
if(DEBUG)					        echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Es wurden keine Datensätze gelöscht <i>(" . basename(__FILE__) . ")</i></p>\n";

                                    $info = 'Es wurden keine Datensätze gelöscht';
                                } else {
                                    // Erfolgsfall 
if(DEBUG)					        echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: $rowCount Posts wurden erfolgreich gelöscht. <i>(" . basename(__FILE__) . ")</i></p>\n";
                                    // Erfolgsmeldung für User
                                    $success = "$rowCount Posts wurden erfolgreich gelöscht.";

                                    // Schließen der DB-Verbindung
                                    dbClose($PDO, $PDOStatement);

                                }
                            } // END DELETE POST PROCESS 
                        } // END PROCESS POST-ID
                    } // BRANCHING END
                } // END PROCESS URL PARAMETERS


#********************************************************************************************#
                    #****************************************#
                    #********** FETCH POSTS FROM DB *********#
                    #****************************************# 

if(DEBUG)	        echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Fetching Blog Beiträge aus der Datenbank... <i>(" . basename(__FILE__) . ")</i></p>\n";
                    // Schritt 1 DB: DB-Verbindung herstellen
                    $PDO = dbConnect('blogprojekt');

                    // Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
                    $sql = "SELECT * 
                            FROM blogs
                            INNER JOIN users USING(userID)
                            INNER JOIN categories USING(catID) ";
                    
                    $placeholders = [];

                    if( $filterCategoryID !== NULL ){
                        $sql .= " WHERE catID = :catID";
                        $placeholders['catID'] = $filterCategoryID;
                    }

                    $sql .= " ORDER BY blogDate DESC";
                    
                    // Schritt 3 DB: Prepared Statements
                    try {
                        // Prepare: SQL-Statement vorbereiten
                        $PDOStatement = $PDO->prepare($sql);
                        
                        // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                        $PDOStatement->execute($placeholders);
                        // showQuery($PDOStatement);
                        
                    } catch(PDOException $error) {
if(DEBUG) 			    echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                    }

                    $postsArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
// if(DEBUG_V)	        echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$postsArray<br>". print_r($postsArray, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";


                    // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                    dbClose( $PDO, $PDOStatement );


#**************************************************************************************#

				#****************************************#
				#********** PROCESS LOGIN FORM **********#
				#****************************************#        


				#********** PREVIEW POST ARRAY **********#
// if(DEBUG_V)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST<br>". print_r($_POST, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
				#****************************************#

                // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
                if( isset( $_POST['logginForm']) === true ){
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'logginForm' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
                    // Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
                    $userEmail      = sanitizeString($_POST['f1']);
                    $userPassword   = sanitizeString($_POST['f2']);

// if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userEmail: $userEmail <i>(" . basename(__FILE__) . ")</i></p>\n";
// if(DEBUG_V)	        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userPassword: $userPassword <i>(" . basename(__FILE__) . ")</i></p>\n";
                    
                    // Schritt 3 FORM: Feld validieren
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    /*
						[x] Validieren der Feldwerte (Feldprüfungen)
						[x] Ausgabe der Fehlermeldungen
						[-] Vorbelegung der Formularfelder für den Fehlerfall 
						[x] Abschließende Prüfung, ob das Formular insgesamt fehlerfrei ist
					*/
                    $errorEmail     = validateEmail($userEmail);
                    $errorPassword  = validateInputString($userPassword);

                    #********** FINAL FORM VALIDATION (FIELDS VALIDATION) **********#
                    if( $errorEmail !== NULL || $errorPassword !== NULL ) {
                        //Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";
                        $errorLogin = "Benutzername oder Password sind falsch";

                    } else {
                        // Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION (FIELDS VALIDATION) Das Formular hat keine Fehler <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 4 FORM: Weiterverarbeitung der Formularwerte
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        #***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#
						
						#********** 1. FETCH USER DATA FROM DB **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Userdaten aus DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        // Schritt 1 DB: DB-Verbindung herstellen
                        $PDO = dbConnect('blogprojekt');

                        // Schritt 2 DB: SQL und placeholders 
                        $sql = "SELECT userID, userFirstName, userLastName, userEmail, userCity, userPassword
                                FROM users
                                WHERE userEmail = :userEmail";

                        $placeholders = [
                            'userEmail' => $userEmail
                        ];

                        // Schritt 3 DB: Prepared Statements
                        try{
                            // Prepare: SQL-Statement vorbereiten
                            $PDOStatement = $PDO->prepare($sql);

                            // Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
                            $PDOStatement->execute($placeholders);
                        } catch (PDOException $error) {
if(DEBUG) 					echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                        }

                        // Schritt 4 DB: Ergebnis der DB-Operation auswerten und schließen der Datenbankverbindung
                        $userProfilData = $PDOStatement->fetch(PDO::FETCH_ASSOC);
// if(DEBUG_V)				echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$userProfilData<br>". print_r($userProfilData, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

                        #********** CLOSE DB CONNECTION **********#
						dbClose($PDO, $PDOStatement);

                        #********** 1. VALIDATE EMAIL-ADRESS **********#
                        if( $userProfilData === false ){
                            // Fehlerfall (ungültige Email Adresse)   
if(DEBUG)	                echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Email $userEmail wurde nicht in die DB gefunden <i>(" . basename(__FILE__) . ")</i></p>\n";
                            $errorLogin = "Benutzername oder Password sind falsch";
                        } else {
                            // Erfolgsfall (gültige Email Adresse)   
if(DEBUG)	                echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Email $userEmail wurde in die DB gefunden <i>(" . basename(__FILE__) . ")</i></p>\n";
                            
                            #********** 2. VALIDATE PASSWORD **********#
                            if( password_verify($userPassword, $userProfilData['userPassword']) === false ){
                                // Fehlerfall
if(DEBUG)	                    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Passwort stimmt nicht überein mit dem aus der DB! <i>(" . basename(__FILE__) . ")</i></p>\n"; 
                                $errorLogin = "Benutzername oder Password sind falsch";
                            } else {
                                // Erfolgsfall
if(DEBUG)	                    echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Passwort stimmen überein mit der aus der DB <i>(" . basename(__FILE__) . ")</i></p>\n";

                                #********** 3. PROCESS LOGIN **********#
if(DEBUG) 						echo "<p class='debug'><b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";

                                #********** 4a. PREPARE SESSION **********#
                                session_name("blogprojekt");

                                #********** 4b. PREPARE SESSION **********#
                                if( session_start() === false ){
                                    // Fehlerfall
if(DEBUG)	                        echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: Fehler beim starten des Sessions <i>(" . basename(__FILE__) . ")</i></p>\n";
                                    $errorLogin = 'Der Login kann nicht durchgeführt werden!<br> 
									Bitte überprüfen Sie, ob in Ihrem Browser die Annahme von Cookies aktiviert ist.';
                                } else {
                                    // Erfolgsfall
if(DEBUG)	echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Session ist erfolgreich gestartet <i>(" . basename(__FILE__) . ")</i></p>\n";                               

                                    #********** 4c. SAVE ACCOUNT DATA INTO SESSION FILE **********#
                                    $_SESSION['ID'] = $userProfilData['userID'];
                                    $_SESSION['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];

                                    #********** 4d. REDIRECT TO INTERNAL PAGE **********#
                                    header("LOCATION: ./dashboard.php");

                                } // 3. PROCESS LOGIN END

                            } // 2. VALIDATE PASSWORD

                        } // 1. VALIDATE EMAIL ADRESS END

                    } // FINAL FORM VALIDATION (FIELDS VALIDATION)

                } // FINAL PROCESS LOGIN FORM

?>

<!doctype html>

<html lang="de">
	
	<head>	
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">

        <link rel="stylesheet" href="fontawesome/css/all.min.css">


		<title>PHP-Projekt Blog</title>

	</head>
	
	<body>
    <header>
        <h1>PHP-Projekt Blog</h1>
            <?php if( $loggedIn === false ): ?>
                <form action="" method="post" class="login-form">
                    <input type="hidden" name="logginForm">
                    <fieldset>
                        <legend>Login</legend>

                        <div class="input-group">
                            <input class="short" type="text" name="f1" placeholder="E-mail">
                            <input class="short" type="password" name="f2" placeholder="Password">
                        </div>
                        
                        <input class="short" type="submit" value="login">
                        
                    </fieldset>
                    <span class="error"><?= $errorLogin ?></span><br>
                </form>
            <?php elseif( $loggedIn === true ): ?>
                <div class="login-form">
                    <p><a href="?action=logout">Logout</a></p>
                    <br>
                    <p><a href="dashboard.php">Zum Dashboard</a></p>
                </div>
            <?php endif ?>
    </header>
		
        <nav><a href="./">Alle Einträge anzeigen</a></nav><br>
		
        <!-- -------- USER MESSAGES START -------- -->
		<?php if( isset($error) === true ): ?>
		<h3 class="error"><?= $error ?></h3>
		<?php elseif( isset($success) === true ): ?>
		<h3 class="success"><?= $success ?></h3>
		<?php elseif( isset($info) === true ): ?>
		<h3 class="info"><?= $info ?></h3>
		<?php endif ?>
		<!-- -------- USER MESSAGES END -------- -->

        <div class="content-wrapper">
            <main class="posts">
                <?php foreach( $postsArray AS $post ): ?>
                    <article class="publishContent">
                        <?php if($loggedIn === true): ?>
                            <editor><a href="?action=edit&postId=<?= $post['blogID']?>"><i class="fa-solid fa-pen"></i></a></editor>
                            <editor><a href="?action=delete&postId=<?= $post['blogID']?>"><i class="fa-solid fa-trash"></i></a></editor>
                        <?php endif ?>
                        <label for="categoryName" class="categoryLabel"> Kategorie: <?= $post['catLabel'] ?></label>
                        <h3><?= $post['blogHeadline']; ?></h3>

                        <label for="contentUser">
                            <?= $post['userFirstName'] ?> <?= $post['userLastName'] ?> (<?= $post['userCity'] ?>) schrieb am <?= isoToEuDateTime($post['blogDate'])['date'] ?> um <?= isoToEuDateTime($post['blogDate'])['time'] ?> Uhr
                        </label>
                        <br>
                        <p><?= nl2br($post['blogContent']) ?></p>
                    </article>
                <?php endforeach ?>
            </main>


            <aside class="categories">
                <h2>Kategorien</h2>
                <?php foreach($categoriesArray AS $categories): ?> 
                    <div class="<?php if($categories['catID'] == $filterCategoryID) echo 'selectedCategory' ?>">
                        <a href="?action=showCategory&id=<?= $categories['catID'] ?>"><?= $categories['catLabel']?></a>
                    </div>
                <?php endforeach ?> 
            </aside>
        <div>
		<br>
		<br>
		<br>

	</body>
	
</html>