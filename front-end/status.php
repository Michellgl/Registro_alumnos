<?php
if (isset($_GET['id']) && isset($_GET['accion'])) {
    
    $id = $_GET['id'];
    $nuevoStatus = $_GET['accion']; 

    


    $apiUrl = "http://localhost:9090/api/alumnos/$id/estatus";

    

    $opciones = [
        'http' => [
            'header'  => "Content-type: text/plain\r\n", 

            'method'  => 'PUT',
            'content' => $nuevoStatus,
            'ignore_errors' => true
        ]
    ];

    $contexto = stream_context_create($opciones);
    $resultado = file_get_contents($apiUrl, false, $contexto);

    
    header("Location: index.php");
    exit();
}
?>