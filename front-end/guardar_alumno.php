<?php

$apiUrl = 'http://localhost:9090/api/alumnos';

function alerta($titulo, $txt, $icon) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>body{font-family:sans-serif;background:#F3F4F6;}</style></head><body>
    <script>
    Swal.fire({
        title: '$titulo',
        text: '$txt',
        icon: '$icon',
        confirmButtonColor: '#10B981', 
        background: '#ffffff',
        color: '#374151'
    }).then(()=>{window.location='index.php'});
    </script></body></html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $datos = [
        'nombre' => $_POST['nombre'],
        'apPaterno' => $_POST['ap_paterno'], 
        'apMaterno' => $_POST['ap_materno'],
        'idGrupo' => (int)$_POST['id_grupo'],
        'estatus' => 'activo'
    ];

    

    if (!empty($_POST['id_alumno'])) {
        $datos['idAlumno'] = (int)$_POST['id_alumno'];
    }

    

    $opciones = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($datos),
            'ignore_errors' => true
        ]
    ];



    $contexto = stream_context_create($opciones);
    $resultado = file_get_contents($apiUrl, false, $contexto);

    
    
    if ($resultado === FALSE) {
        alerta("Error de Conexión", "No se pudo conectar con la API Java (Puerto 9090).", "error");
    } else {
        alerta("Procesado", "Los datos fueron enviados y guardados en Java correctamente.", "success");
    }
}
?>