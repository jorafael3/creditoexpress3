from asyncio import sleep
import subprocess
import time
import mysql.connector
from mysql.connector import Error
import json
import base64
# import requests
from datetime import datetime
# from Crypto.PublicKey import RSA
# from Crypto.Cipher import PKCS1_OAEP
import os
import subprocess
import requests

# //pip install pycryptodome


def Cargar_Datos():
     
    # while True:
        try:
            time.sleep(0.4)
            # conexion = mysql.connector.connect(
            #     host="50.87.184.179",
            #     user="wsoqajmy_jorge",
            #     password="Equilivre3*",
            #     database="wsoqajmy_crediweb"
            # )
            conexion = mysql.connector.connect(
                host="localhost",
                user="root",
                password="",
                database="crediweb"
            )
            if conexion.is_connected():
                # print("Conexión establecida")
                pass

            cursor = conexion.cursor()
            consulta = 'SELECT ID_UNICO, credito_aprobado FROM creditos_solicitados WHERE estado = 1 and credito_aprobado = 1'
            # valores = (numero,)
            cursor.execute(consulta)
            resultados = cursor.fetchall()
            # print((resultados))
            for row in resultados:
                # print(row[0])
                ID_UNICO = row[0]
                 
                # URL del endpoint al que se enviarán los datos
                url = "http://127.0.0.1/creditoexpress3/datoscredito/Datos_Credito"
                payload = {'ID_UNICO': ID_UNICO}
                
                # Realizar la solicitud POST
                response = requests.post(url, data=payload)
                
                # Imprimir la respuesta de la solicitud
                print(response.text)


            cursor.close()
            conexion.close()

        except Error as e:
            print("Error de conexión:", e)
            print("Intentando reconectar...")
            # continue


if __name__ == "__main__":
    Cargar_Datos()