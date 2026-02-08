<?php
$db_file = 'C:/Users/LuaN/Desktop/Registro_alumnos/database.bd';

function alerta($titulo, $txt, $icon) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>body{font-family:sans-serif;background:#F3F4F6;}</style></head><body>
    <script>
    Swal.fire({
        title: '$titulo',
        text: '$txt',
        icon: '$icon',
        confirmButtonColor: '#1F2937', 
        background: '#ffffff'
    }).then(()=>{window.location='index.php'});
    </script></body></html>";
    exit();
}

// LOGICA ELIMINAR/ACTIVAR (GET)
if (isset($_GET['accion'])) {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $id = $_GET['id']; $tipo = $_GET['tipo']; $accion = $_GET['accion'];

        if ($tipo == 'carrera' && $accion == 'eliminar') {
            $pdo->prepare("UPDATE carreras SET estatus = 'inactivo' WHERE id_carrera = ?")->execute([$id]);
            alerta("Eliminado", "La carrera ha sido eliminada.", "success");
        }
    } catch (Exception $e) { alerta("Error", $e->getMessage(), "error"); }
}

// LOGICA REGISTRAR (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $tipo = $_POST['tipo'];

        if ($tipo == 'carrera') {
            $pdo->prepare("INSERT INTO carreras (nombre, siglas, estatus) VALUES (?, ?, 'activo')")
                ->execute([$_POST['nombre'], $_POST['siglas']]);
            alerta("Carrera Agregada", "Se ha registrado correctamente.", "success");
        
        } elseif ($tipo == 'grado') {
            $pdo->prepare("INSERT INTO cat_grados (numero) VALUES (?)")
                ->execute([$_POST['numero_grado']]);
            alerta("Grado Agregado", "Nuevo grado escolar registrado.", "success");

        } elseif ($tipo == 'turno') {
            $pdo->prepare("INSERT INTO cat_turnos (nombre) VALUES (?)")
                ->execute([$_POST['nombre_turno']]);
            alerta("Turno Agregado", "Nuevo turno registrado.", "success");
        }

    } catch (Exception $e) { alerta("Error", $e->getMessage(), "error"); }
}
?>