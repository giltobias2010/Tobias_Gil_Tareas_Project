

<form action="index.php" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" maxlength="80" required>

    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" maxlength="80" required>

    <label for="dni">DNI:</label>
    <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" required>
    
    <label for="email">Correo electrónico:</label>
    <input type="email" id="email" name="email" maxlength="254" required>

    <button type="submit">Registrar estudiante</button>
</form>

<?php

require_once __DIR__ . '/conexion.php';

$errores = [];
$mensaje = '';
$nombre = '';
$apellido = '';
$dni = '';
$email = '';

// Recibir y validar los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validar nombre
    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    // Validar apellido
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }

    // Validar DNI
    if ($dni === '' || !preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }

    // Validar correo
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }

    // Insertar en la base de datos si no hay errores
    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO estudiantes
                    (nombre, apellido, dni, email)
                    VALUES (:nombre, :apellido, :dni, :email)';

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':dni' => $dni,
                ':email' => $email
            ]);

            $mensaje = 'Estudiante registrado correctamente.';

            // Limpiar los campos después de guardar
            $nombre = '';
            $apellido = '';
            $dni = '';
            $email = '';

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $errores[] = 'Ese DNI ya está registrado.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}

// Función para proteger las salidas HTML
function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

?>

<?php if ($mensaje !== ''): ?>
<p><?= escapar($mensaje) ?></p>
<?php endif; ?>
<?php if (!empty($errores)): ?>
<ul>
<?php foreach ($errores as $error): ?>
<li><?= escapar($error) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>