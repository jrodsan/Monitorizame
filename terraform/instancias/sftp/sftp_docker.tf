# VPC existente
data "aws_vpc" "infra" {
  filter {
    name   = "tag:Name"
    values = ["vpc-web"]
  }
}

# Subred existente
data "aws_subnet" "publica" {
  filter {
    name   = "tag:Name"
    values = ["subnet-publica"]
  }
}

# Grupo de seguridad para SSH y SFTP
resource "aws_security_group" "sg_sftp" {
  name        = "sftp-sg"
  description = "Permitir SSH y SFTP"
  vpc_id      = data.aws_vpc.infra.id

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 2022
    to_port     = 2022
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
    Name = "sftp-sg"
  }
}

# Clave EC2 para login SSH
resource "tls_private_key" "ec2_key" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "aws_key_pair" "ec2_key_pair" {
  key_name   = "ec2-access-key"
  public_key = tls_private_key.ec2_key.public_key_openssh
}

resource "local_file" "ec2_key_file" {
  filename        = "${path.module}/ec2-access-key.pem"
  content         = tls_private_key.ec2_key.private_key_pem
  file_permission = "0600"
}

# Clave SFTP para Docker
resource "tls_private_key" "sftp_key" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "local_file" "sftp_key_file" {
  filename        = "${path.module}/sftp-user-key.pem"
  content         = tls_private_key.sftp_key.private_key_pem
  file_permission = "0600"
}

# EC2 con Docker y contenedor SFTP
resource "aws_instance" "sftp_server" {
  ami                         = "ami-084568db4383264d4" # Ubuntu 22.04
  instance_type               = "t2.micro"
  subnet_id                   = data.aws_subnet.publica.id
  vpc_security_group_ids      = [aws_security_group.sg_sftp.id]
  key_name                    = aws_key_pair.ec2_key_pair.key_name
  associate_public_ip_address = true
  private_ip                  = "10.0.1.32"

    user_data = <<-EOF
     #!/bin/bash
      set -eux
      exec > /var/log/user-data.log 2>&1

      apt-get update -y
      apt-get install -y docker.io

      systemctl enable docker
      systemctl start docker

      # Crear estructura para volumen SFTP
      mkdir -p /sftp/admin/upload

      # Añadir clave pública directamente en authorized_keys
      mkdir -p /sftp/admin/.ssh
      echo "${tls_private_key.sftp_key.public_key_openssh}" > /sftp/admin/.ssh/authorized_keys

      # Asignar permisos correctos
      chown -R 1000:1000 /sftp/admin
      chmod 700 /sftp/admin/.ssh
      chmod 600 /sftp/admin/.ssh/authorized_keys

      # Ejecutar contenedor SFTP con soporte de clave pública
      docker run -d \
        --name sftp-server \
        -p 2022:22 \
        --restart unless-stopped \
        -v /sftp/admin:/home/admin \
        -e SFTP_USERS="admin:1000:1000" \
        -e PUBLIC_KEY_FILE=.ssh/authorized_keys \
        atmoz/sftp


        
    EOF



  tags = {
    Name = "sftp-server"
  }
}
