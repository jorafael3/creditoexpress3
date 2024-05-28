<?php

// require_once "models/logmodel.php";
require('public/fpdf/fpdf.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


class DatosCreditomodel extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    function Datos_Credito($ID_UNICO)
    {
        $DATOS_CLIENTE = $this->BUSCAR_DATOS_ID_UNICO($ID_UNICO);
        if ($DATOS_CLIENTE[0] == 1) {
            $REGISTRO  = $this->CONSULTA_API_REG($DATOS_CLIENTE[1][0]);
            echo json_encode($REGISTRO);
            exit();
        } else {
            echo json_encode($DATOS_CLIENTE);
            exit();
        }
    }

    function BUSCAR_DATOS_ID_UNICO($ID_UNICO)
    {
        try {
            $query = $this->db->connect_dobra()->prepare("SELECT * FROM creditos_solicitados 
                where ID_UNICO = :ID_UNICO");
            $query->bindParam(":ID_UNICO", $ID_UNICO, PDO::PARAM_STR);
            if ($query->execute()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return [1, $result];
            } else {
                return [0, "ERROR AL CONSULTAR DATOS " . $ID_UNICO];
            }
        } catch (PDOException $e) {
            $e = $e->getMessage();
            return [0, "INTENTE DE NUEVO"];
        }
    }

    
    function CONSULTA_API_REG($DATOS_CLIENTE)
    {
        $cedula_encr = $DATOS_CLIENTE["cedula_encr"];
        $encry2 = $DATOS_CLIENTE["cedula_encr2"];
        $ID_UNICO_TRANSACCION = $DATOS_CLIENTE["ID_UNICO"];
        $IMAGEN = $DATOS_CLIENTE["IMAGEN_CEDULA_NOMBRE"];
        $CEDULA_ = $DATOS_CLIENTE["cedula"];

        $IMAGE_PATH = "recursos/img_bio/" . $IMAGEN;
        $base64Imagen = $this->convertirImagenABase64($IMAGE_PATH);

        // return $base64Imagen;

        $CONSULTA_API_REG_BIO = $this->CONSULTA_API_REG_BIO($cedula_encr, $base64Imagen);
        if ($CONSULTA_API_REG_BIO[0] == 1) {
            $GUARDAR_DATOS_API_REG_BIO = $this->GUARDAR_DATOS_API_REG_BIO($ID_UNICO_TRANSACCION, $CONSULTA_API_REG_BIO[1], $IMAGEN);
            if ($GUARDAR_DATOS_API_REG_BIO[0] == 1) {
                $CONSULTA_API_REG_DEMOGRAFICO = $this->CONSULTA_API_REG_DEMOGRAFICO($encry2);
                if ($CONSULTA_API_REG_DEMOGRAFICO[0] == 1) {
                    $GUARDAR_DATOS_API_REG_DEMOGRAFICO = $this->GUARDAR_DATOS_API_REG_DEMOGRAFICO($ID_UNICO_TRANSACCION, $CONSULTA_API_REG_DEMOGRAFICO[1]);
                    if ($GUARDAR_DATOS_API_REG_DEMOGRAFICO[0] == 1) {
                        //$CONSULTA_API_REG_SENCILLA = $this->CONSULTA_API_REG_SENCILLA($cedula_encr);
                        // echo json_encode($CONSULTA_API_REG_SENCILLA);
                        // exit();
                        return $GUARDAR_DATOS_API_REG_DEMOGRAFICO;
                    } else {
                        return [0, "Error al procesar la informacion, intentelo de nuevo", $GUARDAR_DATOS_API_REG_DEMOGRAFICO];
                    }
                } else {
                    return [0, "Error al procesar la informacion, intentelo de nuevo", $CONSULTA_API_REG_DEMOGRAFICO];
                }
            } else {
                return [0, "Error al procesar la informacion, intentelo de nuevo", $GUARDAR_DATOS_API_REG_BIO];
            }
        } else {
            return [0, "Error al procesar la informacion, intentelo de nuevo", $CONSULTA_API_REG_BIO];
        }
    }

    function CONSULTA_API_REG_BIO($cedula_encr, $imagen)
    {
        $old_error_reporting = error_reporting();
        error_reporting($old_error_reporting & ~E_WARNING);

        try {

            $url = "https://reconocimiento-dataconsulting.ngrok.app/api/Reconocimiento?code=1LbmHAOC5xcBDW2Lw2eZrGDSQ-9nmBMFZ_sqbHHd7TVaAzFutMbWVQ==";
            $data = [
                "id" => $cedula_encr,
                "emp" => "SALVACERO",
                "selfie" => $imagen
            ];
            $jsonData = json_encode($data);
            $ch = curl_init($url);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Recibir la respuesta como una cadena de texto
            curl_setopt($ch, CURLOPT_POST, true); // Enviar una solicitud POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Datos a enviar en la solicitud POST
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'apiKey: DNkAgQHRnuMIwJFY3pVCrwDtmyuJajmQEMlE' // Agregar la API key en el encabezado
            ]);
            // Ejecutar la solicitud
            $response = curl_exec($ch);

            // Manejar errores
            if (curl_errno($ch)) {
                // echo 'Error:' . curl_error($ch);
                return [0, curl_error($ch)];
            } else {
                $data = json_decode($response, true);
                return [1, $data];
            }
            // Cerrar cURL
            curl_close($ch);
        } catch (Exception $e) {
            $e = $e->getMessage();
            echo json_encode($e);
            exit();
        }
    }

    function GUARDAR_DATOS_API_REG_BIO($ID_UNICO_TRANSACCION, $datos, $IMAGEN)
    {
        try {
            $data = $datos["DATOS"][0];
            // Conectar a la base de datos
            // Definir los parámetros
            $id_unico = $ID_UNICO_TRANSACCION;
            $IMG = $datos["FOTOGRAFIA"][0]["Fotografia"];
            $SIMILITUD = isset($datos["RECONOCIMIENTO"][0]["Similitud"]) ? $datos["RECONOCIMIENTO"][0]["Similitud"] : 0;
            $fileName = $ID_UNICO_TRANSACCION . "_CEDULA_BIO.jpeg";
            $estado = 0;

            $query = $this->db->connect_dobra()->prepare("INSERT INTO Datos_Reconocimiento(
                ID_UNICO, CEDULA, NOMBRES, DES_SEXO, DES_CIUDADANIA, FECHA_NACIM,
                PROV_NAC, CANT_NAC, PARR_NAC, DES_NACIONALIDAD, ESTADO_CIVIL,
                DES_NIV_ESTUD, DES_PROFESION, NOMBRE_CONYUG, CEDULA_CONYUG,
                FECHA_MATRIM, LUG_MATRIM, NOM_PADRE, NAC_PADRE, CED_PADRE,
                NOM_MADRE, NAC_MADRE, CED_MADRE, FECHA_DEFUNC, PROV_DOM,
                CANT_DOM, PARR_DOM, DIRECCION, INDIVIDUAL_DACTILAR,
                IMAGEN,
                IMAGEN_NOMBRE,
                SIMILITUD,
                estado
                ) VALUES (
                :ID_UNICO, :CEDULA, :NOMBRES, :DES_SEXO, :DES_CIUDADANIA, :FECHA_NAC,
                :PROV_NAC, :CANT_NAC, :PARR_NAC, :DES_NACIONALIDAD, :ESTADO_CIVIL,
                :DES_NIV_ESTUD, :DES_PROFESION, :NOMBRE_CONYUG, :CEDULA_CONYUG,
                :FECHA_MATRIM, :LUG_MATRIM, :NOM_PADRE, :NAC_PADRE, :CED_PADRE,
                :NOM_MADRE, :NAC_MADRE, :CED_MADRE, :FECHA_DEFUNC, :PROV_DOM,
                :CANT_DOM, :PARR_DOM, :DIRECCION, :INDIVIDUAL_DACTILAR,
                :IMAGEN,
                :IMAGEN_NOMBRE,
                :SIMILITUD,
                :estado
            )");
            //$serializedArray  = json_encode($ARRAY_BIO, JSON_PRETTY_PRINT);
            // Vincular los parámetros
            $query->bindParam(':ID_UNICO', $id_unico, PDO::PARAM_STR);
            $query->bindParam(':CEDULA', $data['CEDULA'], PDO::PARAM_STR);
            $query->bindParam(':NOMBRES', $data['NOMBRES'], PDO::PARAM_STR);
            $query->bindParam(':DES_SEXO', $data['DES_SEXO'], PDO::PARAM_STR);
            $query->bindParam(':DES_CIUDADANIA', $data['DES_CIUDADANIA'], PDO::PARAM_STR);
            $query->bindParam(':FECHA_NAC', $data['FECHA_NACIM'], PDO::PARAM_STR);
            $query->bindParam(':PROV_NAC', $data['PROV_NAC'], PDO::PARAM_STR);
            $query->bindParam(':CANT_NAC', $data['CANT_NAC'], PDO::PARAM_STR);
            $query->bindParam(':PARR_NAC', $data['PARR_NAC'], PDO::PARAM_STR);
            $query->bindParam(':DES_NACIONALIDAD', $data['DES_NACIONALIDAD'], PDO::PARAM_STR);
            $query->bindParam(':ESTADO_CIVIL', $data['ESTADO_CIVIL'], PDO::PARAM_STR);
            $query->bindParam(':DES_NIV_ESTUD', $data['DES_NIV_ESTUD'], PDO::PARAM_STR);
            $query->bindParam(':DES_PROFESION', $data['DES_PROFESION'], PDO::PARAM_STR);
            $query->bindParam(':NOMBRE_CONYUG', $data['NOMBRE_CONYUG'], PDO::PARAM_STR);
            $query->bindParam(':CEDULA_CONYUG', $data['CEDULA_CONYUG'], PDO::PARAM_STR);
            $query->bindParam(':FECHA_MATRIM', $data['FECHA_MATRIM'], PDO::PARAM_STR);
            $query->bindParam(':LUG_MATRIM', $data['LUG_MATRIM'], PDO::PARAM_STR);
            $query->bindParam(':NOM_PADRE', $data['NOM_PADRE'], PDO::PARAM_STR);
            $query->bindParam(':NAC_PADRE', $data['NAC_PADRE'], PDO::PARAM_STR);
            $query->bindParam(':CED_PADRE', $data['CED_PADRE'], PDO::PARAM_STR);
            $query->bindParam(':NOM_MADRE', $data['NOM_MADRE'], PDO::PARAM_STR);
            $query->bindParam(':NAC_MADRE', $data['NAC_MADRE'], PDO::PARAM_STR);
            $query->bindParam(':CED_MADRE', $data['CED_MADRE'], PDO::PARAM_STR);
            $query->bindParam(':FECHA_DEFUNC', $data['FECHA_DEFUNC'], PDO::PARAM_STR);
            $query->bindParam(':PROV_DOM', $data['PROV_DOM'], PDO::PARAM_STR);
            $query->bindParam(':CANT_DOM', $data['CANT_DOM'], PDO::PARAM_STR);
            $query->bindParam(':PARR_DOM', $data['PARR_DOM'], PDO::PARAM_STR);
            $query->bindParam(':DIRECCION', $data['DIRECCION'], PDO::PARAM_STR);
            $query->bindParam(':INDIVIDUAL_DACTILAR', $data['INDIVIDUAL_DACTILAR'], PDO::PARAM_STR);
            $query->bindParam(':IMAGEN', $IMG, PDO::PARAM_STR);
            $query->bindParam(':IMAGEN_NOMBRE', $fileName, PDO::PARAM_STR);
            $query->bindParam(':SIMILITUD', $SIMILITUD, PDO::PARAM_STR);
            $query->bindParam(':estado', $estado, PDO::PARAM_STR);

            // Ejecutar la consulta
            if ($query->execute()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                // $VAL = $this->GUARDAR_DATOS_VALIDACION_BIO($ARRAY_BIO, $id_unico);
                $data = base64_decode($IMG);
                $uploadDir = 'recursos/img_bio/';
                $filePath = $uploadDir . $fileName;
                $permisos = 0777;
                if (chmod($uploadDir, $permisos)) {
                }
                // Guardar la imagen en la carpeta
                if (file_put_contents($filePath, $data)) {
                }
                return [1, $SIMILITUD];
            } else {
                $err = $query->errorInfo();
                return [0, $err, "jjj"];
            }
        } catch (PDOException $e) {
            $e = $e->getMessage();
            return [0, "INTENTE DE NUEVO: " . $e];
        }
    }

    function CONSULTA_API_REG_DEMOGRAFICO($cedula_encr)
    {
        $old_error_reporting = error_reporting();
        // Desactivar los mensajes de advertencia
        error_reporting($old_error_reporting & ~E_WARNING);
        // Realizar la solicitud
        // Restaurar el nivel de informe de errores original
        try {
            $url = "https://consultadatos-dataconsulting.ngrok.app/api/ServicioMFC?clientId=" . trim($cedula_encr);
            // Datos a enviar en la solicitud POST
            $data = [
                "id" => $cedula_encr,
                "emp" => "SALVACERO",
                "img" => ""
            ];

            // Codificar los datos en formato JSON
            $jsonData = json_encode($data);
            // Inicializar cURL
            $ch = curl_init($url);
            // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Recibir la respuesta como una cadena de texto
            curl_setopt($ch, CURLOPT_POST, true); // Enviar una solicitud POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Datos a enviar en la solicitud POST
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'apiKey: DNkAgQHRnuMIwJFY3pVCrwDtmyuJajmQEMlE' // Agregar la API key en el encabezado
            ]);
            // Ejecutar la solicitud
            $response = curl_exec($ch);
            // Manejar errores
            if (curl_errno($ch)) {
                // echo 'Error:' . curl_error($ch);
                return [0, curl_error($ch)];
            } else {
                $data = json_decode($response, true);
                return [1, $data];
            }
            // Cerrar cURL
            curl_close($ch);
        } catch (Exception $e) {
            $e = $e->getMessage();
            return [0, $e];
        }
    }

    function GUARDAR_DATOS_API_REG_DEMOGRAFICO($ID_UNICO_TRANSACCION, $datos)
    {
        try {
            $data = $datos["CALIFICACION"][0];
            // echo json_encode($data);
            // exit();
            // Conectar a la base de datos
            // Definir los parámetros
            $id_unico = $ID_UNICO_TRANSACCION;

            // Preparar la consulta
            $query = $this->db->connect_dobra()->prepare("INSERT INTO Datos_Empleo (
                DEPENDIENTE, INDEPENDIENTE, CALIFICACION_SD, CALIFICACION_CR, 
                CALIFICACION_TOT, RELACION_DEPENDENCIA, SALARIO, 
                SALARIO_DEPURADO, CUOTA_ESTIMADA, IDENTIFICACION,ID_UNICO
            ) VALUES (
                :DEPENDIENTE, :INDEPENDIENTE, :CALIFICACION_SD, :CALIFICACION_CR, 
                :CALIFICACION_TOT, :RELACION_DEPENDENCIA, :SALARIO, 
                :SALARIO_DEPURADO, :CUOTA_ESTIMADA, :IDENTIFICACION,:ID_UNICO
            )");


            // Vincular los parámetros
            $query->bindParam(':DEPENDIENTE', $data['DEPENDIENTE'], PDO::PARAM_STR);
            $query->bindParam(':INDEPENDIENTE', $data['INDEPENDIENTE'], PDO::PARAM_STR);
            $query->bindParam(':CALIFICACION_SD', $data['CALIFICACION_SD'], PDO::PARAM_STR);
            $query->bindParam(':CALIFICACION_CR', $data['CALIFICACION_CR'], PDO::PARAM_STR);
            $query->bindParam(':CALIFICACION_TOT', $data['CALIFICACION_TOT'], PDO::PARAM_STR);
            $query->bindParam(':RELACION_DEPENDENCIA', $data['RELACION_DEPENDENCIA'], PDO::PARAM_STR);
            $query->bindParam(':SALARIO', $data['SALARIO'], PDO::PARAM_STR);
            $query->bindParam(':SALARIO_DEPURADO', $data['SALARIO_DEPURADO'], PDO::PARAM_STR);
            $query->bindParam(':CUOTA_ESTIMADA', $data['CUOTA_ESTIMADA'], PDO::PARAM_STR);
            $query->bindParam(':IDENTIFICACION', $data['IDENTIFICACION'], PDO::PARAM_STR);
            $query->bindParam(':ID_UNICO', $id_unico, PDO::PARAM_STR);

            // Ejecutar la consulta
            if ($query->execute()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return [1, $result];
            } else {
                $err = $query->errorInfo();
                return [0, $err];
            }
        } catch (PDOException $e) {
            $e = $e->getMessage();
            return [0, "INTENTE DE NUEVO: " . $e];
        }
    }

    function convertirImagenABase64($rutaImagen)
    {
        // Verifica si el archivo existe
        if (file_exists($rutaImagen)) {
            // Obtiene el contenido del archivo de la imagen
            $contenidoImagen = file_get_contents($rutaImagen);
            // Codifica el contenido de la imagen en base64
            $base64Imagen = base64_encode($contenidoImagen);
            // Obtiene el tipo MIME de la imagen
            $tipoImagen = mime_content_type($rutaImagen);
            // Devuelve la imagen en formato base64 con el prefijo de datos adecuado
            return $base64Imagen;
        } else {
            return 'El archivo de la imagen no existe.';
        }
    }
}
