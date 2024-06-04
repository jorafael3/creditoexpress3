import time
import click
# from numpy import can_cast
import pandas as pd
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import Select
import json
from selenium.webdriver.chrome.service import Service
import math
import os
import PyPDF2
from PyPDF2 import PdfReader
import re
import zipfile
import mysql.connector
from datetime import datetime
from datetime import datetime, timedelta
from email import encoders
from email.mime.base import MIMEBase
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.application import MIMEApplication
import smtplib
from datetime import datetime
from bs4 import BeautifulSoup

options = Options()
options.add_argument("--start-maximized")
# options.add_argument("--headless")
# options.add_argument('--no-sandbox')
options.add_experimental_option('excludeSwitches', ['enable-logging'])
path = os.path.dirname(os.path.abspath(__file__))+"\pdf"
# dire = 'C:/xampp/htdocs/svsysback/scrapy/pdf'
prefs = {"download.default_directory": path}
options.add_experimental_option("prefs", prefs)
ser = Service()
driver = webdriver.Chrome(service=ser, options=options)


def cargar_Datos():
    DATOS = {
        "CEDULA":"0918045543",
        "CORREO":"FSALVATIERRA.FS@GMAIL.COM",
        "CELULAR":"0999309899",
        "ACTIVIDAD_ECONOMICA":"DEPENDIENTE",
        "INGRESOS":"2000",
        "PARROQUIA":"CHONGON",
        "ZONA":"VIA LA COSTA",
        "SECTOR":"TERRANOSTRA",
        "BARRIO":"FRAGATA",
        "MONTO_SOLICITAR":"5000",
        "PLAZO":"24 meses",

    }
    login(DATOS)


def login(DATOS):
    # options = webdriver.ChromeOptions()
    # options.add_argument('--no-sandbox')
    # options.add_argument('--disable-dev-shm-usage')
    # # options.add_argument('--headless')  # Opcional: correr el navegador en modo headless

    # driver = webdriver.Chrome(options=options)

    driver.get('https://creditoexpress.bancodelaustro.com/auth/login')
    time.sleep(10)
        # Hacer scroll hasta el final de la página
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
    time.sleep(3)  # Esperar un poco para asegurar que el scroll se complete



    ced = driver.find_element(By.XPATH, '//*[@id="email"]')
    ced.send_keys("fabricio.salvatierra@salvacero.com")
    time.sleep(5)
    passw = driver.find_element(By.XPATH, '//*[@id="password"]')
    passw.send_keys("Fabricio1@")
    time.sleep(5)
    btn = driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Ingresar')]")
    btn.click()
    time.sleep(10)

    btn_mul = driver.find_element(By.XPATH, '/html/body/app-root/section/app-menu/div/div/div[2]/div/div/div[3]')
    btn_mul.click()
    time.sleep(5)

    # ************************************************
    #INFORMACION DEL COMPRADOR
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
    time.sleep(3) 
     
    ced = driver.find_element(By.XPATH, '//*[@id="identification"]')
    ced.send_keys(DATOS["CEDULA"])
    time.sleep(5)

    ced = driver.find_element(By.XPATH, '//*[@id="email"]')
    ced.send_keys(DATOS["CORREO"])
    time.sleep(3)

    try:
        select_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "select[formcontrolname='creditReason']"))
        )
        select = Select(select_element)
        select.select_by_visible_text("EQUIPAMIENTO DE HOGAR U OFICINA")
        time.sleep(5)
    except Exception as e:
        print(f"Ocurrió un error: {e}")
   
    print("COMBO CAMBIADO")

    try:
        btn = driver.find_element(By.XPATH, "//button[@type='button' and contains(text(), 'Continuar')]")
        btn.click()
        time.sleep(5)
    except Exception as e:
        print(f"Ocurrió un error: {e}")
    # ************************************************


    try:
        select_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "select[formcontrolname='activity']"))
        )
        select = Select(select_element)
        select.select_by_visible_text("DEPENDIENTE")
        time.sleep(5)
    except Exception as e:
        print(f"Ocurrió un error: {e}")
    finally:
        pass

    ced = driver.find_element(By.XPATH, '//*[@id="salary"]')
    ced.clear()
    time.sleep(1)
    ced.send_keys(DATOS["INGRESOS"])
    time.sleep(5)

    try:
        select_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "select[formcontrolname='parish']"))
        )
        select = Select(select_element)
        select.select_by_visible_text(DATOS["PARROQUIA"])
        time.sleep(5)
    except Exception as e:
        print(f"Ocurrió un error: {e}")
    finally:
        pass

    ced = driver.find_element(By.XPATH, '//*[@id="typeahead-focus-area"]')
    ced.clear()
    ced.send_keys(DATOS["ZONA"])
    time.sleep(5)

    ced = driver.find_element(By.XPATH, '//*[@id="typeahead-focus-sector"]')
    ced.clear()
    ced.send_keys(DATOS["SECTOR"])
    time.sleep(5)

    ced = driver.find_element(By.XPATH, '//*[@id="typeahead-focus-neighborhood"]')
    ced.clear()
    ced.send_keys(DATOS["BARRIO"])
    time.sleep(5)

    driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
    time.sleep(5)  # Esperar un poco para asegurar que el scroll se complete

    btn = driver.find_element(By.XPATH, "//button[contains(text(), 'Continuar')]")
    btn.click()
    time.sleep(5)

    max_retries = 1  # Set a maximum number of retries
    NEGADO  = 0
    try:
            credito_negado = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Crédito Negado')]"))
            )
            # ... (Process the result as needed)
            NEGADO = 1

    except Exception as e:  # Handle other exceptions
            NEGADO = 0
            
    if NEGADO == 1:
        print("CREDITO NEGADO")
    else:
            ced = driver.find_element(By.XPATH, '//*[@id="amount"]')
            ced.clear()
            ced.send_keys(DATOS["MONTO_SOLICITAR"])
            time.sleep(5)

            driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(2)  # Esperar un poco para asegurar que el scroll se complete

            try:
                select_element = WebDriverWait(driver, 10).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, "select[formcontrolname='installmentMonths']"))
                )
                select = Select(select_element)
                select.select_by_visible_text(DATOS["PLAZO"])
                time.sleep(5)
            except Exception as e:
                    print(f"Ocurrió un error: {e}")
            finally:
                    pass

            btn = driver.find_element(By.XPATH, "//button[contains(text(), 'Calcular')]")
            btn.click()
            time.sleep(5)

            driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(2)  # Esperar un poco para asegurar que el scroll se complete

            btn = driver.find_element(By.XPATH, "//a[contains(text(), 'Detalle del crédito')]")
            btn.click()
            time.sleep(5)

            element = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.CSS_SELECTOR, '.containerDetails')))
            div_content = element.get_attribute("innerHTML")
            print(div_content)
            time.sleep(5)
            print("La palabra 'Crédito Negado' no ha sido encontrada en la página.")

    time.sleep(50)

# cargar_Datos()
    
def Datos_Aprobados():
           
    div = """
        <div _ngcontent-lmv-c139="" class="container">
            <div _ngcontent-lmv-c139="" class="row row-cols-4 gx-2 my-3">
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable">Valor cuota mensual</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable dataTableOne"> $ 259.86 </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable dataTableTwo"> Tasa reajustable </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable"> 15.60% </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable">Periodicidad</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable dataTableOne">Mensual</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable dataTableTwo">Segmento</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable">Consumo</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable">Sistema de amortización</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable dataTableOne">Francesa</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 listTable dataTableTwo">Plazo</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col">
                <div _ngcontent-lmv-c139="" class="p-0 dataTable"> 24 meses </div>
                </div>
            </div>
        </div>
        <hr _ngcontent-lmv-c139="" class="dividerTable">
        <div _ngcontent-lmv-c139="" class="mt-0 mb-5">
            <div _ngcontent-lmv-c139="" class="container text-center" style="width: 50%; margin-left: 50%; justify-content: flex-end;">
                <div _ngcontent-lmv-c139="" class="row gx-1">
                <div _ngcontent-lmv-c139="" class="col-7">
                    <div _ngcontent-lmv-c139="" class="p-1 finalValues">Contribución Solca</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-5">
                    <div _ngcontent-lmv-c139="" class="p-1 dataTable"> $26.77 </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-7">
                    <div _ngcontent-lmv-c139="" class="p-1 finalValues">Gastos y costos</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-5">
                    <div _ngcontent-lmv-c139="" class="p-1 dataTable">$ 0</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-7">
                    <div _ngcontent-lmv-c139="" class="p-1 finalValues">Seguro de desgravamen</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-5">
                    <div _ngcontent-lmv-c139="" class="p-1 dataTable"> $54.53 </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-7">
                    <div _ngcontent-lmv-c139="" class="p-1 finalValues">Encargo fiduciario</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-5">
                    <div _ngcontent-lmv-c139="" class="p-1 dataTable"> $206.75 </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-9" style="background-color: rgba(217, 217, 217, 0.2);">
                    <div _ngcontent-lmv-c139="" class="p-1 finalData">Valor total del crédito</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-3" style="background-color: rgba(217, 217, 217, 0.2);">
                    <div _ngcontent-lmv-c139="" class="p-1 finalDataValues"> $5327.95 </div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-9" style="background-color: rgba(217, 217, 217, 0.2);">
                    <div _ngcontent-lmv-c139="" class="p-1 finalData">Líquido a recibir</div>
                </div>
                <div _ngcontent-lmv-c139="" class="col-3" style="background-color: rgba(217, 217, 217, 0.2);">
                    <div _ngcontent-lmv-c139="" class="p-1 finalDataValues">$5039.90</div>
                </div>
                </div>
            </div>
        </div>
    """
    soup = BeautifulSoup(div, 'lxml')
    # Extract the main data table
    data_rows = soup.select('div.row.row-cols-4 > div.col')

    data = {}
    for i in range(0, len(data_rows), 2):
        key = data_rows[i].text.strip()
        value = data_rows[i + 1].text.strip()
        data[key] = value

    # Extract the final values
    final_values_rows = soup.select('div.container.text-center > div.row > div')

    final_values = {}
    for i in range(0, len(final_values_rows), 2):
        key = final_values_rows[i].text.strip()
        value = final_values_rows[i + 1].text.strip()
        final_values[key] = value

    # Combine both dictionaries
    data.update(final_values)

    print(data)


Datos_Aprobados()


