# VPC
resource "aws_vpc" "web" {
  cidr_block = "10.0.0.0/16"
  tags = {
    Name = "vpc-web"
  }
}

# Subred pública
resource "aws_subnet" "publica" {
  vpc_id                  = aws_vpc.web.id
  cidr_block              = "10.0.1.0/24"
  availability_zone       = "us-east-1a"
  map_public_ip_on_launch = true

  tags = {
    Name = "subnet-publica"
  }
}

# Internet Gateway
resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.web.id

  tags = {
    Name = "igw-web"
  }
}

# Tabla de ruteo pública
resource "aws_route_table" "publica" {
  vpc_id = aws_vpc.web.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.igw.id
  }

  tags = {
    Name = "rtb-publica"
  }
}

# Asociación de subred pública a la tabla de rutas
resource "aws_route_table_association" "asoc_publica" {
  subnet_id      = aws_subnet.publica.id
  route_table_id = aws_route_table.publica.id
}

# Grupo de seguridad web
resource "aws_security_group" "web_sg" {
  name        = "web-sg"
  description = "Permitir SSH, HTTP y HTTPS"
  vpc_id      = aws_vpc.web.id

  ingress {
    description = "SSH desde IP_publica"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"] # Sustituye esto
  }

  ingress {
    description = "HTTP"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "HTTPS"
    from_port   = 443
    to_port     = 443
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
    Name = "web-sg"
  }
}
