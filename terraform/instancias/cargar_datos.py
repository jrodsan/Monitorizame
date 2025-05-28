import Adafruit_DHT
import psycopg2
import time

# Configuración del sensor DHT22 (puedes usar otro tipo de sensor si es necesario)
sensor = Adafruit_DHT.DHT22
pin = 4  # Pin GPIO donde está conectado el sensor

def obtener_datos_sensor():
    humedad, temperatura = Adafruit_DHT.read_retry(sensor, pin)
    if humedad is not None and temperatura is not None:
        return temperatura, humedad
    else:
        print("Error al leer el sensor")
        return None, None

# Conectar a la base de datos PostgreSQL
def conectar_db():
    try:
        conn = psycopg2.connect(
            dbname="sensordata",
            user="admin",
            password="admin",
            host="54.196.128.139",
            port="5432"
        )
        print("Conexión exitosa a la base de datos")
        return conn
    except Exception as e:
        print(f"Error de conexión: {e}")
        return None

# Insertar datos en la base de datos
def insertar_datos(temperatura, humedad):
    conn = conectar_db()
    if conn:
        cursor = conn.cursor()
        try:
            # Suponiendo que tienes una tabla "lecturas" con campos "temperatura" y "humedad"
            query = "INSERT INTO lecturas (temperatura, humedad) VALUES (%s, %s)"
            cursor.execute(query, (temperatura, humedad))
            conn.commit()
            print("Datos insertados con éxito")
        except Exception as e:
            print(f"Error al insertar datos: {e}")
        finally:
            cursor.close()
            conn.close()

# Función principal
def main():
    while True:
        temperatura, humedad = obtener_datos_sensor()
        if temperatura is not None and humedad is not None:
            insertar_datos(temperatura, humedad)
        time.sleep(60)  # Esperar 1 minuto antes de leer nuevamente

if __name__ == "__main__":
    main()
