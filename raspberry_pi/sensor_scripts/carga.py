import json
import psycopg2
import board
import adafruit_dht
import RPi.GPIO as GPIO
import time

# Leer configuración de la base de datos
with open('db.json') as f:
    DB_CONFIG = json.load(f)

# Leer configuración de sensores
with open('sensor_conf.json') as f:
    config = json.load(f)

# Inicializar GPIO
GPIO.setmode(GPIO.BCM)

# Funciones por tipo de sensor
def leer_temperatura(gpio):
    sensor = adafruit_dht.DHT22(getattr(board, f"D{gpio}"))  # Usa board.Dxx
    time.sleep(2)
    return sensor.temperature, "°C"


def leer_humedad(gpio):
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(gpio, GPIO.IN)
    valor = GPIO.input(gpio)  # 0 = húmedo, 1 = seco
    humedad = 0.0 if valor else 1.0
    return humedad, "boolean"

def leer_luz(gpio):
    GPIO.setup(gpio, GPIO.IN)
    valor = GPIO.input(gpio)

    # LM393: GPIO.LOW (0) = luz, GPIO.HIGH (1) = oscuridad
    luz_detectada = 10.0 if valor == GPIO.LOW else 0.0
    return luz_detectada, "boolean"


# Diccionario de funciones lectoras
LECTORES = {
    "temperatura": leer_temperatura,
    "humedad": leer_humedad,
    "luz": leer_luz
}

# Conexión a la base de datos
conn = psycopg2.connect(**DB_CONFIG)
cur = conn.cursor()

# Recorre todos los sensores definidos en el JSON
for sensor in config["sensores"]:
    tipo = sensor["tipo"]
    gpio = sensor["gpio"]
    id_sensor = sensor["id_sensor"]

    try:
        valor, unidad = LECTORES[tipo](gpio)
        print(f"📡 Sensor {tipo} (ID {id_sensor}) en GPIO {gpio}: {valor} {unidad}")

        query = "INSERT INTO lecturas (id_sensor, valor, unidad) VALUES (%s, %s, %s)"
        cur.execute(query, (id_sensor, valor, unidad))

    except Exception as e:
        print(f"❌ Error con sensor {id_sensor} ({tipo}): {e}")

# Finalizar conexión
conn.commit()
cur.close()
conn.close()
GPIO.cleanup()