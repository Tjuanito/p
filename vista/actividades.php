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
    <link rel="stylesheet" href="../recursos/css/style_act.css">
    <title>Página de Juegos</title>
</head>
<body>
    <div class="background-video">
        <video autoplay muted loop id="bg-video">
            <source src="../recursos/video/colombia.mp4" type="video/mp4">
            Tu navegador no soporta el formato de video.
        </video>
    </div>
    <div class="container">
        <aside class="sidebar">
            <div class="header">
                <h1>Environmental Aid</h1>
            </div>
            <div class="profile">
                <img src="../recursos/img/<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic" id="profilePic">
                <h2 id="nickname"><?php echo htmlspecialchars($user['nick_name']); ?></h2>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.html">Inicio</a></li>
                    <li><a href="cuenta_Usr.php">Datos</a></li>
                    <li><a href="logros.php" id="ver-logros">Ver Logros</a></li>
                    <form action="../controlador/eliminar_cuenta_A.php" method="POST" class="caja" id="formulario">
                    <li><a href="#" onclick="confirmDelete()">Eliminar Cuenta</a></li>
                    </form>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <section id="juegos">
                <h2>Actividades Lúdicas</h2>
                <div class="game-card">
                    <img src="../recursos/img/3.jpeg" alt="Juego 1">
                    <div class="game-details">
                        <h3>pa saber</h3>
                        <p>Responde la pregunta, según la letra correspondiente.</p>
                        <a href="../recursos/game1/nivel1.html"><button>Jugar</button></a>  
                    </div>
                </div>
                <div class="game-card">     
                    <img src="../recursos/icons/icono.png" alt="Juego 1">
                    <div class="game-details">
                        <h3>Restoration Journey</h3>
                        <p>Descripción:aqui aprenderas sobre la recoleccion de basuras, plantaciones de a   rboles y especies invasoras.</p>
                        <a href="../recursos/game3/activad.html"><button>Jugar</button></a> 
                    </div>
                </div>
                <!-- Puedes agregar más juegos aquí -->
            </section>
        </main>
    </div>

    <script>
        function confirmDelete() {
            if (confirm("¿Estás seguro de que quieres eliminar tu cuenta?")) {
                window.location.href = "../controlador/eliminar_cuenta_A.php"; // Redirige a la página de eliminación
            }
        }
    </script>
</body>
</html>
