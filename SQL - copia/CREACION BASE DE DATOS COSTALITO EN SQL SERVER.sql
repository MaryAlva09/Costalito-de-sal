CREATE TABLE usuarios 
(
    id INT IDENTITY(300, 1) PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    correo VARCHAR(50) UNIQUE,  -- Correo único para autenticación
    password VARCHAR(255)       -- Contraseña del usuario (se recomienda encriptarla)
);

CREATE TABLE subscripciones
(
    id INT,
    sub VARCHAR(50),
    estatus BIT,
    PRIMARY KEY (id, sub),
    UNIQUE (estatus),  -- Restricción UNIQUE para permitir referencias en claves foráneas
    FOREIGN KEY (id) REFERENCES usuarios (id)
);

CREATE TABLE contenido
(
    id INT IDENTITY(100, 1) PRIMARY KEY,
    titulo VARCHAR(75),
    categoria VARCHAR(50)
);

CREATE TABLE patrocinadores
(
    id INT IDENTITY(200, 1) PRIMARY KEY,
    nombre VARCHAR(75),
    giro VARCHAR(50)
);

CREATE TABLE planes
(
    sub VARCHAR(50) PRIMARY KEY,
    costo FLOAT
);

CREATE TABLE contenido_user
(
    usuario_id INT,
    sub_status BIT,
    show_cont BIT,
    PRIMARY KEY (usuario_id, sub_status),
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    FOREIGN KEY (sub_status) REFERENCES subscripciones (estatus)
);

INSERT INTO usuarios (nombre, apellido, correo, password)
VALUES ('Kevin', 'Valero', 'eduardo-cofe@hotmail.com', '12345678a');

INSERT INTO usuarios (nombre, apellido, correo, password)
VALUES ('Mariana', 'Alvarez', 'mariana.alvareze@alumno.buap.mx', '12345678a');

SELECT * from usuarios