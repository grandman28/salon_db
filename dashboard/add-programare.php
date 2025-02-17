<?php
session_start();
$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_client = $_POST['id_client'];
    $id_serviciu = $_POST['id_serviciu'];
    $id_angajat = $_POST['id_angajat'];
    $data = strtotime($_POST['data']); 
    $durata = $_POST['durata'] ? $_POST['durata'] : null;
    $mentiuni = $_POST['mentiuni'] ?: null;
    $erori = [];

   
    if($durata == null){    
        $durataQuery = "SELECT Durata FROM Servicii WHERE ID_Serviciu = ?";
        $stmt1 = sqlsrv_query($conn, $durataQuery, array($id_serviciu));
        if ($stmt1 === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $stmt1 = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);
        $durata = $stmt1['Durata'];
        
    }

    $query = "SELECT P.ID_Programare, P.Data, P.ID_Serviciu, P.Durata, S.Durata AS DurataServiciu, A.Nume + ' ' + A.Prenume AS NumeAngajat
    FROM Programari P
    JOIN Servicii S ON P.ID_Serviciu = S.ID_Serviciu
    JOIN Angajati A ON P.ID_Angajat = A.ID_Angajat
    WHERE P.ID_Angajat = ?";
    $stmt = sqlsrv_query($conn, $query, array($id_angajat));
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $dataProgramare = $row['Data'];
        $durataExistenta = $row['Durata'] ?? $row['DurataServiciu'];
        $oraSfarsitVerificata =$data + $durata->format('H')*3600 + $durata->format('i')*60;
        $dataProgramare = strtotime($dataProgramare->format('Y-m-d H:i'));
        $oraSfarsitExistenta = $dataProgramare + $durataExistenta->format('H')*3600 + $durataExistenta->format('i')*60;
        
        if (
            ($data >= $dataProgramare && $data < $oraSfarsitExistenta) ||
            ($oraSfarsitVerificata > $dataProgramare && $oraSfarsitVerificata <= $oraSfarsitExistenta)
        ) {
            $erori[] = "Angajatul " . $row["NumeAngajat"] . " are deja o programare între " . date("Y-m-d H:i", $dataProgramare). " și " . date("Y-m-d H:i", $oraSfarsitExistenta) . ".<br>";
        }
    }
    
    if (empty($erori)) {
        $query = "INSERT INTO Programari (ID_Client, ID_Serviciu, ID_Angajat, Data, Durata, Mentiuni) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $query, array(
            $id_client,
            $id_serviciu,
            $id_angajat,
            date('Y-m-d H:i', $data),
            date('H:i', $durata),
            $mentiuni
        ));

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $_SESSION['message'] = "Programarea a fost adăugată cu succes.";
        $_SESSION['class'] = "bg-success";
        header("Location: /bd/dashboard/programari.php");
        exit();
    } else {
    
        $_SESSION['message'] = implode(" ", $erori);
        $_SESSION['class'] = "bg-danger";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="/bd/styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css"
        integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"
        integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
</head>

<body>
    <nav>
        <ul>
            <li><a class="navlink" href="/bd/index.html">Acasa</a></li>
            <li><a class="navlink" href="/bd/servicii.html">Servicii</a></li>
            <li><a class="navlink" href="/bd/despre.html">Despre Noi</a></li>
            <li><a class="navlink" href="/bd/contact.html">Contact</a></li>
        </ul>
        <div class="login-nav">

            <a class="navlink" href="/bd/logout.php">Log Out</a>
        </div>
    </nav>
    <header>
        <h1>Tinker Bell</h1>
        <p>Salon de cosmetică</p>
    </header>
    <div class="welcome-admin">

    </div>
    <div class="container" style="margin-left: 4vw;">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="/bd/dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="/bd/dashboard/facturi.php" class="list-group-item list-group-item-action">Facturi</a>
                    <a href="/bd/dashboard/clienti.php" class="list-group-item list-group-item-action">Clienți</a>
                    <a href="/bd/dashboard/programari.php" class="list-group-item list-group-item-action">Programări</a>
                    <a href="/bd/dashboard/servicii.php" class="list-group-item list-group-item-action">Servicii</a>
                    <a href="/bd/dashboard/produse.php" class="list-group-item list-group-item-action">Produse</a>
                    <a href="/bd/dashboard/inventar.php" class="list-group-item list-group-item-action">Inventar</a>
                    <?php
                    if ($_SESSION['user'] == "admin") {

                        echo '<a href="/bd/dashboard/angajati.php" class="list-group-item list-group-item-action">Angajati</a>';
                        echo '<a href="/bd/dashboard/salarii.php" class="list-group-item list-group-item-action">Salarii</a>';
                        echo '<a href="/bd/dashboard/rapoarte.php" class="list-group-item list-group-item-action">Rapoarte</a>';
                    } ?>
                </div>
            </div>
            <div class="col-md-10">
                <main role="main" class="col-md-12 px-4">
                    <div class="container" style="margin-top: 20px;">
                        <h2>Adauga programare:</h2>

                        <!-- Form pentru filtre -->
                        <form method="POST">

                            <div class="row mb-4">
                                <label for="id_client" class="col-sm-2 col-form-label">Nume Client:</label>
                                <div class="col-sm-6">
                                    <select id="id_client" name="id_client" class="form-select" required>
                                        <option value="">Alegeți un client...</option>
                                        <?php
                                        $query = "SELECT ID_Client, Nume, Prenume, Telefon FROM Clienti";
                                        $stmt = sqlsrv_query($conn, $query);
                                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                            echo "<option value='" . $row['ID_Client'] . "'>" . $row['Nume'] . " " . $row['Prenume'] . " - " . $row['Telefon'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="id_serviciu" class="col-sm-2 col-form-label">Nume Serviciu:</label>
                                <div class="col-sm-6">
                                    <select id="id_serviciu" name="id_serviciu" class="form-select" required>
                                        <option value="">Alegeți un serviciu...</option>
                                        <?php
                                        $query = "SELECT ID_Serviciu, Nume, Pret, Durata FROM Servicii";
                                        $stmt = sqlsrv_query($conn, $query);
                                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                            echo "<option value='" . $row['ID_Serviciu'] . "'>" . $row['Nume'] . " - " . $row['Pret'] . " RON - " . $row['Durata']->format('H:i') . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="id_angajat" class="col-sm-2 col-form-label">Nume Angajat:</label>
                                <div class="col-sm-6">
                                    <select id="id_angajat" name="id_angajat" class="form-select" required>
                                        <option value="">Alegeți un angajat...</option>
                                        <?php
                                        $query = "SELECT ID_Angajat, Nume, Prenume FROM Angajati";
                                        $stmt = sqlsrv_query($conn, $query);
                                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                            echo "<option value='" . $row['ID_Angajat'] . "'>" . $row['Nume'] . " " . $row['Prenume'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="data" class="col-sm-2 col-form-label">Data:</label>
                                <div class="col-sm-3">
                                    <input type="datetime-local" class="form-control" id="data" name="data"
                                        value="<?php echo date('Y-m-d H:i'); ?>" step = "60" min="0" required>
                                </div>
                            </div>
                            <div class = "row mb-4">
                                <label for="durata" class="col-sm-2 col-form-label">Durata <br><small><em>Doar dacă e diferită de cea a serviciului</em></small></label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control" id="durata" name="durata" 
                                        step="60" ></div>
                            </div>
                            <div class = "row mb-4">
                                <label for="mentiuni" class="col-sm-2 col-form-label">Mentiuni</label>
                                <div class="col-sm-6">
                                    <textarea type="text" class="form-control" id="mentiuni" name="mentiuni" ></textarea>
                                </div>
                                </div>
 
                            <div class="row mb-2 mt-4">
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-success">Adaugă programare</button>
                                </div>
                                <div class="col-sm-1">
                                    <a href="/bd/dashboard/programari.php" class="btn btn-secondary">Înapoi</a>
                                </div>
                                <div class="col-sm-1">
                                    <input type="reset" class="btn btn-secondary" value="Reseteaza">
                                </div>
                            </div>
                        </form>



                        <?php
                        if (isset($_SESSION['message'])) {
                            $message = $_SESSION['message'];
                            $class = $_SESSION['class'];
                            unset($_SESSION['message'], $_SESSION['class']);
                        }
                        ?>
                        <div id="responseDiv" class="toast <?php echo $class; ?>"
                            style="display: <?php echo isset($message) ? 'block' : 'none'; ?>;" role="alert"
                            aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body flex-grow-2 "><?php echo $message; ?></div>
                                <button type="button" class="btn-close p-3" aria-label="Close"
                                    onclick="hideToast()"></button>
                            </div>
                        </div>
                    </div>


                    <script>
                        $(document).ready(function () {
                            $('#id_client').selectize({
                                placeholder: 'Tastează pentru a căuta...',
                                sortField: 'text'
                            })
                            $('#id_serviciu').selectize({
                                placeholder: 'Tastează pentru a căuta...',
                                sortField: 'text'
                            })
                            $('#id_angajat').selectize({
                                placeholder: 'Tastează pentru a căuta...',
                                sortField: 'text'
                            })
                        });
                        
                        

                        
                        function showToast() {
            const params = getQueryParams();
            if (params.message) {
                const responseDiv = document.getElementById("responseDiv");
                const toastMessage = document.getElementById("toastMessage");
                toastMessage.textContent = params.message;
                responseDiv.classList.add(params.class);
                responseDiv.style.display = "block";
            }
        }

        function hideToast() {
            const responseDiv = document.getElementById("responseDiv");
            responseDiv.style.display = "none";
        }

        window.onload = showToast;

                    </script>
            </div>
            </main>
        </div>
    </div>
    </div>
</body>

</html>