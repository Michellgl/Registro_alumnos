<?php
// ==========================================
// 1. GESTIÓN LOCAL DE LA BD (PHP CREA/INSERTA)
// ==========================================
$db_file = 'C:/Users/LuaN/Desktop/Registro_alumnos/database.bd';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tablas
    $pdo->exec("CREATE TABLE IF NOT EXISTS carreras (id_carrera INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL, siglas TEXT NOT NULL, estatus TEXT DEFAULT 'activo')");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_grados (id_grado INTEGER PRIMARY KEY AUTOINCREMENT, numero INTEGER NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_turnos (id_turno INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos (id_grupo INTEGER PRIMARY KEY AUTOINCREMENT, id_carrera INTEGER, id_grado INTEGER, id_turno INTEGER, codigo_grupo TEXT, FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera), FOREIGN KEY (id_grado) REFERENCES cat_grados(id_grado), FOREIGN KEY (id_turno) REFERENCES cat_turnos(id_turno))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS alumnos (id_alumno INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, ap_paterno TEXT, ap_materno TEXT, id_grupo INTEGER, estatus TEXT DEFAULT 'pendiente', FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo))");

    // Datos iniciales
    $chk = $pdo->query("SELECT COUNT(*) FROM carreras");
    if ($chk->fetchColumn() == 0) {
        $pdo->beginTransaction();
        $carreras = [
            ['Administración de Empresas', 'LAE'], ['Administración de Empresas Turísticas', 'LAET'],
            ['Relaciones Internacionales', 'LRI'], ['Contaduría Pública y Finanzas', 'LCPF'],
            ['Derecho', 'DER'], ['Mercadotecnia y Publicidad', 'MYP'],
            ['Gastronomía', 'GAS'], ['Periodismo y Ciencias de la Comunicación', 'PCC'],
            ['Diseño de Modas', 'LDM'], ['Pedagogía', 'PED'],
            ['Cultura Física y Educación del Deporte', 'CFED'], ['Idiomas (Inglés y Francés)', 'IDI'],
            ['Psicología', 'PSI'], ['Diseño de Interiores', 'LDI'],
            ['Diseño Gráfico', 'LDG'], ['Ingeniería en Logística y Transporte', 'ILT'],
            ['Ingeniero Arquitecto', 'ARQ'], ['Informática Administrativa y Fiscal', 'IAF'],
            ['Ingeniería en Sistemas Computacionales', 'ISC'], ['Ingeniería Mecánica Automotriz', 'IMA']
        ];
        $stmt_c = $pdo->prepare("INSERT INTO carreras (nombre, siglas) VALUES (?, ?)");
        foreach ($carreras as $c) $stmt_c->execute($c);

        $stmt_g = $pdo->prepare("INSERT INTO cat_grados (numero) VALUES (?)");
        for ($i = 1; $i <= 11; $i++) $stmt_g->execute([$i]);

        $turnos = ['Matutino', 'Vespertino', 'Mixto'];
        $stmt_t = $pdo->prepare("INSERT INTO cat_turnos (nombre) VALUES (?)");
        foreach ($turnos as $t) $stmt_t->execute([$t]);
        $pdo->commit();
    }
} catch (PDOException $e) { die("Error Crítico DB: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Escolar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --secondary: #1F2937; --bg-body: #F3F4F6; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: #374151; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--secondary); height: 100vh; position: fixed; padding: 20px; color: white; z-index: 1000; transition: 0.3s; }
        .brand-logo { font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .menu-item { display: flex; align-items: center; padding: 12px 15px; color: #D1D5DB; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.2s; font-weight: 500; }
        .menu-item:hover { background-color: rgba(16, 185, 129, 0.1); color: var(--primary); }
        .menu-item i { font-size: 1.2rem; margin-right: 12px; }

        /* Contenido */
        .main-content { margin-left: 260px; padding: 30px; }
        
        /* Tarjetas */
        .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid var(--primary); transition: transform 0.2s; height: 100%; }
        .stat-card:hover { transform: translateY(-3px); }
        .grupo-badge { background-color: #ECFDF5; color: var(--primary-dark); padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }

        /* Botones */
        .btn-verde { background-color: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
        .btn-verde:hover { background-color: var(--primary-dark); color: white; }

        /* Estilos TABLA */
        .table-custom th { background-color: #F9FAFB; color: #6B7280; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #E5E7EB; }
        .table-custom td { vertical-align: middle; padding: 12px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .card-table { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }

        /* IMPORTANTE: Colores forzados */
        tr.row-activo, tr.row-activo > td { background-color: #d1fae5 !important; color: #065f46; }
        tr.row-baja, tr.row-baja > td { background-color: #fee2e2 !important; color: #991b1b; }
        tr.row-pendiente, tr.row-pendiente > td { background-color: #ffffff !important; }

        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="brand-logo"><i class="ri-code-box-line"></i> ESCOLAR</div>
    <a href="index.php" class="menu-item"><i class="ri-dashboard-line"></i> Dashboard</a>
    <div class="mt-4 mb-2 px-3 text-uppercase small text-muted">Gestión</div>
    <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#modalGrupo"><i class="ri-add-circle-line"></i> Nuevo Grupo</a>
    <a href="#" class="menu-item" onclick="abrirModalRegistro()"><i class="ri-user-add-line"></i> Inscribir Alumno</a>
    <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#modalConfig"><i class="ri-settings-4-line"></i> Configuración</a>
</nav>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div><h2 class="fw-bold m-0">Panel de Control</h2><p class="text-muted m-0">Resumen de grupos y alumnos.</p></div>
        <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm"><i class="ri-refresh-line"></i></button>
    </header>

    <h5 class="fw-bold text-muted mb-3"><i class="ri-layout-grid-line me-2"></i>Grupos Activos</h5>
    <div class="row g-4 mb-5">
        <?php
        $sql = "SELECT g.codigo_grupo, c.nombre, c.siglas, gr.numero as grado, t.nombre as turno, (SELECT COUNT(*) FROM alumnos WHERE id_grupo = g.id_grupo) as total FROM grupos g JOIN carreras c ON g.id_carrera = c.id_carrera JOIN cat_grados gr ON g.id_grado = gr.id_grado JOIN cat_turnos t ON g.id_turno = t.id_turno ORDER BY g.id_grupo DESC";
        $stmt = $pdo->query($sql);
        $hay = false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $hay = true;
            echo '<div class="col-md-6 col-lg-4 col-xl-3"><div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="grupo-badge"><i class="ri-hashtag"></i> '.$row['codigo_grupo'].'</div>
                    <i class="ri-book-open-line text-muted" style="font-size: 1.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark">'.$row['siglas'].'</h5>
                <p class="text-muted small text-truncate">'.$row['nombre'].'</p>
                <div class="d-flex justify-content-between align-items-end mt-4">
                    <div><span class="d-block text-muted small">TURNO '.strtoupper($row['turno']).'</span><span class="fw-bold">'.$row['grado'].'° Cuatrimestre</span></div>
                    <div class="text-end"><h3 class="m-0 fw-bold" style="color: var(--primary);">'.$row['total'].'</h3><small class="text-muted">ALUMNOS</small></div>
                </div>
            </div></div>';
        }
        if (!$hay) echo '<div class="col-12 text-center py-4 bg-white rounded shadow-sm text-muted">No hay grupos registrados.</div>';
        ?>
    </div>

    <h5 class="fw-bold text-muted mb-3"><i class="ri-table-line me-2"></i>Matrícula de Alumnos (API Java)</h5>
    <div class="card-table">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre Completo</th>
                        <th>Grupo ID</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CONEXIÓN A TU SPRING BOOT (PUERTO 9090)
                    $apiUrl = "http://localhost:9090/api/alumnos";
                    $json = @file_get_contents($apiUrl);

                    if ($json === FALSE) {
                        echo "<tr><td colspan='5' class='text-center text-danger fw-bold py-3'>
                                <i class='ri-error-warning-line me-2'></i>Error de Conexión: No se detecta la API en el puerto 9090.
                              </td></tr>";
                    } else {
                        $alumnos = json_decode($json, true);
                        if (empty($alumnos)) {
                            echo "<tr><td colspan='5' class='text-center py-3'>No hay alumnos en la base de datos (Verifica que copiaste database.bd a la carpeta API).</td></tr>";
                        } else {
                            foreach ($alumnos as $a) {
                                $claseFila = 'row-pendiente';
                                $textoEstado = 'PENDIENTE';
                                
                                if ($a['estatus'] == 'activo') { $claseFila = 'row-activo'; $textoEstado = 'ACTIVO'; }
                                elseif ($a['estatus'] == 'baja') { $claseFila = 'row-baja'; $textoEstado = 'BAJA'; }

                                echo "<tr class='$claseFila'>
                                <td class='ps-4 fw-bold'>" . str_pad($a['idAlumno'], 4, '0', STR_PAD_LEFT) . "</td>
                                <td class='fw-bold'>{$a['nombre']} {$a['apPaterno']} {$a['apMaterno']}</td>
                                <td><span class='badge bg-white text-dark border'>{$a['idGrupo']}</span></td>
                                <td class='fw-bold small'>$textoEstado</td>
                                <td class='text-end pe-4'>
                                    <div class='btn-group'>
                                        <a href='status.php?id={$a['idAlumno']}&accion=activo' class='btn btn-sm btn-light text-success'><i class='ri-check-line'></i></a>
                                        <a href='status.php?id={$a['idAlumno']}&accion=baja' class='btn btn-sm btn-light text-danger'><i class='ri-close-line'></i></a>
                                        <button onclick='cargarEdicion(".json_encode($a).")' class='btn btn-sm btn-light text-primary'><i class='ri-pencil-line'></i></button>
                                    </div>
                                </td>
                                </tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">
                <h5 class="fw-bold mb-4" id="tituloModalAlumno">Registrar Alumno</h5>
                <form action="guardar_alumno.php" method="POST">
                    <input type="hidden" name="id_alumno" id="input_id_alumno">
                    <div class="mb-3"><label class="form-label small fw-bold">Nombre</label><input type="text" name="nombre" id="input_nombre" class="form-control" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col"><label class="form-label small fw-bold">Apellido P.</label><input type="text" name="ap_paterno" id="input_ap_paterno" class="form-control" required></div>
                        <div class="col"><label class="form-label small fw-bold">Apellido M.</label><input type="text" name="ap_materno" id="input_ap_materno" class="form-control" required></div>
                    </div>
                    <div class="mb-4"><label class="form-label small fw-bold">Grupo</label>
                        <select name="id_grupo" id="input_grupo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $q = $pdo->query("SELECT g.id_grupo, g.codigo_grupo, c.siglas FROM grupos g JOIN carreras c ON g.id_carrera = c.id_carrera");
                            while($g = $q->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$g['id_grupo']}'>{$g['codigo_grupo']} - {$g['siglas']}</option>";
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-verde w-100" id="btnGuardarAlumno">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrupo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">
                <h5 class="fw-bold mb-4">Nuevo Grupo</h5>
                <form action="guardar_grupo.php" method="POST">
                    <div class="mb-3"><label class="form-label small fw-bold">Carrera</label>
                        <select name="id_carrera" class="form-select" required>
                            <?php $c = $pdo->query("SELECT * FROM carreras WHERE estatus='activo'"); while($r=$c->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$r['id_carrera']}'>{$r['nombre']}</option>"; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col"><label class="form-label small fw-bold">Grado</label><select name="id_grado" class="form-select"><?php $g=$pdo->query("SELECT * FROM cat_grados"); while($r=$g->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$r['id_grado']}'>{$r['numero']}°</option>"; ?></select></div>
                        <div class="col"><label class="form-label small fw-bold">Turno</label><select name="id_turno" class="form-select"><?php $t=$pdo->query("SELECT * FROM cat_turnos"); while($r=$t->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$r['id_turno']}'>{$r['nombre']}</option>"; ?></select></div>
                    </div>
                    <button type="submit" class="btn btn-verde w-100">Crear</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfig" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="ri-settings-4-line me-2"></i>Configuración del Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6 border-end">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Catálogo de Carreras</h6>
                        <form action="config.php" method="POST" class="row g-2 mb-4">
                            <input type="hidden" name="tipo" value="carrera">
                            <div class="col-md-7"><input type="text" name="nombre" class="form-control form-control-sm" placeholder="Nombre" required></div>
                            <div class="col-md-3"><input type="text" name="siglas" class="form-control form-control-sm" placeholder="Siglas" required></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-dark btn-sm w-100"><i class="ri-add-line"></i></button></div>
                        </form>
                        <div class="table-responsive" style="max-height: 300px; overflow-y:auto;">
                            <table class="table table-sm table-hover border">
                                <thead class="table-light sticky-top"><tr><th>Carrera</th><th>Siglas</th><th class="text-end">Acción</th></tr></thead>
                                <tbody>
                                    <?php
                                    $car = $pdo->query("SELECT * FROM carreras WHERE estatus='activo' ORDER BY nombre ASC");
                                    while($c = $car->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr><td class='small'>{$c['nombre']}</td><td><span class='badge bg-light text-dark border'>{$c['siglas']}</span></td><td class='text-end'><a href='config.php?accion=eliminar&tipo=carrera&id={$c['id_carrera']}' class='text-danger'><i class='ri-delete-bin-line'></i></a></td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Grados Escolares</h6>
                        <form action="config.php" method="POST" class="row g-2 mb-4 align-items-center">
                            <input type="hidden" name="tipo" value="grado">
                            <div class="col-8"><input type="number" name="numero_grado" class="form-control form-control-sm" placeholder="Ej. 10" required></div>
                            <div class="col-4"><button type="submit" class="btn btn-secondary btn-sm w-100">Agregar</button></div>
                            <div class="col-12 mt-2"><small class="text-muted">Actuales: <?php $gs = $pdo->query("SELECT numero FROM cat_grados ORDER BY numero ASC"); while($g = $gs->fetch(PDO::FETCH_ASSOC)) echo "<span class='badge bg-light text-dark border me-1'>{$g['numero']}°</span>"; ?></small></div>
                        </form>
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Turnos Disponibles</h6>
                        <form action="config.php" method="POST" class="row g-2 align-items-center">
                            <input type="hidden" name="tipo" value="turno">
                            <div class="col-8"><input type="text" name="nombre_turno" class="form-control form-control-sm" placeholder="Ej. Nocturno" required></div>
                            <div class="col-4"><button type="submit" class="btn btn-secondary btn-sm w-100">Agregar</button></div>
                            <div class="col-12 mt-2"><small class="text-muted">Actuales: <?php $ts = $pdo->query("SELECT nombre FROM cat_turnos"); while($t = $ts->fetch(PDO::FETCH_ASSOC)) echo "<span class='badge bg-light text-dark border me-1'>{$t['nombre']}</span>"; ?></small></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function abrirModalRegistro() {
        document.getElementById('input_id_alumno').value = ''; document.getElementById('input_nombre').value = '';
        document.getElementById('input_ap_paterno').value = ''; document.getElementById('input_ap_materno').value = '';
        document.getElementById('input_grupo').value = ''; document.getElementById('tituloModalAlumno').innerText = 'Registrar Alumno';
        document.getElementById('btnGuardarAlumno').innerText = 'Guardar';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }
    function cargarEdicion(a) {
        document.getElementById('input_id_alumno').value = a.idAlumno; // CAMELCASE por la API
        document.getElementById('input_nombre').value = a.nombre;
        document.getElementById('input_ap_paterno').value = a.apPaterno;
        document.getElementById('input_ap_materno').value = a.apMaterno;
        document.getElementById('input_grupo').value = a.idGrupo; 
        document.getElementById('tituloModalAlumno').innerText = 'Editar Alumno';
        document.getElementById('btnGuardarAlumno').innerText = 'Actualizar';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }
</script>
</body>
</html>