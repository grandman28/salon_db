<?php
session_start();
$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id_programare = isset($_GET['id']) ? $_GET['id'] : null;
    $idc = isset($_GET['idc']) ? $_GET['idc'] : null;
}


// Procesare formular
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_client = $_POST['id_client'];
    $data = $_POST['data'];
    $programari = $_POST['programari'];
    $suma = $_POST['pret']; 
        $erori = [];
        foreach ($programari as $id_programare) {
            $checkQuery = "SELECT id_factura FROM Programari WHERE ID_Programare = ?";
            $checkStmt = sqlsrv_query($conn, $checkQuery, array($id_programare));
            if ($checkStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            if ($checkRow['id_factura'] !== null) {
                $erori[] = "Programarea cu ID-ul $id_programare are deja o factură asociată.";
            }
        }

        if (count($erori) > 0) {
            $_SESSION['erori'] = $erori;
            $_POST = [];
        } else {
            $addInvoiceQuery = "INSERT INTO Facturi (Id_Client, Data, Suma) VALUES (?, ?, ?)";
            $addInvoiceStmt = sqlsrv_query($conn, $addInvoiceQuery, array($id_client, $data, $suma));
            $invoiceQuery = "SELECT TOP 1 ID_Factura FROM Facturi WHERE ID_Client = ? AND Data = ? ORDER BY ID_Factura DESC";
            $invoiceStmt = sqlsrv_query($conn, $invoiceQuery, array($id_client, $data));
            $invoiceRow = sqlsrv_fetch_array($invoiceStmt, SQLSRV_FETCH_ASSOC);
            $id_factura = $invoiceRow['ID_Factura'];
            foreach ($programari as $id_programare) {
                $insertQuery = "UPDATE Programari SET id_factura = ? WHERE ID_Programare = ?";
                $params = array($id_factura, $id_programare);
                $insertStmt = sqlsrv_query($conn, $insertQuery, $params);
                if ($insertStmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }


            $_SESSION['message'] = "Factura a fost adăugată cu succes.";
            $_SESSION['class'] = "bg-success";
            header("Location: /bd/dashboard/facturi.php");
            exit();
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
                        <h2>Adauga factură</h2>

                        <!-- Form pentru filtre -->
                        <form method="POST">

                            <div class="row mb-4">
                                <label for="id_client" class="col-sm-2 col-form-label">Nume Client:</label>
                                <div class="col-sm-6">
                                    <select id="id_client" name="id_client" class="form-select" required>
                                        <option value="">Alegeți un client...</option>
                                        <?php
                                        $query = "SELECT C.ID_Client, C.Nume, C.Prenume, C.Telefon FROM Clienti C";
                                        $stmt = sqlsrv_query($conn, $query);
                                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                            echo "<option value='" . $row['ID_Client']."'";
                                            if($idc == $row['ID_Client']) {
                                                echo " selected";}
                                            echo ">" . $row['Nume'] . " " . $row['Prenume'] . " - " . $row['Telefon'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label for="data" class="col-sm-2 col-form-label">Data:</label>
                                <div class="col-sm-6">
                                    <input type="date" class="form-control" id="data" name="data"
                                        value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="row mb-2 mt-4">
                                <label for="programari" class="col-sm-2 col-form-label">Programari: <br>
                                    <small><em>(Tine apasat CTRL pentru selectarea mai multor optiuni)</em></small>
                                </label>

                                <div class="col-sm-10">
                                    <select multiple class="form-control" size="5" required name="programari[]">
                                        <?php
                                        
                                        
                                        $query = "SELECT P.ID_Programare, C.Nume + ' ' + C.Prenume AS NumeClient, 
                                        P.Data, S.Nume AS Serviciu, A.Nume +' '+ A.Prenume AS Angajat, P.ID_Client,
                                        S.Pret + ISNULL((SELECT SUM(Suma) FROM Inventar WHERE ID_Programare = P.ID_Programare), 0) AS PretTotal
                                        FROM Programari P
                                        LEFT JOIN Clienti C ON P.ID_Client = C.ID_Client
                                        LEFT JOIN Servicii S ON P.ID_Serviciu = S.ID_Serviciu
                                        LEFT JOIN Angajati A ON P.ID_Angajat = A.ID_Angajat
                                        LEFT JOIN Inventar I ON P.ID_Programare = I.ID_Programare
                                        WHERE P.ID_Factura IS NULL";
                                        $stmt = sqlsrv_query($conn, $query);
                                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                            echo "<option value='" . $row['ID_Programare']."'";
                                            if($id_programare == $row['ID_Programare']) {
                                                echo  "selected";}
                                            echo ">" .  $row['NumeClient'] . " - " . $row['Data']->format('d-m-Y H:i') . " - " . $row['Serviciu'] . " - ";
                                                echo $row['PretTotal'] . " RON - " . $row['Angajat'] . "</option>";
                                        } ?>
                                    </select>
                                </div>

                            </div>
                            <div class="row mb-2 mt-4">

                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="pret" class="col-sm-2 col-form-label">Pret Total:</label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" id="pret" name="pret" step="0.01" min="0"
                                        required>
                                </div>
                            </div>


                            <div class="row mb-2 mt-4">
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-success">Adaugă</button>
                                </div>
                                <div class="col-sm-1">
                                    <a href="/bd/dashboard/facturi.php" class="btn btn-secondary">Înapoi</a>
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
                                <div class="toast-body flex-grow-1"><?php echo $message; ?></div>
                                <button type="button" class="btn-close p-3" aria-label="Close"
                                    onclick="hideToast()"></button>
                            </div>
                        </div>
                    </div>


                    <script>
                        $(document).ready(function () {
                            // Transformăm select-ul în Selectize
                            $('#id_client').selectize({
                                placeholder: 'Tastează pentru a căuta...',
                                sortField: 'text'
                            });
                        });

                        document.getElementById('programari').addEventListener('change', function () {
                            let total = 0;
                            const selectedOptions = Array.from(this.selectedOptions);
                            selectedOptions.forEach(option => {
                                total += parseFloat(option.getAttribute('pret'));
                            });
                            document.getElementById('pret').value = total.toFixed(2);
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