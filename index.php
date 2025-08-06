<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define( 'AIRTABLE_API_KEY', 'patvUvGWq9YnZEskT.9f8c04f354c9c11daa45758f8f79991cd52f0f267e15fa612d439ce17b46f049' );
define( 'AIRTABLE_BASE_ID', 'appY510xtAJMJRk6r' );
define( 'AIRTABLE_TABLE_APLICANTES', 'Aplicantes' ); 
define( 'AIRTABLE_TABLE_PUESTOS', 'Puestos' ); 
define( 'AIRTABLE_TABLE_DEPARTAMENTOS', 'Departamentos' ); 
define( 'AIRTABLE_TABLE_CLIENTES', 'Clientes' ); 
define( 'AIRTABLE_TABLE_PROYECTOS', 'Proyectos' ); 
define( 'AIRTABLE_TABLE_CONTRATOS', 'Contratos' ); 
define( 'AIRTABLE_API_URL', 'https://api.airtable.com/v0/' . AIRTABLE_BASE_ID . '/' );

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Realiza una solicitud HTTP a la API de Airtable usando cURL.
 *
 * @param string $url La URL completa de la API.
 * @param string $method El método HTTP (GET, POST, PATCH, DELETE).
 * @param array $data Los datos a enviar en el cuerpo de la solicitud (para POST/PATCH).
 * @return array|false Los datos decodificados de la respuesta JSON o false en caso de error.
 */
function airtable_api_request($url, $method = 'GET', $data = []) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . AIRTABLE_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if ($http_code >= 400) {
        error_log("Airtable API Error (HTTP $http_code): " . ($response ? $response : 'No response body'));
        return false; 
    }

    return $decoded_response;
}

/**
 * Obtiene datos de una tabla de Airtable.
 *
 * @param string $table_name El nombre de la tabla.
 * @param array $params Parámetros de consulta (ej. 'filterByFormula').
 * @return array Los registros de Airtable o un array vacío en caso de error/no resultados.
 */
function airtable_get_data($table_name, $params = []) {
    $url = AIRTABLE_API_URL . urlencode($table_name);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $response = airtable_api_request($url);

    // ELIMINADO: El var_dump duplicado de aquí
    // echo '<pre style="background-color:#f0f0f0; padding:10px; border:1px solid #ccc;">';
    // var_dump($response); // Esto mostrará lo que Airtable te está enviando
    // echo '</pre>';

    if ($response === false || !isset($response['records'])) {
        return [];
    }
    return $response['records'];
}

/**
 * Actualiza un registro en una tabla de Airtable.
 *
 * @param string $table_name El nombre de la tabla.
 * @param string $record_id El ID del registro a actualizar.
 * @param array $fields Los campos a actualizar.
 * @return array|false El registro actualizado o false en caso de error.
 */
function airtable_update_record($table_name, $record_id, $fields) {
    $url = AIRTABLE_API_URL . urlencode($table_name);
    $data = [
        'records' => [
            [
                'id' => $record_id,
                'fields' => $fields,
            ]
        ]
    ];
    return airtable_api_request($url, 'PATCH', $data);
}

function get_linked_record_name($table_name, $record_id) {
    if (empty($record_id)) {
        return 'N/A';
    }
    $record = airtable_get_data($table_name, ['filterByFormula' => "RECORD_ID() = '" . $record_id . "'"]);
    if (!empty($record) && isset($record[0]['fields'])) {
        $fields = $record[0]['fields'];
        // Intentar buscar por nombres de campos principales conocidos
        if (isset($fields['Nombre del Puesto'])) {
            return htmlspecialchars($fields['Nombre del Puesto']);
        } elseif (isset($fields['Nombre del Departamento'])) {
            return htmlspecialchars($fields['Nombre del Departamento']);
        } elseif (isset($fields['Nombre del Cliente'])) {
            return htmlspecialchars($fields['Nombre del Cliente']);
        } elseif (isset($fields['Nombre del Proyecto'])) {
            return htmlspecialchars($fields['Nombre del Proyecto']);
        } elseif (isset($fields['ID Contrato'])) {
            return htmlspecialchars($fields['ID Contrato']);
        }
       
        foreach ($fields as $key => $value) {
            if (is_string($value) && !empty($value)) {
                return htmlspecialchars($value);
            }
        }
    }
    return htmlspecialchars($record_id);
}


// --- LÓGICA PRINCIPAL DE LA APLICACIÓN ---

$message = '';

// Maneja la actualización de estado (si el formulario fue enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<p style="color: red;">Error de seguridad: Token CSRF inválido.</p>';
    } else {
        $record_id = htmlspecialchars($_POST['record_id']);
        $new_status = htmlspecialchars($_POST['new_status']);

        if (!empty($record_id) && !empty($new_status)) {
            $fields_to_update = ['Estado del Proceso' => $new_status];
            if ($new_status === 'Completada') {
                $fields_to_update['Fecha de Finalización'] = date('Y-m-d');
            }

            $result = airtable_update_record(AIRTABLE_TABLE_APLICANTES, $record_id, $fields_to_update);

            if ($result) {
                $message = '<p style="color: green;">Estado actualizado exitosamente.</p>';
            } else {
                $message = '<p style="color: red;">Error al actualizar el estado en Airtable.</p>';
            }
        } else {
            $message = '<p style="color: red;">ID de registro o nuevo estado no proporcionado.</p>';
        }
    }
}

// Determina qué vista mostrar (lista o detalle)
$aplicante_id = isset($_GET['aplicante_id']) ? htmlspecialchars($_GET['aplicante_id']) : '';
$csrf_token = generate_csrf_token();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Contrataciones - Airtable</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Seguimiento de Contrataciones</h1>
        <?php echo $message; ?>

        <?php if (!empty($aplicante_id)) :
            // --- VISTA DE DETALLE ---
            $aplicante = airtable_get_data(AIRTABLE_TABLE_APLICANTES, ['filterByFormula' => "RECORD_ID() = '" . $aplicante_id . "'"]);
            $aplicante = !empty($aplicante) ? $aplicante[0] : null;

            if (!$aplicante) {
                echo '<p style="color: red;">No se encontró el aplicante con el ID proporcionado o hubo un error al cargar los datos.</p>';
            } else {
                $fields = $aplicante['fields'];

                // Obtener nombres de registros enlazados
                $puesto_nombre = get_linked_record_name(AIRTABLE_TABLE_PUESTOS, $fields['Puesto al que Aplica'][0] ?? '');
                $departamento_nombre = get_linked_record_name(AIRTABLE_TABLE_DEPARTAMENTOS, $fields['Departamento'][0] ?? '');
                $cliente_nombre = get_linked_record_name(AIRTABLE_TABLE_CLIENTES, $fields['Cliente Asociado'][0] ?? '');
                $proyecto_nombre = get_linked_record_name(AIRTABLE_TABLE_PROYECTOS, $fields['Proyecto Asociado'][0] ?? '');
                $contrato_generado = get_linked_record_name(AIRTABLE_TABLE_CONTRATOS, $fields['Contrato Generado'][0] ?? '');

                $foto_perfil_url = 'https://placehold.co/150x150/cccccc/333333?text=Sin+Foto';
                if (isset($fields['Foto de Perfil'][0]['url'])) {
                    $foto_perfil_url = htmlspecialchars($fields['Foto de Perfil'][0]['url']);
                }

                $estado_proceso_actual = htmlspecialchars($fields['Estado del Proceso'] ?? 'N/A');
                $estados_posibles = ['Pendiente', 'En Proceso', 'Completada', 'Rechazado'];
                $status_class = 'airtable-status-' . strtolower(str_replace(' ', '-', $estado_proceso_actual));
                ?>
                <div class="airtable-detalle-card">
                    <h2>Detalle de Contratación: <?php echo htmlspecialchars($fields['Nombre Completo'] ?? 'N/A'); ?></h2>

                    <div class="airtable-detalle-left">
                        <img src="<?php echo $foto_perfil_url; ?>" alt="Foto de Perfil">
                        <div class="airtable-detalle-item">
                            <strong>Estado Actual:</strong>
                            <span class="<?php echo $status_class; ?>"><?php echo $estado_proceso_actual; ?></span>
                        </div>
                    </div>

                    <div class="airtable-detalle-right">
                        <div class="airtable-detalle-item">
                            <strong>Correo Electrónico:</strong>
                            <span><?php echo htmlspecialchars($fields['Correo Electrónico'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Teléfono:</strong>
                            <span><?php echo htmlspecialchars($fields['Teléfono'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Puesto al que Aplica:</strong>
                            <span><?php echo $puesto_nombre; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Salario Esperado:</strong>
                            <span><?php echo htmlspecialchars($fields['Salario Esperado'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Departamento:</strong>
                            <span><?php echo $departamento_nombre; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Fecha de Aplicación:</strong>
                            <span><?php echo isset($fields['Fecha de Aplicación']) ? date('d/m/Y', strtotime($fields['Fecha de Aplicación'])) : 'N/A'; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Responsable de Seguimiento:</strong>
                            <span><?php echo htmlspecialchars($fields['Responsable de Seguimiento'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Cliente Asociado:</strong>
                            <span><?php echo $cliente_nombre; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Proyecto Asociado:</strong>
                            <span><?php echo $proyecto_nombre; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Contrato Generado:</strong>
                            <span><?php echo $contrato_generado; ?></span>
                        </div>
                        <div class="airtable-detalle-item">
                            <strong>Notas / Comentarios:</strong>
                            <p><?php echo nl2br(htmlspecialchars($fields['Notas'] ?? 'N/A')); ?></p>
                        </div>

                        <?php if (isset($fields['CV / Portafolio']) && !empty($fields['CV / Portafolio'])) : ?>
                            <div class="airtable-detalle-item">
                                <strong>Adjuntos:</strong>
                                <?php foreach ($fields['CV / Portafolio'] as $attachment) : ?>
                                    <p><a href="<?php echo htmlspecialchars($attachment['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($attachment['filename']); ?></a></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="airtable-status-update-form">
                            <h3>Actualizar Estado del Proceso</h3>
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($aplicante_id); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                                <label for="new_status">Nuevo Estado:</label>
                                <select name="new_status" id="new_status">
                                    <?php foreach ($estados_posibles as $estado) : ?>
                                        <option value="<?php echo htmlspecialchars($estado); ?>" <?php echo ($estado_proceso_actual === $estado) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($estado); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Actualizar Estado</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="airtable-back-link">
                    <a href="index.php">&laquo; Volver a la lista de contrataciones</a>
                </div>
            <?php
            } // Fin if aplicante
        else :
            // --- VISTA DE LISTA ---
            $filter_formula_parts = [];
            // Por defecto, mostrar "Pendiente" y "En Proceso"
            $filter_formula_parts[] = "OR({Estado del Proceso} = 'Pendiente', {Estado del Proceso} = 'En Proceso')";

            // Aquí se construye la fórmula de filtro de manera segura
            $filter_formula = '';
            if ( ! empty( $filter_formula_parts ) ) {
                if ( count( $filter_formula_parts ) > 1 ) {
                    $filter_formula = 'AND(' . implode( ', ', $filter_formula_parts ) . ')';
                } else {
                    $filter_formula = $filter_formula_parts[0];
                }
            } else {
                $filter_formula = '';
            }

            $params = [];
            

            $aplicantes = airtable_get_data(AIRTABLE_TABLE_APLICANTES, $params);

            if (empty($aplicantes)) {
                echo '<p>No hay contrataciones activas o en proceso que coincidan con los criterios.</p>';
            } else {
            ?>
                <table class="airtable-contrataciones-lista">
                    <thead>
                        <tr>
                            <th>Nombre del Aplicante</th>
                            <th>Puesto</th>
                            <th>Departamento</th>
                            <th>Cliente</th>
                            <th>Proyecto</th>
                            <th>Estado</th>
                            <th>Fecha de Aplicación</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aplicantes as $aplicante) :
                            $fields = $aplicante['fields'];
                            $aplicante_id = $aplicante['id'];

                            $puesto_nombre = get_linked_record_name(AIRTABLE_TABLE_PUESTOS, $fields['Puesto al que Aplica'][0] ?? '');
                            $departamento_nombre = get_linked_record_name(AIRTABLE_TABLE_DEPARTAMENTOS, $fields['Departamento'][0] ?? '');
                            $cliente_nombre = get_linked_record_name(AIRTABLE_TABLE_CLIENTES, $fields['Cliente Asociado'][0] ?? '');
                            $proyecto_nombre = get_linked_record_name(AIRTABLE_TABLE_PROYECTOS, $fields['Proyecto Asociado'][0] ?? '');
                            $estado_proceso = htmlspecialchars($fields['Estado del Proceso'] ?? 'N/A');
                            $fecha_aplicacion = isset($fields['Fecha de Aplicación']) ? date('d/m/Y', strtotime($fields['Fecha de Aplicación'])) : 'N/A';
                            $responsable = htmlspecialchars($fields['Responsable de Seguimiento'] ?? 'N/A');

                            $status_class = 'airtable-status-' . strtolower(str_replace(' ', '-', $estado_proceso));
                        ?>
                            <tr>
                                <td><a href="?aplicante_id=<?php echo htmlspecialchars($aplicante_id); ?>"><?php echo htmlspecialchars($fields['Nombre Completo'] ?? 'N/A'); ?></a></td>
                                <td><?php echo $puesto_nombre; ?></td>
                                <td><?php echo $departamento_nombre; ?></td>
                                <td><?php echo $cliente_nombre; ?></td>
                                <td><?php echo $proyecto_nombre; ?></td>
                                <td><span class="<?php echo $status_class; ?>"><?php echo $estado_proceso; ?></span></td>
                                <td><?php echo $fecha_aplicacion; ?></td>
                                <td><?php echo $responsable; ?></td>
                                <td><a href="?aplicante_id=<?php echo htmlspecialchars($aplicante_id); ?>">Ver Detalle</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php
            }
        endif;
        ?>
    </div>
</body>
</html>
