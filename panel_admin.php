<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<?php 
session_start(); // Inicia la sesión al principio del archivo

if (($_SESSION['rol_id'] != 3)) {
    header("Location: index.php"); 
    exit();
}
include "includes/header.php"; ?>
<?php


if ($_POST && isset($_POST["boton_registrar"])) { //
    $nombre = htmlspecialchars($_POST["nombre"]);
    $contrasena = htmlspecialchars($_POST["contrasena"]);
    $contrasena2 = htmlspecialchars($_POST["contrasena2"]);
    $email = htmlspecialchars($_POST["email"]);
    $rol_id = htmlspecialchars($_POST["rol_id"]);
    $contrasena_codificada = base64_encode($contrasena);
 
    //Funcion del email de la validacion
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Consulta para verificar si ya existe un usuario con el mismo nombre de usuario
    $consultaUsuario = "SELECT nombre FROM usuarios WHERE nombre = '$nombre'";

    // Consulta para verificar si ya existe un usuario con el mismo correo electrónico
    $consultaCorreo = "SELECT email FROM usuarios WHERE email = '$email'";

    $validacionEmail = validateEmail($email);

    if ($nombre != "" && $contrasena != "" && $contrasena2 != "" && $email != "" ) {
        // Ejecuto la consulta para el nombre de usuario
        $resultadoUsuario = mysqli_query($conn, $consultaUsuario);

        // Ejecuto la consulta para el correo electrónico
        $resultadoCorreo = mysqli_query($conn, $consultaCorreo);

        // Verifica si ya existe un usuario con el mismo nombre de usuario
        if (mysqli_num_rows($resultadoUsuario) > 0) {
            echo "<script type='text/javascript'>alert('¡El usuario ya existe!')</script>";
        } elseif (mysqli_num_rows($resultadoCorreo) > 0) {
            echo "<script type='text/javascript'>alert('¡El correo electrónico ya está registrado!')</script>";
        } elseif (!$validacionEmail) {
            echo "<script type='text/javascript'>alert('¡No es correcto el correo!')</script>"; 
        } elseif ($contrasena != $contrasena2) {
            echo "<script type='text/javascript'>alert('¡No son iguales las contraseñas!')</script>";
        } else {
            $query = "INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES ('$nombre', '$email', '$contrasena_codificada', '$rol_id')";
            if (mysqli_query($conn, $query)) {
                echo "<div class='alert alert-success text-center' role='alert'>Usuario registrado exitosamente.</div>";
            } else {
                echo "<p class='text-danger'>Error al registrar usuario: " . mysqli_error($conn) . "</p>";
            }
        }
    } else {
        echo "<script type='text/javascript'>alert('¡Debes de rellenar todos los campos!')</script>";
    } 
}
?>
<script>
    
    function delUser(id,usuario,cont){

        var result = window.confirm('Estás seguro de eliminar el usuario '+usuario+ '?');
        if (result == true) {

            $.ajax({
                url: 'eliminar_usuario.php',
                method: "POST",
            
            data: { id: id},
            success: function(data) {
                alert("usuario Borrado");
                    location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Ajax request failed: " + textStatus, errorThrown);
            }
            });
        
        }
    }
    
</script>
<div class="container mt-4">
    <?php //include "barra_admin.php"; ?>
    <div class="header-container text-white p-4 rounded shadow-sm mb-4" style="background-color: #154c79">
        <h1 class="text-center">Panel de administración</h1>
    </div>
    <div id="contenido" class="d-flex justify-content-around">
        <div id="registrar">
        <div class="container">
                <div class="header-container text-white p-4 rounded shadow-sm mb-1" style="background-color: #154c79">
                    <h1 class="text-center">Registrar</h1>
                </div>
                <form method="POST" class="mt-4">
                    <div class="form-group">
                        <label for="usuario">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ingresa tu usuario" >
                    </div>
                    <div class="form-group mt-4">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Ingresa tu contraseña" >
                    </div>
                    <div class="form-group mt-4">
                        <label for="contrasena">Repetir contraseña</label>
                        <input type="password" class="form-control" name="contrasena2" id="contrasena2" placeholder="Repite tu contraseña" >
                    </div>
                    <div class="form-group mt-4">
                        <label for="usuario">Correo</label>
                        <input type="text" class="form-control" name="email" id="email" placeholder="Ingresa tu correo" >
                    </div>
                    <div class="form-group mt-4">
                        <label for="rol_id" class="form-label">Rol</label>
                        <select name="rol_id" id="rol_id" class="form-control">
                            <option value="1">Cliente</option>
                            <option value="2">Vendedor</option>
                            <option value="3">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" name="boton_registrar" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="usuarios_existentes">
            <div class="container">
                <div class="header-container text-white p-4 rounded shadow-sm mb-1" style="background-color: #154c79">
                    <h1 class="text-center">Usuarios</h1>
                </div>
                <div class="table-responsive rounded">
                    <table id='userTable' class="table table-bordered rounded table-hover custom-table">
                        <thead class="text-white text-center" style="background-color: #154c79">
                            <tr>
                                <th scope="col"><a href="?ordenar=id">ID</a></th> 
                                <th scope="col"><a href="?ordenar=nombre">Usuario</a></th>
                                <th scope="col">Rol</th>
                                <th scope="col"><a href="?ordenar=email">Correo</a></th>
                                <th scope="col" colspan="2" class="text-center">Operaciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            /////////////////////////

                            $orden = 'id'; // Orden por defecto
                            $columnasOrdenables = array('id', 'nombre', 'email');

                            if (isset($_GET['ordenar']) && in_array($_GET['ordenar'], $columnasOrdenables)) {
                                $orden = $_GET['ordenar'];
                            }

                            ///////////////////////////
                            $miUsuario = $_SESSION['nombre'];
                            $query = "SELECT * FROM usuarios WHERE nombre != '$miUsuario' ORDER BY $orden ASC";
                            // Ejecutar la consulta
          
                            $result = mysqli_query($conn, $query);
                            $cont = 0;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['id'];
                                $nombre = $row['nombre'];
                                $rol_id = $row['rol_id'];
                                $email = $row['email'];

                                echo "<tr id='userRow{$id}'>";
                                echo "<td>{$id}</td>";
                                echo "<td>{$nombre}</td>";
                                echo "<td>{$rol_id}</td>";  
                                echo "<td>{$email}</td>";  
                                echo "<td class='text-center'><a onclick=\"delUser('{$id}','{$usuario}','{$cont}')\" class='btn btn-danger'><i class='bi bi-trash'></i> Eliminar</a></td>";
                                echo "</tr>";
                                $cont++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>  
    </div>
    
</div>


<div class="container text-center mt-5">
    <a href="index.php" class="btn btn-warning mb-5">Volver</a>
</div>



<?php include "includes/footer.php"; ?>