<?php


class DatosCredito extends Controller
{

    function __construct()
    {

        parent::__construct();
    }
    function render()
    {
        // $this->view->render('principal/nueva');
    }

    function Datos_Credito()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['ID_UNICO'])) {
                $ID_UNICO = $_POST['ID_UNICO'];
                // Aquí puedes procesar el ID_UNICO según tus necesidades
                // echo "ID_UNICO recibido: " . htmlspecialchars($ID_UNICO);
                $Ventas =  $this->model->Datos_Credito($ID_UNICO);
            } else {
                echo "No se recibió el parámetro ID_UNICO";
            }
        } else {
            echo "Método de solicitud no permitido";
        }
    }
}
