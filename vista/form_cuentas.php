<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../recursos/css/form_cuenta.css">
    <title>Crear Cuentas</title>
</head>
<body>
    <div class="background-video">

    </div>
    <div class="container">
        <aside class="sidebar">
            <div class="header">
                <h1>Environmental Aid</h1>
            </div>
            <div class="profile">
                <?php
                require_once('../controlador/conexionBD.php');
                session_start();

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

                <img src="../recursos/img/<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic" id="profilePic">
                <h2 id="nickname"><?php echo htmlspecialchars($user['nick_name']); ?></h2>
            </div>
            <nav>
                <ul>
                    <li><a href="crear_cuentas.html">Crear Cuentas</a></li>
                    <li><a href="ver_usuarios.html">Ver Usuarios</a></li>
                    <li><a href="administrador.php">Volver Atras</a></li>


                </ul>
            </nav>
        </aside>

        <main class="content">
            <h1>Crear Nuevas Cuentas</h1>
            <form action="../controlador/crear_cuenta.php" method="POST" enctype="multipart/form-data" class="register-form">
                <div class="form-group">
                    <label for="nick">Nick Name:</label>
                    <input type="text" id="nick" name="nick" placeholder="Nick Name" required>
                </div>
                <div class="form-group">
                    <label for="documento">Documento:</label>
                    <input type="text" id="documento" name="documento" placeholder="Documento" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" placeholder="Correo" required>
                </div>
                <div class="form-group">
                    <label for="passwords">Contraseña:</label>
                    <input type="password" id="passwords" name="passwords" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" required>
                        <option value="">Selecciona un rol</option>
                        <option value="administrador">Administrador</option>
                        <option value="aprendiz">Aprendiz</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="foto">Foto de Perfil:</label>
                    <input type="file" id="foto" name="foto">
                </div>
                <button type="submit" name="btn_enviar">Registrar Cuenta</button>

            </form>
        </main>
    </div>
</body>
</html>
