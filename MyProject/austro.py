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
        "CEDULA":"0931531115",
        "CORREO":"jalvaradoe3@gmail.com",
        "CELULAR":"0969786231",
        "ACTIVIDAD_ECONOMICA":"DEPENDIENTE",
        "INGRESOS":"500",
        "PARROQUIA":"XIMENA",
        "ZONA":"SUR",
        "SECTOR":"SUR",
        "BARRIO":"FRAGATA"
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
    ced.send_keys("")
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
    ced.send_keys("")
    ced.send_keys(DATOS["ZONA"])
    time.sleep(5)

    ced = driver.find_element(By.XPATH, '//*[@id="typeahead-focus-sector"]')
    ced.send_keys("")
    ced.send_keys(DATOS["SECTOR"])
    time.sleep(5)

    ced = driver.find_element(By.XPATH, '//*[@id="typeahead-focus-neighborhood"]')
    ced.send_keys("")
    ced.send_keys(DATOS["BARRIO"])
    time.sleep(5)

    # btn = driver.find_element(By.XPATH, "//button[@type='button' and contains(text(), 'Continuar')]")
    # btn.click()
    # time.sleep(5)

    #  # Esperar hasta que aparezca el texto "Crédito Negado"
    # credito_negado = WebDriverWait(driver, 10).until(
    #         EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Crédito Negado')]"))
    # )
    # if credito_negado:
    #     print("La palabra 'Crédito Negado' ha sido encontrada en la página.")
    # else:
    #     print("La palabra 'Crédito Negado' no ha sido encontrada en la página.")


cargar_Datos()