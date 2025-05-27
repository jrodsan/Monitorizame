# Referenciar la VPC y subred ya creadas
data "aws_vpc" "web" {
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

data "aws_security_group" "web_sg" {
  filter {
    name   = "tag:Name"
    values = ["web-sg"]
  }
}

# Crear clave SSH 
resource "tls_private_key" "web_key" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "aws_key_pair" "web_key_pair" {
  key_name   = "web-key"
  public_key = tls_private_key.web_key.public_key_openssh
}

resource "local_file" "web_key_pem" {
  filename      = "${path.module}/web-key.pem"
  content       = tls_private_key.web_key.private_key_pem
  file_permission = "0600"
}

# Instancia EC2 con Docker + Apache + PHP
resource "aws_instance" "web_server" {
  ami                    = "ami-084568db4383264d4" # AMI Ubuntu
  instance_type          = "t2.micro"
  subnet_id              = data.aws_subnet.publica.id
  vpc_security_group_ids = [data.aws_security_group.web_sg.id]
  key_name               = aws_key_pair.web_key_pair.key_name

user_data = <<-EOF
  #!/bin/bash
  set -eux
  exec > /var/log/user-data.log 2>&1

  apt update -y
  apt install -y docker.io

  systemctl enable docker
  systemctl start docker

  mkdir -p /home/ubuntu/web-content

  # Espera a que Terraform copie los archivos via provisioner "file"
  sleep 60

  cd /home/ubuntu/web-content
  docker build -t web-custom .
  docker run -d \
    --name web \
    --restart unless-stopped \
    -p 80:80 \
    -v /home/ubuntu/web-content:/var/www/html \
    web-custom
EOF


  provisioner "file" {
    source      = "${path.module}/web"              # Carpeta local completa
    destination = "/home/ubuntu/web-content"        # Carpeta compartida con Docker

    connection {
      type        = "ssh"
      host        = self.public_ip
      user        = "ubuntu"
      private_key = local_file.web_key_pem.content
    }
  }

  tags = {
    Name = "web-server"
  }
}