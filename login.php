<?php
session_start(); // Start sesiune la începutul scriptului

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$message = null; // Inițializează mesajul
$class = null;   // Inițializează clasa CSS pentru mesaj

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $params = array($username);
    $stmt = sqlsrv_query($conn, "SELECT password, ID_Angajat FROM login WHERE username = ?", $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $db_password = $row['password'];
        $id = $row['ID_Angajat'];

        if ($password===$db_password) { // Verifică parola criptată
            $_SESSION['user'] = $username;
            $_SESSION['ID'] = $id;
            header("Location: dashboard.php"); // Redirecționează la dashboard
            exit();
        } else {
            $message = "Parola este greșită."; // Setează mesajul pentru afișare
            $class = "bg-danger";
        }
    } else {
        $message = "Utilizatorul nu a fost găsit."; // Setează mesajul pentru afișare
        $class = "bg-warning";
    }

    sqlsrv_free_stmt($stmt); // Eliberare resurse
}

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Navigare -->
    <nav>
        <ul>
            <li><a class="navlink" href="/bd/index.html">Acasă</a></li>
            <li><a class="navlink" href="/bd/servicii.html">Servicii</a></li>
            <li><a class="navlink" href="/bd/despre.html">Despre Noi</a></li>
            <li><a class="navlink" href="/bd/contact.html">Contact</a></li>
        </ul>
    </nav>

    <!-- Header -->
    <header>
        <h1>Tinker Bell</h1>
        <p>Salon de cosmetică</p>
    </header>

    <!-- Formular Login -->
    <div class="body-login">
        <div class="login-container">
            <h2>Login</h2>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <button type="submit" class="login-button">Login</button>
            </form>

            <!-- Afișare mesaje de eroare -->
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

    <script src="/bd/login.js"></script>
</body>

</html>
