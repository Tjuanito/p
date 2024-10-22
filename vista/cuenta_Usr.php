<?php
require_once('../controlador/conexionBD.php');
session_start();

error_reporting(0); // Desactiva todos los reportes de errores

// Verificar si el usuario está autenticado
if (!isset($_SESSION['documento'])) {
    header('Location: ../vista/inicio_sesion.html');
    exit;
}

// Obtener el identificador del usuario desde la sesión
$documento_usr = $_SESSION['documento'];

// Consultar la información del usuario
$query = "SELECT * FROM usuarios WHERE documento=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $documento_usr);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Usuario no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../recursos/css/style_Cuenta.css">
    <style>
        .toggle-password {
            cursor: pointer;
            color: rgb(255, 255, 255); /* Color blanco */
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5em; /* Aumentar el tamaño del ícono */
        }
        .input-container {
            position: relative;
        }
        .input-container input {
            width: 100%;
            padding-right: 40px; /* Espacio para el ícono */
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <form action="../controlador/perfil.php" method="POST" enctype="multipart/form-data" class="caja">
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src="../recursos/img/<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic" id="profilePic">
                    <div class="upload-overlay">
                        <label for="file-upload" class="upload-label">
                            <i class="fa fa-camera" aria-hidden="true"></i>
                        </label>
                        <input type="file" id="file-upload" name="foto" class="file-upload" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="profile-info">
                <div id="grupo_nick" class="formulario_grupo">
                    <h1 id="nickname"><?php echo htmlspecialchars($user['nick_name']); ?></h1>
                </div>
                <div class="info-group">
                    <label for="document">Documento:</label>
                    <input type="text" id="document" name="documento" value="<?php echo htmlspecialchars($user['documento']); ?>" required>
                </div>
                <div id="grupo_correo" class="info-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($user['correo']); ?>" required>
                </div>
                <div id="grupo_password" class="info-group">
                    <label for="password">Contraseña:</label>
                    <div class="input-container">
                        <input type="password" id="password" name="passwords" placeholder="Contraseña" required oninput="toggleEyeIcon()">
                        <i class="bx bx-show toggle-password" id="eyeIcon" onclick="togglePassword()" style="display:none;"></i>
                    </div>
                </div>
            </div>
            <a href="actividades.php" class="btn-menu" name="btn_enviar1">Actividades</a>
            <button class="edit-btn" name="btn_enviar" type="submit">Guardar Cambios</button>
        </form>
    </div>
    
    <script src="../JS/script_C.js"></script>
    <script src="https://kit.fontawesome.com/7f1c533a7e.js" crossorigin="anonymous"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const passwordType = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', passwordType);
            eyeIcon.classList.toggle('bx-show');
            eyeIcon.classList.toggle('bx-hide');
        }

        function toggleEyeIcon() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            eyeIcon.style.display = passwordInput.value ? 'block' : 'none';
        }
    </script>
</body>
</html>
