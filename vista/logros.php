<?php
require_once('../controlador/conexionBD.php');
session_start();

error_reporting(0); // Desactiva todos los reportes de errores

// Verificar si el usuario está autenticado
if (!isset($_SESSION['documento'])) {
    header('Location: administrador.php');
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

$query_users = "SELECT * FROM usuarios";
$result_users = $conn->query($query_users);
$users = $result_users->fetch_all(MYSQLI_ASSOC);

// Manejo de edición y actualización
if (isset($_POST['btn_enviar'])) {
    $nuevo_documento = trim($_POST['documento']);
    $correo_usr = trim($_POST['correo']);
    $clave_ingresada = trim($_POST['passwords']);
    
    // Consultar la contraseña actual en la base de datos
    $query = "SELECT contrasena FROM usuarios WHERE documento=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $documento_usr);
    $stmt->execute();
    $stmt->bind_result($contrasena_db);
    $stmt->fetch();
    $stmt->close();

    // Verificar la contraseña
    if (password_verify($clave_ingresada, $contrasena_db)) {
        $cambios_realizados = false;

        // Comprobar cambios
        if ($nuevo_documento !== $user['documento'] || $correo_usr !== $user['correo']) {
            $cambios_realizados = true;
        }

        // Procesar la imagen
        $foto_usr = $_FILES['foto']['name'];
        if ($foto_usr) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_destino = "../recursos/img/" . $foto_usr;
            move_uploaded_file($foto_tmp, $foto_destino);
            $cambios_realizados = true;
        } else {
            $foto_usr = $user['foto_perfil']; // Mantener la foto actual si no se sube una nueva
        }

        // Actualizar la base de datos si hay cambios
        if ($cambios_realizados) {
            $update_query = "UPDATE usuarios SET documento=?, correo=?, foto_perfil=? WHERE documento=?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssss", $nuevo_documento, $correo_usr, $foto_usr, $documento_usr);
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
                // Actualizar la sesión con el nuevo documento
                $_SESSION['documento'] = $nuevo_documento;
            } else {
                $_SESSION['mensaje'] = "Error al actualizar el perfil: " . $stmt->error;
            }
            $stmt->close();
            // Redirigir a la misma página para mostrar los cambios
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['mensaje'] = "No se han realizado cambios.";
        }
    } else {
        $_SESSION['mensaje'] = "Clave incorrecta. No se puede modificar el perfil.";
    }
}




// Manejo de informacion tarjetas
if (isset($_POST['btn_guardar'])) {
    $documento = trim($_POST['documento']);
    $correo = trim($_POST['correo']);
    $user_id = $_POST['user_id'];

    $update_query = "UPDATE usuarios SET documento=?, correo=? WHERE id_user=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $documento, $correo, $user_id);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el usuario: " . $stmt->error;
    }
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}



// Manejo de activación/desactivación de usuario
if (isset($_POST['toggle_user_status'])) {
    $userIdToToggle = $_POST['user_id'];
    $currentStatus = $_POST['current_status'];

    // Cambiar el estado: Si está activado, lo inactiva; si está inactivo, lo activa
    $newStatus = $currentStatus == 'activado' ? 'inactivo' : 'activado';

    // Actualizar el estado en la base de datos
    $update_query = "UPDATE usuarios SET estado=? WHERE id_user=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $newStatus, $userIdToToggle);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario " . ($newStatus === 'activado' ? "activado" : "inactivado") . " correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al cambiar el estado del usuario: " . $stmt->error;
    }
    $stmt->close();
    
    // Redirigir para evitar reenvío de formulario al actualizar la página
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}







// Manejo de eliminación de usuario
if (isset($_POST['delete_user'])) {
    $userIdToDelete = $_POST['user_id'];
    $delete_query = "DELETE FROM usuarios WHERE id_user=?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $userIdToDelete);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['mensaje'])) {
    echo "<script>alert('" . $_SESSION['mensaje'] . "');</script>";
    unset($_SESSION['mensaje']);
}


//conexion con las tablas 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['documento'])) {
    header('Location: administrador.php');
    exit;
}

// Obtener el identificador del usuario desde la sesión
$documento_usr = $_SESSION['documento'];

// Consulta para obtener datos de la tabla `actividad` relacionados con el usuario
$query = "SELECT * FROM actividad WHERE id_user = (SELECT id_user FROM usuarios WHERE documento = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $documento_usr);
$stmt->execute();
$result = $stmt->get_result();
$actividad = $result->fetch_assoc();

if (!$actividad) {
    die("Datos de actividad no encontrados.");
}

// Consulta para obtener todos los datos de la tabla `ludica` relacionados con el usuario
$query_ludica = "SELECT * FROM ludica WHERE id_user = (SELECT id_user FROM usuarios WHERE documento = ?)";
$stmt = $conn->prepare($query_ludica);
$stmt->bind_param("s", $documento_usr);
$stmt->execute();
$result_ludica = $stmt->get_result();
$ludica = $result_ludica->fetch_assoc();

if (!$ludica) {
    die("Datos de ludica no encontrados.");
}

// Manejo de edición y actualización de datos de `actividad`
if (isset($_POST['btn_enviar'])) {
    $nuevo_codigo_actividad = trim($_POST['codigo_actividad']);
    $nivel = trim($_POST['nivel']);
    $puntuacion = trim($_POST['puntuacion']);
    
    // Actualizar la base de datos si hay cambios en la tabla `actividad`
    $update_query = "UPDATE actividad SET codigo_actividad = ?, nivel = ?, puntuacion = ? WHERE id_user = (SELECT id_user FROM usuarios WHERE documento = ?)";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("siis", $nuevo_codigo_actividad, $nivel, $puntuacion, $documento_usr);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Datos de actividad actualizados correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar los datos de actividad: " . $stmt->error;
    }
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Manejo de edición y actualización de datos de `ludica`
if (isset($_POST['btn_guardar'])) {
    $especiesC = trim($_POST['especies_transladadasC']);
    $especiesI = trim($_POST['especies_transladadasI']);
    $puntaje = trim($_POST['puntaje']);
    
    // Actualizar la base de datos si hay cambios en la tabla `ludica`
    $update_query = "UPDATE ludica SET especies_transladadasC = ?, especies_transladadasI = ?, puntaje = ? WHERE id_user = (SELECT id_user FROM usuarios WHERE documento = ?)";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iiis", $especiesC, $especiesI, $puntaje, $documento_usr);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Datos de ludica actualizados correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar los datos de ludica: " . $stmt->error;
    }
    $stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración del Sistema</title>
    <link rel="stylesheet" href="../recursos/css/style_logro.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="background"></div>
<div class="container">
    <div class="sidebar">
        <div class="profile-info">
            <br>
            <div class="profile-pic-container">
                <div class="profile-header">
                    <img src="../recursos/img/<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic" id="profilePic">
                </div>
                <br>
            </div>
            <div class="inputs">
                <form action="" method="POST" enctype="multipart/form-data">
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
                        <input type="password" id="password" name="passwords" placeholder="Contraseña" required>
                    </div>
                    <input type="file" id="file-upload" name="foto" class="file-upload" accept="image/*">
                    <button class="edit-btn" name="btn_enviar" type="submit">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="tabs">
            <ul class="tab" onclick="toggleTab('tab1')">Ver Usuarios</ul>
            <ul class="tab" onclick="toggleTab('tab2')">Crear Usuarios</ul>
        </div>

        <div id="tab1" class="tab-content active">
            <div id="user-cards">
                <?php foreach ($users as $user): ?>
                    <div class="user-card" id="user-card-<?php echo htmlspecialchars($user['id_user']); ?>" data-user-id="<?php echo htmlspecialchars($user['id_user']); ?>">
                        <div class="card-header">
                            <img src="../recursos/img/<?php echo htmlspecialchars($user['foto_perfil']); ?>" alt="Foto de Perfil" class="profile-pic">
                            <div class="nickname-container">
                                <h1 class="nickname"><?php echo htmlspecialchars($user['nick_name']); ?></h1>
                                <p class="user-id"><?php echo htmlspecialchars($user['id_user']); ?></p>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="info-group">
                                    <label for="document">Documento:</label>
                                    <input type="text" name="documento" value="<?php echo htmlspecialchars($user['documento']); ?>" required>
                                </div>
                                <div id="grupo_correo" class="info-group">
                                    <label for="correo">Correo:</label>
                                    <input type="email" name="correo" value="<?php echo htmlspecialchars($user['correo']); ?>" required>
                                </div>
                                <p><strong>Rol:</strong> <span><?php echo htmlspecialchars($user['rol']); ?></span></p>
                                <p><strong>Estado:</strong> <span><?php echo htmlspecialchars($user['estado']); ?></span></p>


                                <div class="user-actions">

</div>



                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form action="actividades.php" method="POST">

        <div id="tab2" class="tab-content">

</form>


<script src="../JS/script_admi.js"></script>
<script src="https://kit.fontawesome.com/7f1c533a7e.js" crossorigin="anonymous"></script>
<script>
    function toggleTab(tabId) {
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
    }

    function toggleUserStatus(userId, isChecked) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = userId;

    const currentStatusInput = document.createElement('input');
    currentStatusInput.type = 'hidden';
    currentStatusInput.name = 'current_status';
    // Si el interruptor está marcado, envía 'activado'; si no, 'inactivo'
    currentStatusInput.value = isChecked ? 'activado' : 'inactivo'; 

    const toggleStatusInput = document.createElement('input');
    toggleStatusInput.type = 'hidden';
    toggleStatusInput.name = 'toggle_user_status';
    toggleStatusInput.value = '1';

    form.appendChild(userIdInput);
    form.appendChild(currentStatusInput);
    form.appendChild(toggleStatusInput);
    document.body.appendChild(form);
    form.submit(); // Envía el formulario para actualizar el estado
}



</script>
</body>
</html>
