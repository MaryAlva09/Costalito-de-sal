CREATE DATABASE Costalito_de_sal;
USE Costalito_de_sal;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    correo VARCHAR(100) UNIQUE,  -- Correo único para autenticación
    password VARCHAR(255)        -- Contraseña del usuario (se recomienda encriptarla)
);

CREATE TABLE subscripciones (
    id INT,
    sub VARCHAR(100),
    estatus TINYINT(1),          -- Usamos TINYINT(1) para representar BOOLEAN
    PRIMARY KEY (id, sub),
    INDEX (estatus),             -- Índice para la columna 'estatus'
    FOREIGN KEY (id) REFERENCES usuarios (id)
);

CREATE TABLE contenido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100),
    categoria VARCHAR(100)
);

CREATE TABLE patrocinadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    giro VARCHAR(100)
);

CREATE TABLE planes (
    sub VARCHAR(100) PRIMARY KEY,
    costo FLOAT
);

CREATE TABLE contenido_user (
    usuario_id INT,
    sub_status TINYINT(1),
    show_cont TINYINT(1),
    PRIMARY KEY (usuario_id, sub_status),
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    FOREIGN KEY (sub_status) REFERENCES subscripciones (estatus)
);

-- Inserciones en la tabla 'usuarios'
INSERT INTO usuarios (nombre, apellido, correo, password)
VALUES ('Kevin', 'Valero', 'eduardo-cofe@hotmail.com', '12345678a');

INSERT INTO usuarios (nombre, apellido, correo, password)
VALUES ('Mariana', 'Alvarez', 'mariana.alvareze@alumno.buap.mx', '12345678a');


-- Inserciones en la tabla 'contenido'
INSERT INTO contenido (titulo, categoria)
VALUES ('La razón de estar contigo', 'Novela');

-- Inserciones en la tabla 'patrocinadores'
INSERT INTO patrocinadores (nombre, giro)
VALUES ('Poliworks', 'Tecnología');

INSERT INTO patrocinadores (nombre, giro)
VALUES ('Banamex', 'Finanzas');

INSERT INTO patrocinadores (nombre, giro)
VALUES ('Nat Geo Education', 'Educación');

-- Inserciones en la tabla 'planes'
INSERT INTO planes (sub, costo)
VALUES ('Premium', 19.99);

INSERT INTO planes (sub, costo)
VALUES ('Free', 0.00);

