# 🌿 Monitorizame - Sistema de Monitoreo de Cultivos

Este proyecto permite monitorear las condiciones ambientales de un huerto desde cualquier parte del mundo usando sensores conectados a una Raspberry Pi. Los datos son almacenados en una base de datos TimescaleDB alojada en AWS, visualizados en una interfaz web y respaldados automáticamente.

---

## 🔍 Características Principales

* Monitoreo de temperatura, humedad del suelo y luz ambiental
* Raspberry Pi como nodo de captura de datos
* Almacenamiento de datos en TimescaleDB (PostgreSQL)
* Interfaz web responsiva con Bootstrap + Chart.js
* Notificaciones automáticas por Telegram
* Backups diarios por SFTP mediante `cron`

---
## 🛠️ Roadmap - Monitorízame  

Este proyecto está en constante evolución. Estas son las mejoras que quiero ir implementando para hacerlo más completo, escalable y útil:  

### 🌱 Corto plazo  
- [ ] Automatización del riego según las condiciones del entorno  
- [ ] Mejorar la visibilidad de los datos 
- [ ] Opción de despliegue en local con **docker-compose** además de AWS  

### 🌿 Medio plazo  
- [ ] Implementar un **dashboard en Grafana** para análisis más completo  
- [ ] Añadir API REST para exponer datos de sensores  
- [ ] Optimización de la base de datos TimescaleDB para grandes volúmenes  

### 🌳 Largo plazo  
- [ ] Aplicación móvil para el monitoreo remoto  
- [ ] Integración con servicios de alertas más avanzados (ej. Twilio, Email)  
- [ ] Soporte para múltiples nodos de Raspberry Pi distribuidos  
---


---
🤝 Contribuciones

Este proyecto está en desarrollo y abierto a mejoras. ¡Las pull requests son bienvenidas!
---

## ⚙️ Requisitos del Sistema

### Hardware

* Raspberry Pi con conexión Wi-Fi
* Sensor DHT22 (temperatura y humedad)
* Sensor de humedad digital (GPIO)
* Sensor de luz LM393
* Fuente de alimentación estable

### Software

* Raspberry Pi OS Lite 
* Python 3 con:

  * `psycopg2-binary`
  * `adafruit-circuitpython-dht`
  * `RPi.GPIO`
* Flask, Bootstrap, Chart.js
* Docker + Terraform
* AWS EC2 + TimescaleDB
* Telegram Bot API


## 📅 Instalación en Raspberry Pi

### 1. Instalar Raspberry Pi OS

Descargar desde: [https://www.raspberrypi.com/software/](https://www.raspberrypi.com/software/)

> Se recomienda la versión **Lite de 32 bits** por compatibilidad con algunas bibliotecas GPIO.

### 2. Instalar dependencias base

```bash
sudo apt update
sudo apt install -y python3-pip python3-venv libgpiod2 libffi-dev build-essential libpq-dev
```

### 3. Crear y activar entorno virtual

```bash
python3 -m venv ~/venv
source ~/venv/bin/activate
```

### 4. Instalar bibliotecas Python necesarias

```bash
pip install psycopg2-binary adafruit-circuitpython-dht RPi.GPIO
```

### 5. Configurar crontab para ejecución automática cada 5 minutos

```bash
crontab -e
```

Agregar esta línea al final (ajusta \[usuario] por tu nombre real):

```bash
*/5 * * * * /bin/bash -c 'source /home/[usuario]/venv/bin/activate && python /home/[usuario]/script/carga.py >> /home/[usuario]/script/log.txt 2>&1'
```

---

## 🪜 Infraestructura como Código con Terraform

Este repositorio está dividido en módulos. Ejecuta cada uno en orden para desplegar toda la infraestructura:

### Estructura

```bash
terraform/
├── redes/          # Crea VPC, subred y tabla de rutas
├── instancias/
    ├── timescaledb/   # EC2 con TimescaleDB
    ├── ftp/           # Servidor de backups SFTP
    └── web/           # Interfaz web Flask
```

### Orden de ejecución

1. **Redes**

```bash
cd terraform/redes
terraform init
terraform apply
```

2. **Base de Datos (TimescaleDB)**

```bash
cd ../instancias/timescaledb
terraform init
terraform apply
```

3. **Servidor FTP**

```bash
cd ../ftp
terraform init
terraform apply
```

4. **Servidor Web**

```bash
cd ../web
terraform init
terraform apply
```

> ✅ Usa `terraform destroy` en cualquier módulo para eliminar recursos específicos.

---

## 📊 Visualización de Datos

Una vez desplegado el frontend, accede a la IP elástica asociada a la instancia EC2 del servidor web desde cualquier navegador:

```
http://[tu-ip-elastica]
```

---

## 🚀 Automatizaciones

* **Backup Diario:** El servidor TimescaleDB ejecuta un `cron` a las 02:00 AM que exporta la base de datos y la envía al servidor SFTP.
* **Notificaciones:** Un bot de Telegram alerta al usuario cuando se detectan valores fuera de los umbrales.

---

## 📤 Licencia

MIT License

---

## 📅 Autor

**Jesús Rodríguez**
Repositorio: [Monitorizame](https://github.com/jrodsan/Monitorizame)

