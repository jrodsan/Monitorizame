#!/bin/bash

# Configuración
DB_CONTAINER="timescaledb"                      # Nombre del contenedor Docker
DB_USER="admin"                                 # Usuario de la base de datos
DB_NAME="sensordata"                            # Nombre de la base de datos
BACKUP_DIR="/home/ubuntu/db_backups"            # Directorio local de backups
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$(date +%F_%H-%M-%S).sql"
SFTP_HOST="10.0.1.32"
SFTP_PORT=2022
SFTP_USER="admin"
SFTP_KEY="/home/ubuntu/.ssh/sftp-user-key.pem"
REMOTE_DIR="upload/"

# Crear directorio si no existe
mkdir -p "$BACKUP_DIR"

# Crear el backup desde el contenedor
docker exec "$DB_CONTAINER" pg_dump -U "$DB_USER" -d "$DB_NAME" > "$BACKUP_FILE"

# Verificar si se creó el backup correctamente
if [[ $? -ne 0 ]]; then
  echo "❌ Error al generar la copia de seguridad"
  exit 1
fi

echo "✅ Backup generado: $BACKUP_FILE"

# Subir el backup al servidor SFTP
sftp -o StrictHostKeyChecking=no -i "$SFTP_KEY" -P "$SFTP_PORT" ${SFTP_USER}@${SFTP_HOST} <<EOF
put $BACKUP_FILE $REMOTE_DIR/
EOF

# Verificar si se subió correctamente
if [[ $? -eq 0 ]]; then
  echo "✅ Backup subido correctamente a $SFTP_HOST:$REMOTE_DIR"
else
  echo "❌ Error al subir el backup vía SFTP"
  exit 1
fi