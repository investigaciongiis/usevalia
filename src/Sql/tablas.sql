-- Categorías web
CREATE TABLE usevalia__categoria_web (
	id INT UNSIGNED AUTO_INCREMENT,
	nombre VARCHAR(255) NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO usevalia__categoria_web(nombre) VALUES
	('Buscadores/Portales'),
	('Blogs/Informativas'),
	('Foros/Interacción'),
	('Redes sociales'),
	('Transaccionales'),
	('Comercio electrónico'),
	('Correo electrónico'),
	('Entretenimiento'),
	('Académico'),
    ('Colaborativas'),
	('Descargas'),
	('Corporativa/Entidades públicas'),
	('Servicios');


-- Tareas
CREATE TABLE usevalia__tarea (
	id INT UNSIGNED AUTO_INCREMENT,
	nombre VARCHAR(255) NOT NULL,
	categoria INT UNSIGNED NOT NULL,
	PRIMARY KEY (id),
    FOREIGN KEY (categoria)
        REFERENCES usevalia__categoria_web(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Aplicacion
CREATE TABLE usevalia__aplicacion (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    url TEXT,
    descripcion TEXT,
    categoria INT UNSIGNED NOT NULL,
	PRIMARY KEY (id),
    FOREIGN KEY (categoria)
        REFERENCES usevalia__categoria_web(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Puntuacion
CREATE TABLE usevalia__esquema_puntuacion (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    PRIMARY KEY (id)
);

CREATE TABLE usevalia__valor_puntuacion (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    escala INT UNSIGNED NOT NULL,
    tipo VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (escala)
        REFERENCES usevalia__esquema_puntuacion(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Grupo de usuarios
CREATE TABLE usevalia__grupo_auditores (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    PRIMARY KEY (id)
);

CREATE TABLE usevalia__grupo_auditores_usuarios (
    usuario INT UNSIGNED NOT NULL,
    grupo INT UNSIGNED NOT NULL,
    PRIMARY KEY (usuario, grupo),
    FOREIGN KEY (usuario)
        REFERENCES users(uid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (grupo)
        REFERENCES usevalia__grupo_auditores(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Tags
CREATE TABLE usevalia__etiqueta (
    id INT UNSIGNED AUTO_INCREMENT,
    valor VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE usevalia__grupo_auditores_etiqueta (
    grupo INT UNSIGNED NOT NULL,
    etiqueta INT UNSIGNED NOT NULL,
    PRIMARY KEY (grupo, etiqueta),
    FOREIGN KEY (grupo)
        REFERENCES usevalia__grupo_auditores(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (etiqueta)
        REFERENCES usevalia__etiqueta(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Catalogo
CREATE TABLE usevalia__catalogo (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    esquema INT UNSIGNED NOT NULL,
    autor INT UNSIGNED NOT NULL,
    grupo INT UNSIGNED NOT NULL,
    lectura ENUM('PUBLICO','GRUPO','PRIVADO') NOT NULL,
    escritura ENUM('PUBLICO','GRUPO','PRIVADO') NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (esquema)
        REFERENCES usevalia__esquema_puntuacion(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (autor)
        REFERENCES users(uid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (grupo)
        REFERENCES usevalia__grupo_auditores(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Prioridades
CREATE TABLE usevalia__prioridad (
	id INT UNSIGNED AUTO_INCREMENT,
	nombre VARCHAR(255) NOT NULL,
	catalogo INT UNSIGNED NOT NULL,
	peso INT UNSIGNED NOT NULL,
	fallos INT UNSIGNED NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (catalogo)
        REFERENCES usevalia__catalogo(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Grupo de directrices
CREATE TABLE usevalia__grupo_directrices (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    catalogo INT UNSIGNED NOT NULL,
    esquema INT UNSIGNED,
    PRIMARY KEY (id),
    FOREIGN KEY (esquema)
        REFERENCES usevalia__esquema_puntuacion(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (catalogo)
        REFERENCES usevalia__catalogo(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Directrices
CREATE TABLE usevalia__directriz (
    iid INT UNSIGNED AUTO_INCREMENT,
    eid VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    peso INT DEFAULT 0,
    padre INT UNSIGNED,
    grupo INT UNSIGNED NOT NULL,
    esquema INT UNSIGNED,
    PRIMARY KEY (iid),
    FOREIGN KEY (padre)
        REFERENCES usevalia__directriz(iid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (esquema)
        REFERENCES usevalia__esquema_puntuacion(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (grupo)
        REFERENCES usevalia__grupo_directrices(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Fuente
CREATE TABLE usevalia__fuente (
    iid INT UNSIGNED AUTO_INCREMENT,
    eid VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    url TEXT,
    PRIMARY KEY (iid)
);

CREATE TABLE usevalia__directriz_fuente (
    directriz INT UNSIGNED NOT NULL,
    fuente INT UNSIGNED NOT NULL,
    PRIMARY KEY (directriz, fuente),
    FOREIGN KEY (directriz)
        REFERENCES usevalia__directriz(iid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (fuente)
        REFERENCES usevalia__fuente(iid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Auditoria
CREATE TABLE usevalia__auditoria (
    id INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin_estimada DATE NOT NULL,
    fecha_fin_real DATE,
    aplicacion INT UNSIGNED NOT NULL,
    administrador INT UNSIGNED NOT NULL,
    catalogo INT UNSIGNED NOT NULL,
    evaluacion VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (aplicacion)
        REFERENCES usevalia__aplicacion(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (administrador)
        REFERENCES users(uid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (catalogo)
        REFERENCES usevalia__catalogo(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE usevalia__auditoria_usuarios (
    usuario INT UNSIGNED NOT NULL,
    auditoria INT UNSIGNED NOT NULL,
    PRIMARY KEY (usuario, auditoria),
    FOREIGN KEY (usuario)
        REFERENCES users(uid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (auditoria)
        REFERENCES usevalia__auditoria(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Puntuacion
CREATE TABLE usevalia__puntuacion (
    id INT UNSIGNED AUTO_INCREMENT,
    usuario INT UNSIGNED NOT NULL,
    auditoria INT UNSIGNED NOT NULL,
    directriz INT UNSIGNED NOT NULL,
    puntuacion VARCHAR(255) NOT NULL,
    observacion TEXT,
    mejora TEXT,
    tarea INT UNSIGNED,
    PRIMARY KEY (id),
    FOREIGN KEY (usuario)
        REFERENCES users(uid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (auditoria)
        REFERENCES usevalia__auditoria(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (directriz)
        REFERENCES usevalia__directriz(iid)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (tarea)
        REFERENCES usevalia__tarea(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Insertar tareas
INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 1),
	('Iniciar sesión', 1),
	('Búsqueda con palabras', 1),
	('Búsqueda con imágenes', 1),
	('Búsqueda en diferentes idiomas', 1),
	('Filtrar resultados', 1),
	('Cambiar idioma', 1),
	('Búsqueda predictiva', 1),
	('Sugerencias de búsqueda', 1);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 2),
	('Iniciar sesión', 2),
	('Búsqueda con palabras', 2),
	('Ver últimas novedades/noticias', 2),
	('Compartir contenido', 2),
	('Comentar contenido', 2),
	('Ver etiquetas', 2),
	('Valorar contenido', 2);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 3),
	('Iniciar sesión', 3),
	('Búsqueda con palabras', 3),
	('Ver publicaciones populares', 3),
	('Ver últimas publicaciones', 3),
	('Publicar tema', 3),
	('Citar un tema/publicación', 3),
	('Responder tema', 3),
	('Ver perfil de un usuario', 3),
	('Ver mensajes de un usuario', 3);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 4),
	('Iniciar sesión', 4),
	('Buscar contenido', 4),
	('Publicar contenido', 4),
	('Compartir contenido', 4),
	('Enviar mensajes privados', 4),
	('Ver perfil de un usuario', 4),
	('Editar mi perfil', 4),
	('Ver eitquetas', 4);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 5),
	('Iniciar sesión', 5),
	('Realizar transferencia', 5),
	('Ver recibos', 5),
	('Ver saldo', 5),
	('Ver mis cuentas bancarias', 5),
	('Ver mis tarjetas', 5),
	('Solicitar préstamo', 5),
	('Ver información de una cuenta', 5);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 6),
	('Iniciar sesión', 6),
	('Búsqueda con palabras', 6),
	('Buscar por categoría', 6),
	('Ver información producto', 6),
	('Valorar producto', 6),
	('Realizar pedido', 6),
	('Cancelar pedido', 6),
	('Realizar reclamación', 6),
	('Añadir producto a lista de deseos', 6);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
    ('Registrarse', 7),
    ('Iniciar sesión', 7),
    ('Redactar correo', 7),
    ('Filtrar correos', 7),
    ('Eliminar correo', 7),
    ('Crear carpetas', 7),
    ('Responder correo', 7),
    ('Bloquear emisor', 7),
    ('Búsqueda por palabras', 7);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 8),
	('Iniciar sesión', 8),
	('Búsqueda con palabras', 8),
	('Buscar por categoría', 8),
	('Ver contenido/juego', 8),
	('Añadir a favoritos', 8),
	('Valorar contenido/juego', 8),
	('Ver últimas novedades', 8),
	('Ver sugerencias', 8);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Iniciar sesión', 9),
	('Ver anuncios', 9),
	('Ver tareas', 9),
	('Descargar material', 9),
	('Contactar con un profesor', 9),
	('Ver notas de exámenes', 9),
	('Filtrar asignaturas', 9),
	('Ver planificación/calendario asignatura', 9);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 10),
	('Iniciar sesión', 10),
	('Búsqueda por palabras', 10),
	('Ver recientes', 10),
	('Crear nuevo documento', 10),
	('Filtrar documentos', 10),
	('Descargar un documento', 10),
	('Añadir participantes', 10),
	('Comunicarse con los participantes', 10);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 11),
	('Iniciar sesión', 11),
	('Búsqueda por palabras', 11),
	('Filtrar descargas', 11),
	('Buscar por categoría', 11),
	('Descargar contenido', 11),
	('Compartir descarga', 11),
	('Valorar descarga', 11),
	('Ver descripción descarga', 11);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Ver información empresa', 12),
	('Ver tarifas', 12),
	('Ver servicios proporcionados', 12),
	('Ver información de contacto', 12),
	('Ver enlaces a redes sociales', 12),
	('Cambiar idioma de la página', 12);

INSERT INTO usevalia__tarea(nombre, categoria) VALUES
	('Registrarse', 13),
	('Iniciar sesión', 13),
	('Usar servicio', 13),
	('Ver ayuda', 13),
	('Ver información del servicio', 13),
	('Cambiar idioma de la página', 13);
