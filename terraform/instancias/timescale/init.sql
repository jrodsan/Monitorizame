-- Crear extensión TimescaleDB (si es PostgreSQL con TimescaleDB habilitado)
CREATE EXTENSION IF NOT EXISTS timescaledb;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL, -- Debe estar hasheada con bcrypt u otro algoritmo
    nombre_completo VARCHAR(100),
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
