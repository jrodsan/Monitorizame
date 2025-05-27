provider "aws" {
  region = "us-east-1"
}

# Datos de VPC y subred existentes
data "aws_vpc" "infra" {
  filter {
    name   = "tag:Name"
    values = ["vpc-web"]
  }
}

data "aws_subnet" "publica" {
  filter {
    name   = "tag:Name"
    values = ["subnet-publica"]
  }
}

# Grupo de seguridad para permitir SSH y PostgreSQL
resource "aws_security_group" "timescaledb_sg" {
  name        = "timescaledb-sg"
  description = "Permitir acceso SSH y PostgreSQL"
  vpc_id      = data.aws_vpc.infra.id

  ingress {
    description = "SSH"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "PostgreSQL"
    from_port   = 5432
    to_port     = 5432
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "timescaledb-sg"
  }
}

# Generar clave SSH
resource "tls_private_key" "timescaledb_key" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "aws_key_pair" "timescaledb_key_pair" {
  key_name   = "timescaledb-key"
  public_key = tls_private_key.timescaledb_key.public_key_openssh
}

resource "local_file" "timescaledb_private_key_file" {
  filename        = "${path.module}/timescaledb-key.pem"
  content         = tls_private_key.timescaledb_key.private_key_pem
  file_permission = "0600"
}

# Instancia EC2 con Docker y TimescaleDB
resource "aws_instance" "timescaledb_server" {
  ami                         = "ami-084568db4383264d4" # Ubuntu 22.04 LTS
  instance_type               = "t2.micro"
  subnet_id                   = data.aws_subnet.publica.id
  vpc_security_group_ids      = [aws_security_group.timescaledb_sg.id]
  key_name                    = aws_key_pair.timescaledb_key_pair.key_name
  associate_public_ip_address = true

  # Arranca el contenedor con Docker y deja init.sql copiado
  user_data = <<-EOF
    #!/bin/bash
    set -eux
    exec > /var/log/user-data.log 2>&1

    # Instalar Docker
    apt-get update -y
    apt-get install -y docker.io

    systemctl enable docker
    systemctl start docker

    # Descargar e iniciar TimescaleDB
    docker run -d \
      --name timescaledb \
      -e POSTGRES_USER=admin \
      -e POSTGRES_PASSWORD=admin \
      -e POSTGRES_DB=sensordata \
      -p 5432:5432 \
      --restart unless-stopped \
      timescale/timescaledb:latest-pg14

    # Esperar a que el contenedor arranque
    sleep 20

    # Ejecutar script SQL si existe
    if [ -f /home/ubuntu/init.sql ]; then
      docker exec -i timescaledb psql -U admin -d sensordata < /home/ubuntu/init.sql || true
    fi
  EOF

  # Copiar el archivo init.sql desde la máquina local
  provisioner "file" {
    source      = "init.sql"
    destination = "/home/ubuntu/init.sql"

    connection {
      type        = "ssh"
      host        = self.public_ip
      user        = "ubuntu"
      private_key = local_file.timescaledb_private_key_file.content
    }
  }

  tags = {
    Name = "timescaledb-server"
  }
}
