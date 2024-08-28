<?php

$url_Validar_Celular = constant('URL') . 'principal/Validar_Celular/';
$url_Validar_Codigo = constant('URL') . 'principal/Validar_Codigo/';
$url_Validar_Cedula = constant('URL') . 'principal/Validar_Cedula/';



?>

<script>
    var url_Validar_Celular = '<?php echo $url_Validar_Celular ?>';
    var url_Validar_Codigo = '<?php echo $url_Validar_Codigo ?>';
    var url_Validar_Cedula = '<?php echo $url_Validar_Cedula ?>';

    var TELEFONO;
    var ID_UNICO;
    var IMAGE = null;
    var IMAGE_TIPO = null;
    var CODIGO_SMS = null;
    var IMAGECEDULA = null;
    var IMAGECEDULA_TIPO = null;

    function Mensaje(t1, t2, ic) {
        Swal.fire(
            t1,
            t2,
            ic
        );
    }

    $("#CELULAR").focus();

    var element = document.querySelector("#kt_stepper_example_basic");
    // Initialize Stepper
    var stepper = new KTStepper(element);
    // Handle next step
    stepper.on("kt.stepper.next", function(stepper) {

        if (stepper.getCurrentStepIndex() === 1) {
            var celularInput = document.querySelector("#CELULAR");
            celularInput = celularInput.value.trim();
            if (celularInput == "") {
                Mensaje("Debe ingresar un numero celular", "", "error");
                $("#CELULAR").focus();
                return false;
            } else if (celularInput.length != 10) {
                Mensaje("Debe ingresar un numero celular valido", "", "error");
                $("#CELULAR").focus();
                return false;
            } else {
                let terminos = $("#TERMINOS").is(":checked");
                if (terminos == false) {
                    Mensaje("Debe aceptar los terminos y condiciones para continuar", "", "error");
                    return false;
                } else {
                    Guardar_Celular();
                }
            }
            // var codeInputs = $('.code-input');
            // codeInputs.first().focus();
            // stepper.goNext();

        }
        if (stepper.getCurrentStepIndex() === 2) {
            // var codeInputs = $('.code-input');
            // codeInputs.first().focus();
            Validar_Codigo();

            // stepper.goNext();
        }

        // stepper.goNext();
    });

    stepper.on("kt.stepper.previous", function(stepper) {
        // stepper.goPrevious();
    });

    function Guardar_Celular() {
        let cel = $("#CELULAR").val();
        let terminos = $("#TERMINOS").is(":checked");
        let param = {
            celular: cel,
            terminos: terminos,
            tipo: 2
        }
        AjaxSendReceiveData(url_Validar_Celular, param, function(x) {



            if (x[0] == 1) {
                TELEFONO = x[1];
                ID_UNICO = x[3];
                stepper.goNext();
                // Validar_Codigo(x[3], x[4]);
                $("#SECC_COD").append(x[2]);
                var codeInputs = $('.code-input');
                codeInputs.first().focus();
            } else if (x[0] == 2) {
                $("#SECC_CEL").empty();
                $("#SECC_B").empty();
                $("#SECC_CEL").append(x[3]);
            } else {
                Mensaje(x[1], "", x[2]);
            }
        });
    }

    function Validar_Codigo() {
        var codeInputs = document.querySelectorAll('.code-input');
        var valores = Array.from(codeInputs).map(function(input) {
            return input.value;
        });
        let CON = 0;
        valores.map(function(x) {
            if (x.trim() == "") {
                Mensaje("Ingrese el codigo de 4 digitos", "", "error")
                return;
            } else {
                CON++;
            }
        });
        if (CON == 4) {
            let param = {
                TELEFONO: $("#CEL_1").val(),
                // TELEFONO: TEL,
                // CODIGO: CODIGO,
                CODIGO: valores,

            }
            AjaxSendReceiveData(url_Validar_Codigo, param, function(x) {

                if (x[0] == 1) {
                    $("#SECC_CRE").append(x[2]);
                    CODIGO_SMS = valores
                    // CODIGO_SMS = CODIGO
                    stepper.goNext();
                    // $("#SECC_B").addClass("d-none");

                } else {
                    Mensaje(x[1], "", x[2]);
                }
            });
        }


    }

    function Verificar() {
        let Cedula = $("#CEDULA").val();
        let cel = $("#CEL").val();
        let email = $("#CORREO").val();

        if (Cedula == "") {
            Mensaje("Debe ingresar un número de cédula valido", "", "error")
        } else {
            if (!validarCorreoElectronico(email.trim())) {
                Mensaje("Correo no válido", "por favor ingrese un correo valido", "error");
                return;
            }
            let param = {
                cedula: Cedula,
                celular: cel,
                email: email,
                tipo: 1,
                IMAGEN: IMAGE,
                IMAGECEDULA: IMAGECEDULA,
                CODIGO_SMS: CODIGO_SMS.join('')
            }

            $("#SECCION_GIF").removeClass("d-none");
            $("#SECCION_INGRESO_DATOS").addClass("d-none");
            $("#SECC_B").addClass("d-none");
            AjaxSendReceiveData(url_Validar_Cedula, param, function(x) {
                
                // $("#SECCION_GIF").addClass("d-none");
                // $("#SECCION_FOTO_CEDULA").removeClass("d-none");
                // $("#SECC_B").removeClass("d-none");
                // Mensaje(x[1],JSON.stringify(x[3]),"")
                if (x[0] == 1) {
                    $("#SECCION_GIF").addClass("d-none");
                    $("#SECC_CRE").empty();
                    $("#SECC_B").empty();
                    $("#SECCION_FOTO").empty();
                    $("#SECC_APR").append(x[3]);
                } else {
                    $("#SECCION_GIF").addClass("d-none");
                    $("#SECCION_INGRESO_DATOS").removeClass("d-none");
                    $("#SECC_B").removeClass("d-none");
                    Mensaje(x[1], x[2], "error");
                }
            });
            // if (IMAGE == null) {
            //     Mensaje("No se encontro una fotografia válida", "Por favor vuelva a tomar la foto", "error");
            // } else {

            // }
        }
    }

    $("#btnIrDatos").on("click", function(x) {

        let Cedula = $("#CEDULA").val();
        let CORREO = $("#CORREO").val();
        // if (IMAGE != null) {
        if (Cedula == "") {
            Mensaje("Debe ingresar un número de cédula valido", "", "error")
        } else {
            let val = validarCedulaEcuatoriana(Cedula);

            if (validarCedulaEcuatoriana(Cedula)) {
                if (validarCorreoElectronico(CORREO.trim())) {
                    $("#SECCION_FOTO").removeClass("d-none");
                    $("#SECCION_INGRESO_DATOS").addClass("d-none");
                    $("#SECC_B").removeClass("d-none");
                    $("#SECC_BTN_CON_DATOS").addClass("d-none");
                    $("#SECC_BTN_CON_DATOS_CEDULA").removeClass("d-none");
                } else {
                    Mensaje("Correo no válido", "por favor ingrese un correo valido", "error");
                }
            } else {

                Mensaje("La cédula ingresada no es valida", "por favor ingrese un número valido", "error");
            }
        }
    });

    $("#btnIrDatoscedula").on("click", function(x) {

        let Cedula = $("#CEDULA").val();

        // if (IMAGE != null) {
        if (Cedula == "") {
            Mensaje("Debe ingresar un número de cédula valido", "", "error")
        } else {

            if (validarCedulaEcuatoriana(Cedula)) {
                if (IMAGE == null) {
                    Mensaje("No se encontro foto", "por favor Debe tomarse un foto para continuar", "info")
                } else {
                    $("#SECCION_FOTO_CEDULA").removeClass("d-none");
                    $("#SECCION_FOTO").addClass("d-none");
                    $("#SECC_B").removeClass("d-none");
                    $("#SECC_BTN_CON_DATOS").addClass("d-none");
                    $("#SECC_BTN_CON_DATOS_CEDULA").addClass("d-none");
                }


            } else {


                Mensaje("La cédula ingresada no es valida", "por favor ingrese un número valido", "error")
            }


        }
    });

    $("#CEDULA").on('keydown', function(event) {
        if (event.which === 13) { // 13 is the keycode for Enter
            event.preventDefault();
            $("#btnIrDatos").click()
        }
    });

    $("#BtnBackToDatosCedula").on("click", function(x) {

        $("#SECCION_FOTO").addClass("d-none");
        $("#SECCION_INGRESO_DATOS").removeClass("d-none");
        $("#SECC_BTN_CON_DATOS_CEDULA").addClass("d-none");
        $("#SECC_BTN_CON_DATOS").removeClass("d-none");
        $("#SECC_B").addClass("d-none");

    });

    $("#BtnBackToDatosfoto").on("click", function(x) {

        $("#SECCION_FOTO").removeClass("d-none");
        $("#SECCION_FOTO_CEDULA").addClass("d-none");
        $("#SECC_BTN_CON_DATOS_CEDULA").removeClass("d-none");
        $("#SECC_BTN_CON_DATOS").addClass("d-none");
        $("#SECC_B").addClass("d-none");

    });

    $("#CELULAR").on("input", function() {
        var cleanedValue = $(this).val().replace(/\D/g, '');
        cleanedValue = cleanedValue.slice(0, 10);
        $(this).val(cleanedValue);
    });

    $("#CEDULA").on("input", function() {
        var cleanedValue = $(this).val().replace(/\D/g, '');
        cleanedValue = cleanedValue.slice(0, 10);
        $(this).val(cleanedValue);
    });

    function validarCedulaEcuatoriana(cedula) {
        // Verificar que la cédula tenga 10 dígitos
        if (cedula.length !== 10) {
            return false;
        }

        // Verificar que solo contenga números
        if (!/^\d+$/.test(cedula)) {
            return false;
        }

        // Extraer el código de la región y verificar que sea válido
        var region = parseInt(cedula.substring(0, 2));
        if (region < 1 || region > 24) {
            return false;
        }

        // Aplicar el algoritmo de verificación
        var total = 0;
        var digitos = cedula.split('').map(Number);
        var coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];

        for (var i = 0; i < coeficientes.length; i++) {
            var producto = digitos[i] * coeficientes[i];
            if (producto >= 10) {
                producto -= 9;
            }
            total += producto;
        }

        var digitoVerificador = total % 10 ? 10 - total % 10 : 0;

        // Verificar que el último dígito sea igual al dígito verificador
        return digitoVerificador === digitos[9];
    }

    function validarCorreoElectronico(email) {

        var regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        return regex.test(email);
    }

    const videoWidth = 320;
    const videoHeight = 320;
    const videoTag = $("#theVideo")[0];
    const canvasTag = $("#theCanvas")[0];
    const canvasTag2 = $("#theCanvas2")[0];
    const btnCapture = $("#btnCapture");
    const btnDownloadImage = $("#btnDownloadImage");
    const btnSendImageToServer = $("#btnSendImageToServer");
    const btnStartCamera = $("#btnStartCamera");

    let cameraActive = false;
    let stream;
    // Set initial button states
    btnCapture.prop("disabled", true);
    btnDownloadImage.prop("disabled", true);
    btnSendImageToServer.prop("disabled", true);

    // Set video and canvas attributes
    $(videoTag).attr("width", videoWidth).attr("height", videoHeight);
    $(canvasTag).attr("width", videoWidth).attr("height", videoHeight);
    $(canvasTag2).attr("width", videoWidth).attr("height", videoHeight);

    btnStartCamera.on("click", async () => {
        try {


            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    width: videoWidth,
                    height: videoHeight
                }
            });
            videoTag.srcObject = stream;
            btnStartCamera.prop("disabled", true);
            $("#theVideo").removeClass("d-none");
            $("#theCanvas").addClass("d-none");
            $("#SECC_VECTOR").addClass("d-none");
            $("#CANVAS_CAMARA").removeClass("d-none");

            // Enable buttons when the camera is active
            cameraActive = true;
            btnCapture.prop("disabled", false);
            IMAGE = null;
        } catch (error) {

            Mensaje("Error al iniciar la camara", "Asegurese de dar permisos a la camara, o tener una conectada", "error");
        }
    });

    btnCapture.on("click", () => {
        const canvasContext = canvasTag.getContext("2d");
        canvasContext.drawImage(videoTag, 0, 0, videoWidth, videoHeight);
        btnDownloadImage.prop("disabled", false);
        btnSendImageToServer.prop("disabled", false);
        const imageDataURL = canvasTag.toDataURL("image/jpeg");
        IMAGE = imageDataURL;


        btnCapture.prop("disabled", true);
        btnStartCamera.prop("disabled", false);
        $("#theVideo").addClass("d-none");
        $("#theCanvas").removeClass("d-none");
        cameraActive = false;
        stopCamera();
    });

    // const videoWidth2 = 320;
    // const videoHeight2 = 320;
    // const videoTag2 = document.getElementById("theVideo2");
    // const canvasTag3 = document.getElementById("theCanvas3");
    // const btnCapture2 = document.getElementById("btnCaptureCedula");
    // const btnStartCamera2 = document.getElementById("btnStartCameraCedula");

    // let cameraActive2 = false; // Variable para rastrear el estado de la cámara
    // var stream2;

    // videoTag2.setAttribute("width", videoWidth);
    // videoTag2.setAttribute("height", videoHeight);
    // canvasTag3.setAttribute("width", videoWidth);
    // canvasTag3.setAttribute("height", videoHeight);

    // btnStartCamera2.addEventListener("click", async () => {
    //     try {
    //         stream2 = await navigator.mediaDevices.getUserMedia({
    //             audio: false,
    //             video: {
    //                 width: videoWidth2,
    //                 height: videoHeight2
    //             },
    //         });
    //         videoTag2.srcObject = stream2;
    //         btnStartCamera2.disabled = true;
    //         $("#theVideo2").removeClass("d-none");
    //         $("#theCanvas3").addClass("d-none");
    //         $("#SECC_VECTOR2").addClass("d-none");
    //         $("#CANVAS_CAMARA2").removeClass("d-none");

    //         // Habilitar los botones cuando la cámara está activa
    //         cameraActive2 = true;
    //         btnCapture2.disabled = false;
    //         IMAGECEDULA = null
    //     } catch (error) {
    //         
    //         Mensaje("Error al iniciar la camara", "Asegurese de dar permisos a la camara, o tener una conectada", "error")
    //     }
    // });

    // btnCapture2.addEventListener("click", () => {
    //     const canvasContext = canvasTag3.getContext("2d");
    //     canvasContext.drawImage(videoTag2, 0, 0, videoWidth2, videoHeight2);
    //     const imageDataURL = canvasTag3.toDataURL("image/jpeg");
    //     btnDownloadImage.disabled = false;
    //     btnSendImageToServer.disabled = false;
    //     IMAGECEDULA = imageDataURL;
    //     // Hacer algo con la imagen en base64, como mostrarla en una etiqueta de imagen o enviarla al servidor
    //     
    //     btnCapture2.disabled = true;

    //     $("#theVideo2").addClass("d-none");
    //     $("#theCanvas3").removeClass("d-none");
    //     cameraActive2 = false;
    //     stopCamera2()

    // });



    // Detener la transmisión de la cámara
    function stopCamera() {
        if (stream) {

            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            videoTag.srcObject = null;
            stream = null;
            cameraActive = false;
            btnStartCamera.disabled = false;
        }
    }

    // function stopCamera2() {
    //     if (stream2) {
    //         
    //         const tracks = stream2.getTracks();
    //         tracks.forEach(track => track.stop());
    //         videoTag2.srcObject = null;
    //         stream2 = null;
    //         cameraActive2 = false;
    //         btnStartCamera2.disabled = false;
    //     }
    // }


    // $('#fileInput').on('change', function(event) {
    //     
    //     if (this.files && this.files[0]) {
    //         const file = this.files[0];
    //         // Check if the selected file is an image
    //         if (!file.type.startsWith('image/')) {
    //             Mensaje("Debe ser una foto de la cédula", "", "error");
    //             IMAGECEDULA = null
    //             
    //             return;
    //         }

    //         

    //         // Display file details in the console log
    //         
    //         
    //         

    //         // Optional: Read the file and display its contents
    //         const reader = new FileReader();
    //         reader.onload = function(e) {
    //             IMAGECEDULA = e.target.result
    //             IMAGECEDULA_TIPO = file.name.split('.').pop().toLowerCase();
    //             
    //             $('#imagePreview').attr('src', e.target.result);
    //             $('#CANVAS_CAMARA2').removeClass('d-none');
    //             $('#SECC_VECTOR2').addClass('d-none');

    //         };
    //         reader.readAsDataURL(file);
    //     }
    // });


    /**
     * Boton para forzar la descarga de la imagen
     */
    // btnDownloadImage.addEventListener("click", () => {
    //     const link = document.createElement("a");
    //     link.download = "capturedImage.png";
    //     link.href = canvasTag.toDataURL();
    //     link.click();
    // });

    /**
     *Enviar imagen al serrvidor para se guardada
     */
    // btnSendImageToServer.addEventListener("click", async () => {
    //     const dataURL = canvasTag.toDataURL();
    //     const blob = await dataURLtoBlob(dataURL);
    //     const data = new FormData();
    //     data.append("capturedImage", blob, "capturedImage.png");

    //     try {
    //         const response = await axios.post("upload.php", data, {
    //             headers: {
    //                 "Content-Type": "multipart/form-data"
    //             },
    //         });
    //         alert(response.data);
    //     } catch (error) {
    //         
    //     }
    // });

    async function dataURLtoBlob(dataURL) {
        const arr = dataURL.split(",");
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        const n = bstr.length;
        const u8arr = new Uint8Array(n);
        for (let i = 0; i < n; i++) {
            u8arr[i] = bstr.charCodeAt(i);
        }
        return new Blob([u8arr], {
            type: mime
        });
    }

    function AjaxSendReceiveData(url, data, callback) {
        var xmlhttp = new XMLHttpRequest();

        // Mostrar la barra de progreso al iniciar la solicitud AJAX
        $.blockUI({
            message: '<div class="d-flex justify-content-center align-items-center">' +
                '<p class="mr-3 mb-0">Estamos validando tus datos ...</p>' +
                '<div class="progress" style="width: 150px;">' +
                '<div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                '</div>' +
                '</div>',
            css: {
                backgroundColor: 'transparent',
                color: '#fff',
                border: '0'
            },
            overlayCSS: {
                opacity: 0.5
            }
        });

        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                // Ocultar la barra de progreso cuando la solicitud AJAX haya finalizado
                $.unblockUI();
                if (this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    callback(data);
                } else {
                    // Manejar errores aquí
                }
            }
        };

        xmlhttp.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                var percentComplete = (event.loaded / event.total) * 100;
                // Actualizar el valor de la barra de progreso mientras se carga la solicitud
                document.getElementById("progressBar").style.width = percentComplete + "%";
            }
        };

        xmlhttp.onerror = function() {
            // Ocultar la barra de progreso en caso de error
            $.unblockUI();
            // Manejar errores aquí
        };

        data = JSON.stringify(data);
        xmlhttp.open("POST", url, true);
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlhttp.send(data);
    }


    // function AjaxSendReceiveData(url, data, callback) {
    //     var xmlhttp = new XMLHttpRequest();
    //     $.blockUI({
    //         message: '<div class="d-flex justify-content-center align-items-center"><p class="mr-50 mb-0">Cargando ...</p> <div class="spinner-grow spinner-grow-sm text-white" role="status"></div> </div>',
    //         css: {
    //             backgroundColor: 'transparent',
    //             color: '#fff',
    //             border: '0'
    //         },
    //         overlayCSS: {
    //             opacity: 0.5
    //         }
    //     });

    //     xmlhttp.onreadystatechange = function() {
    //         if (this.readyState == 4 && this.status == 200) {
    //             var data = this.responseText;
    //             data = JSON.parse(data);
    //             callback(data);
    //         }
    //     }
    //     xmlhttp.onload = () => {
    //         $.unblockUI();
    //         // 
    //     };
    //     xmlhttp.onerror = function() {
    //         $.unblockUI();
    //     };
    //     data = JSON.stringify(data);
    //     xmlhttp.open("POST", url, true);
    //     xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    //     xmlhttp.send(data);

    // }
</script>