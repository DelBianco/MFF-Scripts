 DROP TABLE pessoaArtigo;
 DROP TABLE artigoARS;
 DROP TABLE pessoa;
 DROP TABLE periodico;

CREATE TABLE pessoa(
  idPessoa INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nomeCompleto VARCHAR (255),
  nomeCitacao VARCHAR (255)
);

CREATE TABLE periodico(
	idPeriodico INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR (255),
	ano SMALLINT
);

CREATE TABLE artigoARS(
  idArtigo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idPeriodico INT,
  
  foreign key(idPeriodico) references periodico(idPeriodico)
);

CREATE TABLE pessoaArtigo(
	idArtigo INT,
	idPessoa INT,

	PRIMARY KEY (idArtigo,idPessoa),
	foreign key(idPessoa) references pessoa(idPessoa),
	foreign key(idArtigo) references artigoARS(idArtigo)
);
