<?php
// index.php
require_once 'db_config.php';
session_start();

$msg = "";

// Lógica de Login
if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
    $stmt->execute([$_POST['user'], $_POST['pass']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['usuario'] = $user['usuario'];
    } else {
        $msg = "<p style='color:red;'>Acceso denegado. Verifique sus credenciales.</p>";
    }
}

// Cerrar Sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Lógica de Registro de Cita
if (isset($_POST['agendar'])) {
    $sql = "INSERT INTO citas (paciente_nombre, especialidad, fecha, hora) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['paciente'], $_POST['especialidad'], $_POST['fecha'], $_POST['hora']]);
    $msg = "<p style='color:green;'>Cita registrada correctamente en SQLite.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Médico Prototipo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <?php if (!isset($_SESSION['usuario'])): ?>
        <div class="login-form">
            <h2>Acceso al Sistema Médico</h2>
            <?= $msg ?>
            <form method="POST">
                <input type="text" name="user" placeholder="Usuario (admin)" required>
                <input type="password" name="pass" placeholder="Contraseña (1234)" required>
                <button type="submit" name="login">Ingresar</button>
            </form>
        </div>

    <?php else: ?>
        <div class="header">
            <span>Bienvenido: <strong><?= $_SESSION['usuario'] ?></strong></span>
            <a href="?logout=1" class="logout">Cerrar Sesión</a>
        </div>

        <div class="appointment-form">
            <h2>Agendar Cita Médica</h2>
            <?= $msg ?>
            <form method="POST">
                <input type="text" name="paciente" placeholder="Nombre completo del paciente" required>
                <select name="especialidad">
                    <option value="Medicina General">Medicina General</option>
                    <option value="Pediatría">Pediatría</option>
                    <option value="Odontología">Odontología</option>
                </select>
                <div style="display:flex; gap:10px;">
                    <input type="date" name="fecha" required>
                    <input type="time" name="hora" required>
                </div>
                <button type="submit" name="agendar">Guardar en Base de Datos Local</button>
            </form>
        </div>

        <h2>Reporte de Citas (Persistencia Local)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM citas ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['paciente_nombre']) ?></td>
                        <td><?= $row['especialidad'] ?></td>
                        <td><?= $row['fecha'] ?> | <?= $row['hora'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>