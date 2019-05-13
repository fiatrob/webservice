# Host: localhost  (Version 5.5.5-10.3.9-MariaDB)
# Date: 2019-04-15 17:35:31
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "usuario"
#

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpf` char(11) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` char(32) DEFAULT NULL,
  `dtnasc` date DEFAULT NULL,
  `stt` tinyint(1) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(15) DEFAULT NULL,
  `bairro` varchar(15) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `cep` int(8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#
# Data for table "usuario"
#

INSERT INTO `usuario` VALUES (1,'02037267752','Osmar Betazzi Dordal','osmarbd@gmail.com','05c334c9d6eba5a1290e60d4b19c0f76','1976-01-10',1,'M','Av. São José','3','bloco 3','Guararapes','Curitiba','PR',27350000);
