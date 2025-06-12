import psycopg2
import requests

# Configuración de la base de datos
conn = psycopg2.connect(
    dbname='sensordata',  # Nombre de la base de datos
    user='admin',         # Usuario de la base de datos
    password='admin',     # Contraseña de la base de datos
    host='10.0.1.44',     # Dirección del servidor de base de datos (localhost si estás en la misma máquina)
    port=5432             # Puerto de PostgreSQL
)

# Token del bot de Telegram
BOT_TOKEN = '7600192330:AAHrinoG9Y6VNsU4xofE-ZxH4TS17jUgSCU'

# Función para enviar mensaje al usuario de Telegram
def enviar_mensaje_telegram(chat_id, mensaje):
    """Envia un mensaje a un usuario de Telegram."""
    url = f"https://api.telegram.org/bot{BOT_TOKEN}/sendMessage"
    data = {
        'chat_id': chat_id,
        'text': mensaje,
        'parse_mode': 'Markdown'  # Usamos markdown para formato
    }
    response = requests.post(url, data=data)
    return response.ok

# Función principal para verificar alertas y enviar mensajes
def verificar_alertas():
    """Verifica las alertas activas y envía un mensaje de Telegram si es necesario."""
    cursor = conn.cursor()

    # Consulta para obtener alertas activas y sus detalles asociados
    cursor.execute("""
        SELECT 
            a.id_alerta,
            a.id_sensor,
            s.tipo,
            u.chat_id_telegram,
            l.valor,
            l.unidad,
            l.fecha_hora
        FROM alertas a
        JOIN sensores s ON a.id_sensor = s.id_sensor
        JOIN raspberry_pi r ON s.id_raspberry = r.id_raspberry
        JOIN usuarios u ON r.id_usuario = u.id_usuario
        LEFT JOIN LATERAL (
            SELECT valor, unidad, fecha_hora
            FROM lecturas
            WHERE id_sensor = s.id_sensor
            ORDER BY fecha_hora DESC
            LIMIT 1
        ) l ON TRUE
        WHERE a.estado = TRUE AND u.chat_id_telegram IS NOT NULL;
    """)

    # Recuperamos las filas con las alertas activas
    alertas = cursor.fetchall()

    for alerta in alertas:
        id_alerta, id_sensor, tipo_sensor, chat_id, valor, unidad, fecha = alerta

        # Creamos el mensaje de alerta
        mensaje = (
            f"🚨 *Alerta Activada*\n"
            f"Sensor `{tipo_sensor}` (ID: {id_sensor})\n"
            f"Última lectura: *{valor} {unidad}* a las {fecha.strftime('%Y-%m-%d %H:%M:%S')}\n"
            f"Por favor, revisa el sensor."
        )

        # Enviar mensaje al chat de Telegram del usuario
        enviado = enviar_mensaje_telegram(chat_id, mensaje)
        print(f"[{'✔️' if enviado else '❌'}] Mensaje a {chat_id}: {mensaje}")

    # Cerramos el cursor y la conexión
    cursor.close()
    conn.close()

# Ejecutamos la función para verificar las alertas
if __name__ == "__main__":
    verificar_alertas()