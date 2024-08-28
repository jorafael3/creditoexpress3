import time
import subprocess
import mysql.connector
from mysql.connector import Error
import logging
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
# Configuración del servidor de correo
SMTP_SERVER = "smtp.gmail.com"  # Cambia esto si usas otro servidor SMTP
SMTP_PORT = 587  # Usualmente 587 para TLS
SMTP_USER = "jalvaradoe3@gmail.com"  # Cambia a tu correo
SMTP_PASSWORD = "bfdv olrv ctlf kgzf"  # Cambia a tu contraseña

# Configuración de destinatario y remitente
FROM_EMAIL = SMTP_USER
TO_EMAILS = ["jalvaradoe3@gmail.com", "marketing@salvacero.com","gerencia@salvacero.com"]  # Añade aquí tus correos

# Configuración de logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def enviar_correo_error(mensaje):
    try:
        # Configuración del correo
        msg = MIMEMultipart()
        msg['From'] = FROM_EMAIL
        msg['To'] = ", ".join(TO_EMAILS)  # Convierte la lista a una cadena separada por comas
        msg['Subject'] = "Error en el Script de Encriptación"
        
        # Cuerpo del correo
        mensaje = "ERROR EN EL ENCRIPTADOR, PONERSE EN CONTACTO PARA REVISARLO "+ str(mensaje)
        msg.attach(MIMEText(mensaje, 'plain'))
        
        # Conectar al servidor SMTP
        server = smtplib.SMTP(SMTP_SERVER, SMTP_PORT)
        server.starttls()
        server.login(SMTP_USER, SMTP_PASSWORD)
        server.send_message(msg)
        server.quit()
        
        logging.info("Correo de error enviado correctamente.")
    except Exception as e:
        logging.error(f"Fallo al enviar el correo: {e}")


def run_csharp_file_with_parameter(parameter):
    dotnet_executable = "C:\\Program Files\\dotnet\\dotnet.exe"
    csharp_file = "C:\\xampp\\htdocs\\creditoexpress3\\MyProject2\\bin\\Debug\\net6.0\\MyProject2.dll"
    command = [dotnet_executable, csharp_file, parameter]
    
    try:
        output = subprocess.check_output(command)
        return output.decode('utf-8')
    except subprocess.CalledProcessError as e:
        logging.error(f"Error ejecutando archivo C#: {e}")
        return None

def Guardar_encrypt(cedula_encr, cedula, conexion):
    try:
        cursor = conexion.cursor()
        consulta = """
            UPDATE encript_agua
                SET cedula_encrypt = %s,
                    encrypt = 1
            WHERE cedula = %s
        """
        valores = (cedula_encr, cedula)
        cursor.execute(consulta, valores)
        conexion.commit()
    except Error as e:
        logging.error(f"Error al guardar en la base de datos: {e}")
        raise

def Cargar_Datos():
    intentos = 0
    while True:
        try:

            time.sleep(0.5)

            conexion = mysql.connector.connect(
                host="50.87.184.179",
                user="wsoqajmy_jorge",
                password="Equilivre3*",
                database="wsoqajmy_crediweb"
            )

            if conexion.is_connected():
                logging.info("Conexión establecida")
                intentos = 0  # Resetea el contador de intentos después de una conexión exitosa

            cursor = conexion.cursor()
            consulta = 'SELECT cedula FROM encript_agua WHERE encrypt = 0'
            cursor.execute(consulta)
            resultados = cursor.fetchall()
            print(len(resultados))
            for row in resultados:
                cedula_encr = run_csharp_file_with_parameter(row[0])
                if cedula_encr:
                    print(row[0])
                    Guardar_encrypt(cedula_encr, row[0], conexion)
                    logging.info(f"Cédula {row[0]} encriptada y guardada correctamente")
                    print(cedula_encr)
            cursor.close()
            conexion.close()
            
        except Error as e:
            logging.error(f"Error de conexión: {e}")
            intentos += 1
            sleep_time = min(2 ** intentos, 300)  # Espera exponencial con un máximo de 5 minutos
            logging.info(f"Reintentando en {sleep_time} segundos...")
            enviar_correo_error(f"Error de conexión: {e}")
            time.sleep(sleep_time)

        except Exception as e:
            logging.error(f"Error inesperado: {e}")
            enviar_correo_error(f"Error inesperado: {e}")
            raise  # Vuelve a lanzar la excepción para que se maneje de acuerdo a la lógica del script

if __name__ == "__main__":
    Cargar_Datos()
    # enviar_correo_error(f"Error inesperado:")

