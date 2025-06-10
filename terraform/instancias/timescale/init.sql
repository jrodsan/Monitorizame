-- Crear extensión TimescaleDB (si es PostgreSQL con TimescaleDB habilitado)
CREATE EXTENSION IF NOT EXISTS timescaledb;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL, -- Debe estar hasheada con bcrypt u otro algoritmo
    nombre_completo VARCHAR(100),
    chat_id_telegram BIGINT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Raspberry Pi
CREATE TABLE raspberry_pi (
    id_raspberry SERIAL PRIMARY KEY,  
    id_usuario INT REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    nombre VARCHAR(100),
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de sensores
CREATE TABLE sensores (
    id_sensor SERIAL PRIMARY KEY,  
    id_raspberry INT REFERENCES raspberry_pi(id_raspberry) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Tabla de lecturas (usar hypertable con TimescaleDB)
CREATE TABLE lecturas (
    id_sensor INT REFERENCES sensores(id_sensor) ON DELETE CASCADE,
    valor FLOAT NOT NULL,
    unidad TEXT,
    fecha_hora TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (id_sensor, fecha_hora)
);

-- Convertir tabla de lecturas en hypertable (solo si estás usando TimescaleDB)
SELECT create_hypertable('lecturas', 'fecha_hora', if_not_exists => TRUE);

-- Tabla de configuraciones por sensor
CREATE TABLE configuraciones (
    id_configuracion SERIAL PRIMARY KEY,
    id_sensor INT REFERENCES sensores(id_sensor) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL, --poner nombre?
    umbral_min FLOAT,
    umbral_max FLOAT,
    alerta_activada BOOLEAN DEFAULT TRUE
);

-- Tabla de notificaciones generadas automáticamente
CREATE TABLE notificaciones (
    id_notificacion SERIAL PRIMARY KEY,
    id_usuario INT REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    mensaje TEXT NOT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de eventos (para seguimiento del sistema: login, errores, alertas, etc.)
CREATE TABLE eventos (
    id_evento SERIAL PRIMARY KEY,
    id_usuario INT REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    tipo_evento VARCHAR(50), -- ejemplo: 'login', 'alerta', 'sensor_offline'
    mensaje TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabla de fotos del huerto
CREATE TABLE fotos (
    id_foto SERIAL PRIMARY KEY,
    id_raspberry INT REFERENCES raspberry_pi(id_raspberry) ON DELETE CASCADE,
    url_foto TEXT NOT NULL, -- puede ser una URL o ruta de archivo local
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabla de alertas activas/históricas
CREATE TABLE alertas (
    id_alerta SERIAL PRIMARY KEY,
    id_sensor INT REFERENCES sensores(id_sensor) ON DELETE CASCADE,
    estado BOOLEAN NOT NULL, -- TRUE = activa, FALSE = finalizada
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP
);


-- Función para gestionar alertas en base a lecturas
CREATE OR REPLACE FUNCTION verificar_alerta()
RETURNS TRIGGER AS $$
DECLARE
    conf RECORD;
    alerta_existente RECORD;
BEGIN
    -- Buscar configuración del sensor
    SELECT * INTO conf FROM configuraciones WHERE id_sensor = NEW.id_sensor;

    -- Si no hay configuración, no hacemos nada
    IF NOT FOUND THEN
        RETURN NEW;
    END IF;

    -- Verificar si el valor está fuera de los umbrales definidos
    IF NEW.valor < conf.umbral_min OR NEW.valor > conf.umbral_max THEN
        -- Revisar si ya hay una alerta activa
        SELECT * INTO alerta_existente FROM alertas
        WHERE id_sensor = NEW.id_sensor AND estado = TRUE;

        -- Si no existe, creamos una nueva alerta activa
        IF NOT FOUND THEN
            INSERT INTO alertas (id_sensor, estado) VALUES (NEW.id_sensor, TRUE);
        END IF;
    ELSE
        -- Si el valor vuelve al rango normal, cerramos alerta si estaba activa
        UPDATE alertas
        SET estado = FALSE, fecha_fin = now()
        WHERE id_sensor = NEW.id_sensor AND estado = TRUE;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Trigger que llama a la función tras insertar una lectura
CREATE TRIGGER trigger_verificar_alerta
AFTER INSERT ON lecturas
FOR EACH ROW
EXECUTE FUNCTION verificar_alerta();