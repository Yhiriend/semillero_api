-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-05-2025 a las 17:58:30
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_semillero_v3`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizarUsuario` (IN `p_usuario_id` INT, IN `p_nombre` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_tipo` ENUM('estudiante','profesor','administrador'), IN `p_contraseña` VARCHAR(255), IN `p_programa_id` INT, IN `p_roles` VARCHAR(255))   BEGIN
    DECLARE v_rol_nombre VARCHAR(50);
    DECLARE v_rol_id INT;
    DECLARE v_programa_exists INT;
    DECLARE v_universidad_id INT;
    DECLARE v_universidad_cordoba_id INT;
    DECLARE v_es_administrador INT;
    DECLARE v_pos INT DEFAULT 1;
    DECLARE v_comma_pos INT;
    DECLARE v_roles_str VARCHAR(255);

    -- Manejo de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al actualizar el usuario. Operación revertida.';
    END;

    START TRANSACTION;

    -- Validar que el usuario existe
    IF NOT EXISTS (SELECT 1 FROM `Usuario` WHERE id = p_usuario_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario especificado no existe.';
    END IF;

    -- Validar que el email no esté en uso por otro usuario
    IF EXISTS (SELECT 1 FROM `Usuario` WHERE email = p_email AND id != p_usuario_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El email ya está registrado por otro usuario.';
    END IF;

    -- Validar si el usuario es administrador
    SELECT COUNT(*) INTO v_es_administrador
    FROM `Usuario_Rol`
    WHERE usuario_id = p_usuario_id AND rol_id = 6;

    IF v_es_administrador > 0 AND p_tipo != 'administrador' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede cambiar el tipo de un usuario administrador.';
    END IF;

    -- Validar programa si se proporciona
    IF p_programa_id IS NOT NULL THEN
        SELECT COUNT(*) INTO v_programa_exists
        FROM `Programa`
        WHERE id = p_programa_id;

        IF v_programa_exists = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El programa especificado no existe.';
        END IF;

        -- Validar que el programa pertenece a una universidad válida
        SELECT f.universidad_id INTO v_universidad_id
        FROM `Programa` p
        JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE p.id = p_programa_id;

        SELECT id INTO v_universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';

        IF v_universidad_id != v_universidad_cordoba_id THEN
            -- Verificar si el tipo o roles requieren pertenecer a la Universidad de Córdoba
            IF p_tipo IN ('profesor', 'administrador') OR 
               FIND_IN_SET('Coordinador de Semillero', p_roles) OR 
               FIND_IN_SET('Coordinador de Proyecto', p_roles) OR 
               FIND_IN_SET('Coordinador de Eventos', p_roles) OR 
               FIND_IN_SET('Lider de Proyecto', p_roles) THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Usuarios con roles de coordinador, líder o tipo profesor/administrador deben pertenecer a la Universidad de Córdoba.';
            END IF;
        END IF;
    ELSE
        -- Si no hay programa, validar que no se asignen roles restringidos
        IF FIND_IN_SET('Coordinador de Semillero', p_roles) OR 
           FIND_IN_SET('Coordinador de Proyecto', p_roles) OR 
           FIND_IN_SET('Coordinador de Eventos', p_roles) OR 
           FIND_IN_SET('Lider de Proyecto', p_roles) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Usuarios sin programa no pueden tener roles de coordinador o líder.';
        END IF;
    END IF;

    -- Actualizar los datos del usuario
    UPDATE `Usuario`
    SET nombre = p_nombre,
        email = p_email,
        tipo = p_tipo,
        contraseña = p_contraseña,
        programa_id = p_programa_id
    WHERE id = p_usuario_id;

    -- Eliminar roles existentes
    DELETE FROM `Usuario_Rol`
    WHERE usuario_id = p_usuario_id;

    -- Procesar nuevos roles
    SET v_roles_str = p_roles;
    WHILE v_pos <= LENGTH(v_roles_str) DO
        SET v_comma_pos = LOCATE(',', v_roles_str, v_pos);
        IF v_comma_pos = 0 THEN
            SET v_comma_pos = LENGTH(v_roles_str) + 1;
        END IF;

        -- Extraer el nombre del rol
        SET v_rol_nombre = TRIM(SUBSTRING(v_roles_str, v_pos, v_comma_pos - v_pos));
        SET v_pos = v_comma_pos + 1;

        IF v_rol_nombre != '' THEN
            -- Obtener el rol_id usando la función auxiliar
            SET v_rol_id = `ObtenerRolId`(v_rol_nombre);

            -- Insertar el rol en Usuario_Rol
            INSERT INTO `Usuario_Rol` (usuario_id, rol_id)
            VALUES (p_usuario_id, v_rol_id);
        END IF;
    END WHILE;

    COMMIT;

    -- Devolver el ID del usuario actualizado
    SELECT p_usuario_id AS usuario_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AgregarUsuarioASemillero` (IN `p_usuario_id` INT, IN `p_semillero_id` INT)   BEGIN
    INSERT INTO `Semillero_Usuario` (usuario_id, semillero_id, fecha_inscripcion)
    VALUES (p_usuario_id, p_semillero_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AsignarProyectoAUsuario` (IN `p_usuario_id` INT, IN `p_proyecto_id` INT)   BEGIN
    INSERT INTO `Proyecto_Usuario` (usuario_id, proyecto_id, fecha_asignacion)
    VALUES (p_usuario_id, p_proyecto_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CrearEventoCompleto` (IN `p_nombre_evento` VARCHAR(200), IN `p_descripcion_evento` TEXT, IN `p_coordinador_id` INT, IN `p_fecha_inicio` DATETIME, IN `p_fecha_fin` DATETIME, IN `p_ubicacion` VARCHAR(200), IN `p_actividades` JSON)   BEGIN
    DECLARE v_evento_id INT;
    DECLARE v_actividad_count INT;
    DECLARE v_i INT DEFAULT 0;
    DECLARE v_titulo_act VARCHAR(200);
    DECLARE v_desc_act TEXT;
    DECLARE v_fecha_ini_act DATETIME;
    DECLARE v_fecha_fin_act DATETIME;
    DECLARE v_responsables JSON;
    DECLARE v_j INT;
    DECLARE v_responsable_id INT;
    DECLARE v_actividad_id INT;
    DECLARE universidad_cordoba_id INT;
    DECLARE usuario_universidad INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    SELECT id INTO universidad_cordoba_id FROM `Universidad` WHERE nombre = 'Universidad de Cordoba';
    
    IF p_coordinador_id IS NOT NULL THEN
        SELECT f.universidad_id INTO usuario_universidad
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = p_coordinador_id;
        
        IF usuario_universidad IS NULL OR usuario_universidad != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador del evento debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
    
    INSERT INTO `Evento` (nombre, descripcion, coordinador_id, fecha_inicio, fecha_fin, ubicacion)
    VALUES (p_nombre_evento, p_descripcion_evento, p_coordinador_id, p_fecha_inicio, p_fecha_fin, p_ubicacion);
    
    SET v_evento_id = LAST_INSERT_ID();
    
    SET v_actividad_count = JSON_LENGTH(p_actividades);
    
    WHILE v_i < v_actividad_count DO
        SET v_titulo_act = JSON_UNQUOTE(JSON_EXTRACT(p_actividades, CONCAT('$[', v_i, '].titulo')));
        SET v_desc_act = JSON_UNQUOTE(JSON_EXTRACT(p_actividades, CONCAT('$[', v_i, '].descripcion')));
        SET v_fecha_ini_act = JSON_UNQUOTE(JSON_EXTRACT(p_actividades, CONCAT('$[', v_i, '].fecha_inicio')));
        SET v_fecha_fin_act = JSON_UNQUOTE(JSON_EXTRACT(p_actividades, CONCAT('$[', v_i, '].fecha_fin')));
        SET v_responsables = JSON_EXTRACT(p_actividades, CONCAT('$[', v_i, '].responsables'));
        
        INSERT INTO `Actividad` (titulo, descripcion, evento_id, fecha_inicio, fecha_fin, estado)
        VALUES (v_titulo_act, v_desc_act, v_evento_id, v_fecha_ini_act, v_fecha_fin_act, 'pendiente');
        
        SET v_actividad_id = LAST_INSERT_ID();
        
        SET v_j = 0;
        WHILE v_j < JSON_LENGTH(v_responsables) DO
            SET v_responsable_id = JSON_EXTRACT(v_responsables, CONCAT('$[', v_j, ']'));
            
            INSERT INTO `Actividad_Responsable` (actividad_id, responsable_id)
            VALUES (v_actividad_id, v_responsable_id);
            
            SET v_j = v_j + 1;
        END WHILE;
        
        SET v_i = v_i + 1;
    END WHILE;
    
    COMMIT;
    
    SELECT v_evento_id AS id_evento_creado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CrearProyecto` (IN `p_titulo` VARCHAR(200), IN `p_descripcion` TEXT, IN `p_semillero_id` INT, IN `p_lider_id` INT, IN `p_coordinador_id` INT, IN `p_fecha_inicio` DATE, IN `p_usuarios` VARCHAR(255))   BEGIN
    DECLARE v_proyecto_id INT;
    DECLARE v_usuario_id INT;
    DECLARE v_pos INT DEFAULT 1;
    DECLARE v_comma_pos INT;
    DECLARE v_usuarios_str VARCHAR(255);

    -- Manejo de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al crear el proyecto.';
    END;

    START TRANSACTION;

    -- Validar semillero
    IF NOT EXISTS (SELECT 1 FROM `Semillero` WHERE id = p_semillero_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El semillero especificado no existe.';
    END IF;

    -- Insertar proyecto
    INSERT INTO `Proyecto` (titulo, descripcion, semillero_id, lider_id, coordinador_id, estado, fecha_inicio)
    VALUES (p_titulo, p_descripcion, p_semillero_id, p_lider_id, p_coordinador_id, 'activo', p_fecha_inicio);

    SET v_proyecto_id = LAST_INSERT_ID();

    -- Asignar usuarios
    SET v_usuarios_str = p_usuarios;
    WHILE v_pos <= LENGTH(v_usuarios_str) DO
        SET v_comma_pos = LOCATE(',', v_usuarios_str, v_pos);
        IF v_comma_pos = 0 THEN
            SET v_comma_pos = LENGTH(v_usuarios_str) + 1;
        END IF;

        SET v_usuario_id = TRIM(SUBSTRING(v_usuarios_str, v_pos, v_comma_pos - v_pos));
        SET v_pos = v_comma_pos + 1;

        IF v_usuario_id != '' THEN
            CALL `AsignarProyectoAUsuario`(v_usuario_id, v_proyecto_id);
        END IF;
    END WHILE;

    COMMIT;

    SELECT v_proyecto_id AS proyecto_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CrearSemillero` (IN `p_nombre` VARCHAR(100), IN `p_descripcion` TEXT, IN `p_coordinador_id` INT, IN `p_programa_id` INT)   BEGIN
    DECLARE coordinador_valido INT;
    DECLARE programa_exists INT;
    
    SELECT COUNT(*) INTO programa_exists
    FROM `Programa`
    WHERE id = p_programa_id;
    
    IF programa_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El programa especificado no existe.';
    END IF;
    
    IF p_coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO coordinador_valido
        FROM `Usuario` u
        JOIN `Usuario_Rol` ur ON u.id = ur.usuario_id
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = p_coordinador_id
          AND u.tipo = 'profesor'
          AND ur.rol_id = 3
          AND f.universidad_id = (SELECT id FROM `Universidad` WHERE nombre = 'Universidad de Cordoba');
        
        IF coordinador_valido = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe ser un profesor de la Universidad de Córdoba con rol de Coordinador de Semillero.';
        END IF;
    END IF;
    
    INSERT INTO `Semillero` (nombre, descripcion, coordinador_id, programa_id)
    VALUES (p_nombre, p_descripcion, p_coordinador_id, p_programa_id);
    
    SELECT LAST_INSERT_ID() AS semillero_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CrearUsuario` (IN `p_nombre` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_tipo` ENUM('estudiante','profesor','administrador'), IN `p_contraseña` VARCHAR(255), IN `p_programa_id` INT, IN `p_roles` VARCHAR(255))   BEGIN
    DECLARE v_usuario_id INT;
    DECLARE v_rol_nombre VARCHAR(50);
    DECLARE v_rol_id INT;
    DECLARE v_programa_exists INT;
    DECLARE v_universidad_id INT;
    DECLARE v_universidad_cordoba_id INT;
    DECLARE v_pos INT DEFAULT 1;
    DECLARE v_comma_pos INT;
    DECLARE v_roles_str VARCHAR(255);


    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al crear el usuario. Operación revertida.';
    END;

    START TRANSACTION;

    IF EXISTS (SELECT 1 FROM `Usuario` WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El email ya está registrado.';
    END IF;

    IF p_programa_id IS NOT NULL THEN
        SELECT COUNT(*) INTO v_programa_exists
        FROM `Programa`
        WHERE id = p_programa_id;

        IF v_programa_exists = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El programa especificado no existe.';
        END IF;


        SELECT f.universidad_id INTO v_universidad_id
        FROM `Programa` p
        JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE p.id = p_programa_id;

        SELECT id INTO v_universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';

        IF v_universidad_id != v_universidad_cordoba_id THEN
            IF p_tipo IN ('profesor', 'administrador') OR 
               FIND_IN_SET('Coordinador de Semillero', p_roles) OR 
               FIND_IN_SET('Coordinador de Proyecto', p_roles) OR 
               FIND_IN_SET('Coordinador de Eventos', p_roles) OR 
               FIND_IN_SET('Lider de Proyecto', p_roles) THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Usuarios con roles de coordinador, líder o tipo profesor/administrador deben pertenecer a la Universidad de Córdoba.';
            END IF;
        END IF;
    ELSE
        IF FIND_IN_SET('Coordinador de Semillero', p_roles) OR 
           FIND_IN_SET('Coordinador de Proyecto', p_roles) OR 
           FIND_IN_SET('Coordinador de Eventos', p_roles) OR 
           FIND_IN_SET('Lider de Proyecto', p_roles) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Usuarios sin programa no pueden tener roles de coordinador o líder.';
        END IF;
    END IF;

    INSERT INTO `Usuario` (nombre, email, tipo, contraseña, programa_id)
    VALUES (p_nombre, p_email, p_tipo, p_contraseña, p_programa_id);

    SET v_usuario_id = LAST_INSERT_ID();

    SET v_roles_str = p_roles;
    WHILE v_pos <= LENGTH(v_roles_str) DO
        SET v_comma_pos = LOCATE(',', v_roles_str, v_pos);
        IF v_comma_pos = 0 THEN
            SET v_comma_pos = LENGTH(v_roles_str) + 1;
        END IF;

        SET v_rol_nombre = TRIM(SUBSTRING(v_roles_str, v_pos, v_comma_pos - v_pos));
        SET v_pos = v_comma_pos + 1;

        IF v_rol_nombre != '' THEN
            SET v_rol_id = `ObtenerRolId`(v_rol_nombre);

            INSERT INTO `Usuario_Rol` (usuario_id, rol_id)
            VALUES (v_usuario_id, v_rol_id);
        END IF;
    END WHILE;

    COMMIT;

    SELECT v_usuario_id AS usuario_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `EliminarUsuarioDeSemillero` (IN `p_usuario_id` INT, IN `p_semillero_id` INT)   BEGIN
    -- Manejo de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al eliminar usuario del semillero.';
    END;

    START TRANSACTION;

    -- Validar que el usuario está inscrito
    IF NOT EXISTS (SELECT 1 FROM `Semillero_Usuario` WHERE usuario_id = p_usuario_id AND semillero_id = p_semillero_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario no está inscrito en el semillero.';
    END IF;

    DELETE FROM `Semillero_Usuario`
    WHERE usuario_id = p_usuario_id AND semillero_id = p_semillero_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `EliminarUsuarios` (IN `p_usuario_id` INT)   BEGIN
    DECLARE es_administrador INT;
    
    SELECT COUNT(*) INTO es_administrador 
    FROM `Usuario_Rol` 
    WHERE usuario_id = p_usuario_id AND rol_id = 6;
    
    IF es_administrador > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se pueden eliminar usuarios con rol de administrador';
    ELSE
        DELETE FROM `Semillero_Usuario` WHERE usuario_id = p_usuario_id;
        DELETE FROM `Proyecto_Usuario` WHERE usuario_id = p_usuario_id;
        DELETE FROM `Usuario_Rol` WHERE usuario_id = p_usuario_id;
        DELETE FROM `Actividad_Responsable` WHERE responsable_id = p_usuario_id;
        DELETE FROM `Evaluacion` WHERE evaluador_id = p_usuario_id;
        
        UPDATE `Semillero` SET coordinador_id = NULL WHERE coordinador_id = p_usuario_id;
        UPDATE `Proyecto` SET coordinador_id = NULL WHERE coordinador_id = p_usuario_id;
        UPDATE `Proyecto` SET lider_id = NULL WHERE lider_id = p_usuario_id;
        UPDATE `Evento` SET coordinador_id = NULL WHERE coordinador_id = p_usuario_id;
        
        DELETE FROM `Usuario` WHERE id = p_usuario_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ListarUsuariosPorTipo` (IN `p_tipo` VARCHAR(50))   BEGIN
    SELECT 
        u.id,
        u.nombre,
        u.tipo,
        r.nombre AS rol,
        COALESCE(uni.nombre, 'Sin universidad') AS universidad,
        COALESCE(f.nombre, 'Sin facultad') AS facultad,
        COALESCE(p.nombre, 'Sin programa') AS programa
    FROM `Usuario` u
    INNER JOIN `Usuario_Rol` ur ON u.id = ur.usuario_id
    INNER JOIN `Rol` r ON ur.rol_id = r.id
    LEFT JOIN `Programa` p ON u.programa_id = p.id
    LEFT JOIN `Facultad` f ON p.facultad_id = f.id
    LEFT JOIN `Universidad` uni ON f.universidad_id = uni.id
    WHERE u.tipo = p_tipo
    ORDER BY u.id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerActividadesPorUsuario` (IN `p_usuario_id` INT)   BEGIN
    SELECT a.*
    FROM `Actividad` a
    JOIN `Actividad_Responsable` ar ON a.id = ar.actividad_id
    WHERE ar.responsable_id = p_usuario_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerProyectosPorSemillero` (IN `p_semillero_nombre` VARCHAR(100))   BEGIN
    DECLARE v_semillero_id INT;
    DECLARE v_semillero_count INT;

    -- Manejo de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al obtener proyectos del semillero.';
    END;

    -- Validar que el semillero existe y es único
    SELECT COUNT(*) INTO v_semillero_count
    FROM `Semillero`
    WHERE nombre = p_semillero_nombre;

    IF v_semillero_count = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El semillero especificado no existe.';
    ELSEIF v_semillero_count > 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Existen múltiples semilleros con el mismo nombre. Por favor, use un identificador único.';
    END IF;

    -- Obtener el semillero_id
    SELECT id INTO v_semillero_id
    FROM `Semillero`
    WHERE nombre = p_semillero_nombre
    LIMIT 1;

    -- Consulta principal
    SELECT 
        p.id,
        p.titulo,
        p.descripcion,
        p.estado,
        p.fecha_inicio,
        p.fecha_fin,
        COALESCE(lider.nombre, 'Sin líder') AS lider,
        COALESCE(coord.nombre, 'Sin coordinador') AS coordinador
    FROM `Proyecto` p
    LEFT JOIN `Usuario` lider ON p.lider_id = lider.id
    LEFT JOIN `Usuario` coord ON p.coordinador_id = coord.id
    WHERE p.semillero_id = v_semillero_id
    ORDER BY p.fecha_inicio DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerUsuariosPorSemillero` (IN `p_semillero_nombre` VARCHAR(100))   BEGIN
    DECLARE v_semillero_id INT;
    DECLARE v_semillero_count INT;

    -- Manejo de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error al obtener usuarios del semillero.';
    END;

    -- Validar que el semillero existe y es único
    SELECT COUNT(*) INTO v_semillero_count
    FROM `Semillero`
    WHERE nombre = p_semillero_nombre;

    IF v_semillero_count = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El semillero especificado no existe.';
    ELSEIF v_semillero_count > 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Existen múltiples semilleros con el mismo nombre. Por favor, use un identificador único.';
    END IF;

    -- Obtener el semillero_id
    SELECT id INTO v_semillero_id
    FROM `Semillero`
    WHERE nombre = p_semillero_nombre
    LIMIT 1;

    -- Consulta principal
    SELECT 
        u.id,
        u.nombre,
        u.email,
        u.tipo,
        GROUP_CONCAT(r.nombre) AS roles,
        COALESCE(p.nombre, 'Sin programa') AS programa,
        COALESCE(f.nombre, 'Sin facultad') AS facultad,
        COALESCE(uni.nombre, 'Sin universidad') AS universidad,
        su.fecha_inscripcion
    FROM `Semillero_Usuario` su
    JOIN `Usuario` u ON su.usuario_id = u.id
    LEFT JOIN `Usuario_Rol` ur ON u.id = ur.usuario_id
    LEFT JOIN `Rol` r ON ur.rol_id = r.id
    LEFT JOIN `Programa` p ON u.programa_id = p.id
    LEFT JOIN `Facultad` f ON p.facultad_id = f.id
    LEFT JOIN `Universidad` uni ON f.universidad_id = uni.id
    WHERE su.semillero_id = v_semillero_id
    GROUP BY u.id, su.fecha_inscripcion
    ORDER BY u.nombre;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarEvaluacion` (IN `p_proyecto_id` INT, IN `p_evaluador_id` INT, IN `p_evento_id` INT, IN `p_comentarios` TEXT, IN `p_dominio_tema` TINYINT, IN `p_manejo_auditorio` TINYINT, IN `p_planteamiento_problema` TINYINT, IN `p_justificacion` TINYINT, IN `p_objetivo_general` TINYINT, IN `p_objetivo_especifico` TINYINT, IN `p_marco_teorico` TINYINT, IN `p_metodologia` TINYINT, IN `p_resultado_esperado` TINYINT, IN `p_referencia_bibliografica` TINYINT, IN `p_estado` ENUM('pendiente','completada','cancelada'))   BEGIN
    DECLARE v_error BOOLEAN DEFAULT FALSE;
    
    -- Validar rangos de puntuación (0-5)
    IF p_dominio_tema NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_manejo_auditorio NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_planteamiento_problema NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_justificacion NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_objetivo_general NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_objetivo_especifico NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_marco_teorico NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_metodologia NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_resultado_esperado NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    IF p_referencia_bibliografica NOT BETWEEN 0 AND 5 THEN SET v_error = TRUE; END IF;
    
    IF v_error THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Todas las puntuaciones deben estar entre 0 y 5';
    ELSE
        -- Insertar la evaluación con todos los criterios
        INSERT INTO `Evaluacion` (
            proyecto_id, evaluador_id, evento_id, comentarios,
            dominio_tema, manejo_auditorio, planteamiento_problema,
            justificacion, objetivo_general, objetivo_especifico,
            marco_teorico, metodologia, resultado_esperado, referencia_bibliografica,
            estado
        ) VALUES (
            p_proyecto_id, p_evaluador_id, p_evento_id, p_comentarios,
            p_dominio_tema, p_manejo_auditorio, p_planteamiento_problema,
            p_justificacion, p_objetivo_general, p_objetivo_especifico,
            p_marco_teorico, p_metodologia, p_resultado_esperado, p_referencia_bibliografica,
            IFNULL(p_estado, 'completada')
        );
        
        SELECT LAST_INSERT_ID() AS evaluacion_id;
    END IF;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `ObtenerRolId` (`p_rol_nombre` VARCHAR(50)) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE v_rol_id INT;

    SELECT id INTO v_rol_id
    FROM `Rol`
    WHERE nombre = p_rol_nombre;

    IF v_rol_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El rol especificado no existe.';
    END IF;

    RETURN v_rol_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `semillero_id` int(11) DEFAULT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` enum('pendiente','en_progreso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`id`, `titulo`, `descripcion`, `semillero_id`, `proyecto_id`, `evento_id`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(7, 'Presentación de Proyectos de IA', 'Exposición de proyectos de inteligencia artificial', 8, NULL, NULL, '2025-08-01 09:00:00', '2025-08-01 12:00:00', 'pendiente', '2025-04-29 15:47:10', '2025-04-30 17:40:15'),
(8, 'Taller de Robótica', 'Taller práctico sobre construcción de robots', NULL, 9, 6, '2025-08-02 10:00:00', '2025-08-02 14:00:00', 'pendiente', '2025-04-29 15:47:10', '2025-04-30 17:40:32');

--
-- Disparadores `actividad`
--
DELIMITER $$
CREATE TRIGGER `validar_actividad_relacion` BEFORE INSERT ON `actividad` FOR EACH ROW BEGIN
    IF NEW.semillero_id IS NULL AND NEW.proyecto_id IS NULL AND NEW.evento_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La actividad debe estar asociada a al menos un semillero, proyecto o evento.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_responsable`
--

CREATE TABLE `actividad_responsable` (
  `actividad_id` int(11) NOT NULL,
  `responsable_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actividad_responsable`
--

INSERT INTO `actividad_responsable` (`actividad_id`, `responsable_id`) VALUES
(8, 20),
(8, 21);

--
-- Disparadores `actividad_responsable`
--
DELIMITER $$
CREATE TRIGGER `validar_responsable_actividad` BEFORE INSERT ON `actividad_responsable` FOR EACH ROW BEGIN
    DECLARE v_semillero_id INT;
    DECLARE v_proyecto_id INT;
    DECLARE v_usuario_en_semillero INT;
    DECLARE v_usuario_en_proyecto INT;

    -- Obtener semillero_id y proyecto_id de la actividad
    SELECT semillero_id, proyecto_id
    INTO v_semillero_id, v_proyecto_id
    FROM `Actividad`
    WHERE id = NEW.actividad_id;

    -- Caso 1: Actividad sin semillero ni proyecto (solo evento)
    IF v_semillero_id IS NULL AND v_proyecto_id IS NULL THEN
        -- No se aplica validación
        SET v_usuario_en_semillero = 1;
        SET v_usuario_en_proyecto = 1;

    -- Caso 2: Actividad solo con proyecto
    ELSEIF v_proyecto_id IS NOT NULL AND v_semillero_id IS NULL THEN
        -- Validar que el usuario pertenezca al proyecto
        SELECT COUNT(*) INTO v_usuario_en_proyecto
        FROM `Proyecto_Usuario`
        WHERE proyecto_id = v_proyecto_id AND usuario_id = NEW.responsable_id;

        IF v_usuario_en_proyecto = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario no pertenece al proyecto asociado a la actividad.';
        END IF;

    -- Caso 3: Actividad solo con semillero
    ELSEIF v_semillero_id IS NOT NULL AND v_proyecto_id IS NULL THEN
        -- Validar que el usuario pertenezca al semillero
        SELECT COUNT(*) INTO v_usuario_en_semillero
        FROM `Semillero_Usuario`
        WHERE semillero_id = v_semillero_id AND usuario_id = NEW.responsable_id;

        IF v_usuario_en_semillero = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario no pertenece al semillero asociado a la actividad.';
        END IF;

    -- Caso 4: Actividad con semillero y proyecto
    ELSE
        -- Validar si el usuario pertenece al semillero o al proyecto
        SELECT COUNT(*) INTO v_usuario_en_semillero
        FROM `Semillero_Usuario`
        WHERE semillero_id = v_semillero_id AND usuario_id = NEW.responsable_id;

        SELECT COUNT(*) INTO v_usuario_en_proyecto
        FROM `Proyecto_Usuario`
        WHERE proyecto_id = v_proyecto_id AND usuario_id = NEW.responsable_id;

        IF v_usuario_en_semillero = 0 AND v_usuario_en_proyecto = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario no pertenece ni al semillero ni al proyecto asociado a la actividad.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_4sGYYmDn9J0lZh2V', 's:7:\"forever\";', 2061491010),
('laravel_cache_Cpxz4HdYlVLPxpSR', 's:7:\"forever\";', 2061493459),
('laravel_cache_D2wdfaukCfX8Iwhs', 'a:1:{s:11:\"valid_until\";i:1746131089;}', 1747340689),
('laravel_cache_DofiJmLDwEBvhUk8', 'a:1:{s:11:\"valid_until\";i:1746214086;}', 1747423746),
('laravel_cache_opMP8L2DezliFsmW', 'a:1:{s:11:\"valid_until\";i:1746133434;}', 1747343094),
('laravel_cache_z0qlzb82rb3pQnEv', 'a:1:{s:11:\"valid_until\";i:1746129718;}', 1747338478);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `evaluador_id` int(11) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `dominio_tema` tinyint(4) NOT NULL,
  `manejo_auditorio` tinyint(4) NOT NULL,
  `planteamiento_problema` tinyint(4) NOT NULL,
  `justificacion` tinyint(4) NOT NULL,
  `objetivo_general` tinyint(4) NOT NULL,
  `objetivo_especifico` tinyint(4) NOT NULL,
  `marco_teorico` tinyint(4) NOT NULL,
  `metodologia` tinyint(4) NOT NULL,
  `resultado_esperado` tinyint(4) NOT NULL,
  `referencia_bibliografica` tinyint(4) NOT NULL,
  `puntuacion` decimal(3,1) GENERATED ALWAYS AS ((`dominio_tema` + `manejo_auditorio` + `planteamiento_problema` + `justificacion` + `objetivo_general` + `objetivo_especifico` + `marco_teorico` + `metodologia` + `resultado_esperado` + `referencia_bibliografica`) / 10.0) STORED,
  `puntaje_total` tinyint(4) GENERATED ALWAYS AS (`dominio_tema` + `manejo_auditorio` + `planteamiento_problema` + `justificacion` + `objetivo_general` + `objetivo_especifico` + `marco_teorico` + `metodologia` + `resultado_esperado` + `referencia_bibliografica`) STORED,
  `estado` enum('pendiente','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`id`, `proyecto_id`, `evaluador_id`, `comentarios`, `dominio_tema`, `manejo_auditorio`, `planteamiento_problema`, `justificacion`, `objetivo_general`, `objetivo_especifico`, `marco_teorico`, `metodologia`, `resultado_esperado`, `referencia_bibliografica`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 8, 18, 'Proyecto sólido, pero necesita mejorar la metodología', 4, 3, 4, 3, 4, 4, 3, 2, 4, 3, 'completada', '2025-04-29 15:47:29', '2025-04-30 17:14:35');

--
-- Disparadores `evaluacion`
--
DELIMITER $$
CREATE TRIGGER `validar_evaluador_profesor_insert` BEFORE INSERT ON `evaluacion` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    
    SELECT COUNT(*) INTO es_profesor
    FROM `Usuario`
    WHERE id = NEW.evaluador_id AND tipo = 'profesor';
    
    IF es_profesor = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El evaluador debe ser un profesor.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_evaluador_profesor_update` BEFORE UPDATE ON `evaluacion` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    
    SELECT COUNT(*) INTO es_profesor
    FROM `Usuario`
    WHERE id = NEW.evaluador_id AND tipo = 'profesor';
    
    IF es_profesor = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El evaluador debe ser un profesor.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_proyecto_inscrito_evento` BEFORE INSERT ON `evaluacion` FOR EACH ROW BEGIN
    DECLARE v_proyecto_evento INT;

    SELECT COUNT(*) INTO v_proyecto_evento
    FROM `Proyecto_Evento`
    WHERE proyecto_id = NEW.proyecto_id;

    IF v_proyecto_evento = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El proyecto no está inscrito en ningún evento.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_proyecto_inscrito_evento_update` BEFORE UPDATE ON `evaluacion` FOR EACH ROW BEGIN
    DECLARE v_proyecto_evento INT;

    SELECT COUNT(*) INTO v_proyecto_evento
    FROM `Proyecto_Evento`
    WHERE proyecto_id = NEW.proyecto_id;

    IF v_proyecto_evento = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El proyecto no está inscrito en ningún evento.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento`
--

CREATE TABLE `evento` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `coordinador_id` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evento`
--

INSERT INTO `evento` (`id`, `nombre`, `descripcion`, `coordinador_id`, `fecha_inicio`, `fecha_fin`, `ubicacion`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(6, 'Congreso de Tecnología 2025', 'Evento para presentar avances en tecnología', 18, '2025-08-01 08:00:00', '2025-08-02 18:00:00', 'Auditorio Universidad de Córdoba', '2025-04-29 15:47:10', '2025-04-29 15:47:10');

--
-- Disparadores `evento`
--
DELIMITER $$
CREATE TRIGGER `validar_coordinador_evento_insert` BEFORE INSERT ON `evento` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 5;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador de evento debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Eventos.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_coordinador_evento_update` BEFORE UPDATE ON `evento` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 5;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador de evento debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Eventos.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facultad`
--

CREATE TABLE `facultad` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `universidad_id` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facultad`
--

INSERT INTO `facultad` (`id`, `nombre`, `universidad_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Ingeniería', 1, '2025-04-29 12:13:57', '2025-04-29 12:14:41'),
(2, 'Ciencias', 1, '2025-04-29 12:13:57', '2025-04-29 12:14:52'),
(3, 'Administración', 1, '2025-04-29 12:13:57', '2025-04-29 12:14:56'),
(4, 'Ingeniería', 2, '2025-04-29 12:13:57', '2025-04-29 12:15:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa`
--

CREATE TABLE `programa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `facultad_id` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programa`
--

INSERT INTO `programa` (`id`, `nombre`, `descripcion`, `facultad_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(7, 'Ingeniería de Sistemas', 'Programa enfocado en desarrollo de software y tecnologías de la información', 1, '2025-04-29 12:15:20', '2025-04-29 12:15:20'),
(8, 'Ingeniería Electrónica', 'Formación en diseño y mantenimiento de sistemas electrónicos', 1, '2025-04-29 12:15:20', '2025-04-29 12:15:20'),
(9, 'Biología', 'Estudio de ciencias biológicas y biotecnología', 2, '2025-04-29 12:15:20', '2025-04-29 12:15:20'),
(10, 'Administración de Empresas', 'Gestión empresarial y emprendimiento', 3, '2025-04-29 12:15:20', '2025-04-29 12:15:20'),
(11, 'Matemáticas', 'Formación en ciencias matemáticas y estadística', 2, '2025-04-29 12:15:20', '2025-04-29 12:15:20'),
(12, 'Ingeniería Civil', 'Diseño y construcción de infraestructuras', 4, '2025-04-29 12:15:20', '2025-04-29 12:15:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
--

CREATE TABLE `proyecto` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `semillero_id` int(11) NOT NULL,
  `lider_id` int(11) DEFAULT NULL,
  `coordinador_id` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo','completado','cancelado') NOT NULL DEFAULT 'activo',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proyecto`
--

INSERT INTO `proyecto` (`id`, `titulo`, `descripcion`, `semillero_id`, `lider_id`, `coordinador_id`, `estado`, `fecha_inicio`, `fecha_fin`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(7, 'Desarrollo de Modelos de IA', 'Proyecto para investigar modelos de IA', 8, NULL, 18, 'activo', '2025-05-01', NULL, '2025-04-29 15:40:04', '2025-04-29 15:40:04'),
(8, 'Análisis de Datos con IA', 'Proyecto para analizar datasets con IA', 8, 20, 18, 'activo', '2025-06-01', NULL, '2025-04-29 15:45:48', '2025-04-29 15:45:48'),
(9, 'Desarrollo de Robots Autónomos', 'Construcción de robots con navegación autónoma', 9, 21, 22, 'activo', '2025-07-01', NULL, '2025-04-29 15:46:15', '2025-04-29 15:46:15');

--
-- Disparadores `proyecto`
--
DELIMITER $$
CREATE TRIGGER `validar_coordinador_proyecto_insert` BEFORE INSERT ON `proyecto` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 4;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Proyecto.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_coordinador_proyecto_update` BEFORE UPDATE ON `proyecto` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 4;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Proyecto.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_lider_en_semillero_insert` BEFORE INSERT ON `proyecto` FOR EACH ROW BEGIN
    DECLARE existe INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.lider_id IS NOT NULL THEN
        SELECT COUNT(*) INTO existe
        FROM `Semillero_Usuario` su
        JOIN `Usuario_Rol` ur ON ur.usuario_id = su.usuario_id
        WHERE su.semillero_id = NEW.semillero_id
          AND su.usuario_id = NEW.lider_id
          AND ur.rol_id = 2;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.lider_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF existe = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El líder asignado no pertenece al semillero o no tiene el rol de Líder de Proyecto.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El líder debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_lider_en_semillero_update` BEFORE UPDATE ON `proyecto` FOR EACH ROW BEGIN
    DECLARE existe INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.lider_id IS NOT NULL THEN
        SELECT COUNT(*) INTO existe
        FROM `Semillero_Usuario` su
        JOIN `Usuario_Rol` ur ON ur.usuario_id = su.usuario_id
        WHERE su.semillero_id = NEW.semillero_id
          AND su.usuario_id = NEW.lider_id
          AND ur.rol_id = 2;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.lider_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF existe = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El líder asignado no pertenece al semillero o no tiene el rol de Líder de Proyecto.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El líder debe pertenecer a la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_evento`
--

CREATE TABLE `proyecto_evento` (
  `id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `fecha_inscripcion` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `proyecto_evento`
--

INSERT INTO `proyecto_evento` (`id`, `proyecto_id`, `evento_id`, `fecha_inscripcion`, `observaciones`) VALUES
(1, 8, 6, '2025-04-30 15:44:07', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_usuario`
--

CREATE TABLE `proyecto_usuario` (
  `proyecto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proyecto_usuario`
--

INSERT INTO `proyecto_usuario` (`proyecto_id`, `usuario_id`, `fecha_asignacion`) VALUES
(8, 24, '2025-04-30 17:51:04'),
(8, 27, '2025-05-03 18:11:22'),
(9, 21, '2025-04-30 17:54:23');

--
-- Disparadores `proyecto_usuario`
--
DELIMITER $$
CREATE TRIGGER `validar_usuario_en_semillero_proyecto` BEFORE INSERT ON `proyecto_usuario` FOR EACH ROW BEGIN
    DECLARE v_semillero_id INT;
    DECLARE v_usuario_en_semillero INT;

    SELECT semillero_id
    INTO v_semillero_id
    FROM `Proyecto`
    WHERE id = NEW.proyecto_id;

 
    SELECT COUNT(*) INTO v_usuario_en_semillero
    FROM `Semillero_Usuario`
    WHERE semillero_id = v_semillero_id AND usuario_id = NEW.usuario_id;


    IF v_usuario_en_semillero = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario no pertenece al semillero asociado al proyecto.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_actividadesconresponsables`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_actividadesconresponsables` (
`id` int(11)
,`titulo` varchar(200)
,`evento` varchar(200)
,`responsables` mediumtext
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_evaluacionesproyectos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_evaluacionesproyectos` (
`id` int(11)
,`proyecto` varchar(200)
,`evaluador` varchar(100)
,`puntuacion` decimal(3,1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_eventoscoordinados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_eventoscoordinados` (
`id` int(11)
,`nombre` varchar(200)
,`coordinador` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_proyectosconsemillero`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_proyectosconsemillero` (
`id` int(11)
,`titulo` varchar(200)
,`semillero` varchar(100)
,`lider` varchar(100)
,`coordinador` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_semilleroscoordinacion`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_semilleroscoordinacion` (
`id` int(11)
,`nombre` varchar(100)
,`coordinador` varchar(100)
,`programa` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_usuariosdetallado`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_usuariosdetallado` (
`id` int(11)
,`nombre` varchar(100)
,`email` varchar(100)
,`tipo` enum('estudiante','profesor','administrador')
,`roles` mediumtext
,`programa` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre`, `descripcion`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Integrante Semillero', 'Participa en proyectos como investigador', '2025-04-29 12:15:43', '2025-04-29 12:15:50'),
(2, 'Lider de Proyecto', 'Crea proyectos', '2025-04-29 12:15:43', '2025-04-29 12:15:55'),
(3, 'Coordinador de Semillero', 'Gestiona semilleros', '2025-04-29 12:15:43', '2025-04-29 12:15:58'),
(4, 'Coordinador de Proyecto', 'Participa en proyectos como asesor', '2025-04-29 12:15:43', '2025-04-29 12:16:01'),
(5, 'Coordinador de Eventos', 'Organiza eventos académicos', '2025-04-29 12:15:43', '2025-04-29 12:16:05'),
(6, 'Administrador', 'Acceso completo al sistema', '2025-04-29 12:15:43', '2025-04-29 12:16:07'),
(7, 'Evaluador', 'Evalúa actividades', '2025-04-29 12:15:43', '2025-04-29 12:16:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `semillero`
--

CREATE TABLE `semillero` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `coordinador_id` int(11) DEFAULT NULL,
  `programa_id` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `semillero`
--

INSERT INTO `semillero` (`id`, `nombre`, `descripcion`, `coordinador_id`, `programa_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(8, 'Inteligencia Artificial', 'Investigación en IA', 18, 7, '2025-04-29 15:23:14', '2025-04-30 17:49:19'),
(9, 'Robótica', 'Investigación en robótica y automatización', 22, 8, '2025-04-29 15:44:24', '2025-04-30 17:49:27');

--
-- Disparadores `semillero`
--
DELIMITER $$
CREATE TRIGGER `validar_coordinador_semillero_insert` BEFORE INSERT ON `semillero` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 3;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador de semillero debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Semillero.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a un programa de la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_coordinador_semillero_update` BEFORE UPDATE ON `semillero` FOR EACH ROW BEGIN
    DECLARE es_profesor INT;
    DECLARE tiene_rol_coordinador INT;
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    
    IF NEW.coordinador_id IS NOT NULL THEN
        SELECT COUNT(*) INTO es_profesor
        FROM `Usuario`
        WHERE id = NEW.coordinador_id AND tipo = 'profesor';
        
        SELECT COUNT(*) INTO tiene_rol_coordinador
        FROM `Usuario_Rol`
        WHERE usuario_id = NEW.coordinador_id AND rol_id = 3;
        
        SELECT f.universidad_id INTO universidad_usuario
        FROM `Usuario` u
        LEFT JOIN `Programa` p ON u.programa_id = p.id
        LEFT JOIN `Facultad` f ON p.facultad_id = f.id
        WHERE u.id = NEW.coordinador_id;
        
        SELECT id INTO universidad_cordoba_id
        FROM `Universidad`
        WHERE nombre = 'Universidad de Cordoba';
        
        IF es_profesor = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador de semillero debe ser un profesor.';
        ELSEIF tiene_rol_coordinador = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador asignado no tiene el rol de Coordinador de Semillero.';
        ELSEIF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El coordinador debe pertenecer a un programa de la Universidad de Córdoba.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `semillero_usuario`
--

CREATE TABLE `semillero_usuario` (
  `semillero_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_inscripcion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `semillero_usuario`
--

INSERT INTO `semillero_usuario` (`semillero_id`, `usuario_id`, `fecha_inscripcion`) VALUES
(8, 24, '2025-04-30 17:50:11'),
(8, 27, '2025-05-03 18:10:58'),
(9, 21, '2025-04-30 17:50:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universidad`
--

CREATE TABLE `universidad` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `universidad`
--

INSERT INTO `universidad` (`id`, `nombre`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Universidad de Cordoba', '2025-04-29 12:12:52', '2025-04-29 12:13:41'),
(2, 'Universidad Nacional', '2025-04-29 12:12:52', '2025-04-29 12:13:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tipo` enum('estudiante','profesor','administrador') NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `programa_id` int(11) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `email`, `tipo`, `contraseña`, `programa_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(18, 'Juan Pérez', 'juan.perez.prof@universidad.edu', 'profesor', 'new_hashed_password_456', 7, '2025-04-29 12:34:26', '2025-04-29 12:36:29'),
(19, 'Ana Gómez', 'ana.gomez@externo.com', 'administrador', 'hashed_password_789', NULL, '2025-04-29 12:37:10', '2025-04-29 12:37:10'),
(20, 'Carlos Martínez', 'carlos.martinez@universidad.edu', 'estudiante', 'hashed_password_123', 7, '2025-04-29 15:43:03', '2025-04-29 15:43:03'),
(21, 'Laura Sánchez', 'laura.sanchez@universidad.edu', 'estudiante', 'hashed_password_456', 8, '2025-04-29 15:43:31', '2025-04-29 15:43:31'),
(22, 'Diana Torres', 'diana.torres@universidad.edu', 'profesor', 'hashed_password_789', 8, '2025-04-29 15:43:58', '2025-04-29 15:43:58'),
(23, 'Pedro Ramírez', 'pedro.ramirez@externo.com', 'administrador', 'hashed_password_101', NULL, '2025-04-29 15:44:10', '2025-04-29 15:44:10'),
(24, 'Enrique Martinez', 'martinez@edu.co', 'estudiante', 'password', 7, '2025-04-30 17:49:01', '2025-04-30 17:49:01'),
(25, 'test', 'correo@ejemplo.com', 'estudiante', '$2y$12$EITIeuaf.QJN/4wkLug70u1clLQ4.wrNgNqBFOzmA8dnqzu/jultC', 7, '2025-05-01 14:29:05', '2025-05-01 14:29:05'),
(26, 'test dos', 'correo2@ejemplo.com', 'estudiante', '$2y$12$LBdZmr2.zoLnf4fu9ildM.BSY8p07ol7PQBP/Dy6TIm/AHhV9HGO.', 7, '2025-05-01 14:46:58', '2025-05-01 14:46:58'),
(27, 'Elias Balle', 'elias@ejemplo.com', 'estudiante', '$2y$12$RRBWTtvpIKNr3nr7218/jOaz9oyQykWZaMRd79x2nTDhaw0lpmCmG', 7, '2025-05-02 14:25:52', '2025-05-02 14:25:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

CREATE TABLE `usuario_rol` (
  `usuario_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`usuario_id`, `rol_id`) VALUES
(18, 3),
(18, 4),
(18, 5),
(18, 7),
(19, 6),
(20, 1),
(20, 2),
(21, 1),
(21, 2),
(22, 3),
(22, 4),
(22, 7),
(23, 6),
(24, 1);

--
-- Disparadores `usuario_rol`
--
DELIMITER $$
CREATE TRIGGER `validar_rol_usuario_insert` BEFORE INSERT ON `usuario_rol` FOR EACH ROW BEGIN
    DECLARE usuario_tipo VARCHAR(20);
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    DECLARE rol_nombre VARCHAR(50);
    
    SELECT tipo INTO usuario_tipo FROM `Usuario` WHERE id = NEW.usuario_id;
    
    SELECT f.universidad_id INTO universidad_usuario
    FROM `Usuario` u
    LEFT JOIN `Programa` p ON u.programa_id = p.id
    LEFT JOIN `Facultad` f ON p.facultad_id = f.id
    WHERE u.id = NEW.usuario_id;
    
    SELECT id INTO universidad_cordoba_id FROM `Universidad` WHERE nombre = 'Universidad de Cordoba';
    
    SELECT nombre INTO rol_nombre FROM `Rol` WHERE id = NEW.rol_id;
    
    IF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
        IF rol_nombre IN ('Coordinador de Semillero', 'Coordinador de Proyecto', 'Coordinador de Eventos', 'Lider de Proyecto') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Usuarios externos no pueden tener roles de coordinador o líder.';
        END IF;
    END IF;
    
    IF usuario_tipo = 'estudiante' THEN
        IF rol_nombre IN ('Coordinador de Semillero', 'Coordinador de Proyecto', 'Coordinador de Eventos', 'Evaluador', 'Administrador') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Los estudiantes no pueden tener roles de profesores o administradores.';
        END IF;
    ELSEIF usuario_tipo = 'profesor' THEN
        IF rol_nombre IN ('Lider de Proyecto', 'Integrante Semillero') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Los profesores no pueden tener roles reservados para estudiantes.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validar_rol_usuario_update` BEFORE UPDATE ON `usuario_rol` FOR EACH ROW BEGIN
    DECLARE usuario_tipo VARCHAR(20);
    DECLARE universidad_usuario INT;
    DECLARE universidad_cordoba_id INT;
    DECLARE rol_nombre VARCHAR(50);
    
    SELECT tipo INTO usuario_tipo FROM `Usuario` WHERE id = NEW.usuario_id;
    
    SELECT f.universidad_id INTO universidad_usuario
    FROM `Usuario` u
    LEFT JOIN `Programa` p ON u.programa_id = p.id
    LEFT JOIN `Facultad` f ON p.facultad_id = f.id
    WHERE u.id = NEW.usuario_id;
    
    SELECT id INTO universidad_cordoba_id FROM `Universidad` WHERE nombre = 'Universidad de Cordoba';
    
    SELECT nombre INTO rol_nombre FROM `Rol` WHERE id = NEW.rol_id;
    
    IF universidad_usuario IS NULL OR universidad_usuario != universidad_cordoba_id THEN
        IF rol_nombre IN ('Coordinador de Semillero', 'Coordinador de Proyecto', 'Coordinador de Eventos', 'Lider de Proyecto') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Usuarios externos no pueden tener roles de coordinador o líder.';
        END IF;
    END IF;
    
    IF usuario_tipo = 'estudiante' THEN
        IF rol_nombre IN ('Coordinador de Semillero', 'Coordinador de Proyecto', 'Coordinador de Eventos', 'Evaluador', 'Administrador') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Los estudiantes no pueden tener roles de profesores o administradores.';
        END IF;
    ELSEIF usuario_tipo = 'profesor' THEN
        IF rol_nombre IN ('Lider de Proyecto', 'Integrante Semillero') THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: Los profesores no pueden tener roles reservados para estudiantes.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_actividadesconresponsables`
--
DROP TABLE IF EXISTS `reporte_actividadesconresponsables`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_actividadesconresponsables`  AS SELECT `a`.`id` AS `id`, `a`.`titulo` AS `titulo`, `e`.`nombre` AS `evento`, group_concat(`u`.`nombre` order by `u`.`nombre` ASC separator ',') AS `responsables` FROM (((`actividad` `a` join `evento` `e` on(`a`.`evento_id` = `e`.`id`)) join `actividad_responsable` `ar` on(`a`.`id` = `ar`.`actividad_id`)) join `usuario` `u` on(`ar`.`responsable_id` = `u`.`id`)) GROUP BY `a`.`id` ORDER BY `a`.`fecha_inicio` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_evaluacionesproyectos`
--
DROP TABLE IF EXISTS `reporte_evaluacionesproyectos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_evaluacionesproyectos`  AS SELECT `e`.`id` AS `id`, `p`.`titulo` AS `proyecto`, `u`.`nombre` AS `evaluador`, `e`.`puntuacion` AS `puntuacion` FROM ((`evaluacion` `e` join `proyecto` `p` on(`e`.`proyecto_id` = `p`.`id`)) join `usuario` `u` on(`e`.`evaluador_id` = `u`.`id`)) ORDER BY `e`.`fecha_creacion` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_eventoscoordinados`
--
DROP TABLE IF EXISTS `reporte_eventoscoordinados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_eventoscoordinados`  AS SELECT `e`.`id` AS `id`, `e`.`nombre` AS `nombre`, coalesce(`u`.`nombre`,'Sin coordinador') AS `coordinador` FROM (`evento` `e` left join `usuario` `u` on(`e`.`coordinador_id` = `u`.`id`)) ORDER BY `e`.`fecha_inicio` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_proyectosconsemillero`
--
DROP TABLE IF EXISTS `reporte_proyectosconsemillero`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_proyectosconsemillero`  AS SELECT `p`.`id` AS `id`, `p`.`titulo` AS `titulo`, `s`.`nombre` AS `semillero`, coalesce(`u1`.`nombre`,'Sin líder') AS `lider`, coalesce(`u2`.`nombre`,'Sin coordinador') AS `coordinador` FROM (((`proyecto` `p` join `semillero` `s` on(`p`.`semillero_id` = `s`.`id`)) left join `usuario` `u1` on(`p`.`lider_id` = `u1`.`id`)) left join `usuario` `u2` on(`p`.`coordinador_id` = `u2`.`id`)) ORDER BY `p`.`titulo` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_semilleroscoordinacion`
--
DROP TABLE IF EXISTS `reporte_semilleroscoordinacion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_semilleroscoordinacion`  AS SELECT `s`.`id` AS `id`, `s`.`nombre` AS `nombre`, coalesce(`u`.`nombre`,'Sin coordinador') AS `coordinador`, coalesce(`p`.`nombre`,'Sin programa') AS `programa` FROM ((`semillero` `s` left join `usuario` `u` on(`s`.`coordinador_id` = `u`.`id`)) left join `programa` `p` on(`s`.`programa_id` = `p`.`id`)) ORDER BY `s`.`nombre` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_usuariosdetallado`
--
DROP TABLE IF EXISTS `reporte_usuariosdetallado`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_usuariosdetallado`  AS SELECT `u`.`id` AS `id`, `u`.`nombre` AS `nombre`, `u`.`email` AS `email`, `u`.`tipo` AS `tipo`, group_concat(`r`.`nombre` order by `r`.`nombre` ASC separator ',') AS `roles`, coalesce(`p`.`nombre`,'Sin programa') AS `programa` FROM (((`usuario` `u` left join `usuario_rol` `ur` on(`u`.`id` = `ur`.`usuario_id`)) left join `rol` `r` on(`ur`.`rol_id` = `r`.`id`)) left join `programa` `p` on(`u`.`programa_id` = `p`.`id`)) GROUP BY `u`.`id` ORDER BY `u`.`nombre` ASC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semillero_id` (`semillero_id`),
  ADD KEY `proyecto_id` (`proyecto_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `actividad_responsable`
--
ALTER TABLE `actividad_responsable`
  ADD PRIMARY KEY (`actividad_id`,`responsable_id`),
  ADD KEY `responsable_id` (`responsable_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_id` (`proyecto_id`),
  ADD KEY `evaluador_id` (`evaluador_id`);

--
-- Indices de la tabla `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coordinador_id` (`coordinador_id`);

--
-- Indices de la tabla `facultad`
--
ALTER TABLE `facultad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_universidad` (`nombre`,`universidad_id`),
  ADD KEY `universidad_id` (`universidad_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `programa`
--
ALTER TABLE `programa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_facultad` (`nombre`,`facultad_id`),
  ADD KEY `facultad_id` (`facultad_id`);

--
-- Indices de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semillero_id` (`semillero_id`),
  ADD KEY `lider_id` (`lider_id`),
  ADD KEY `coordinador_id` (`coordinador_id`);

--
-- Indices de la tabla `proyecto_evento`
--
ALTER TABLE `proyecto_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proyecto_id` (`proyecto_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`proyecto_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `semillero`
--
ALTER TABLE `semillero`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coordinador_id` (`coordinador_id`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `semillero_usuario`
--
ALTER TABLE `semillero_usuario`
  ADD PRIMARY KEY (`semillero_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `universidad`
--
ALTER TABLE `universidad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `evento`
--
ALTER TABLE `evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `facultad`
--
ALTER TABLE `facultad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `programa`
--
ALTER TABLE `programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `proyecto_evento`
--
ALTER TABLE `proyecto_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `semillero`
--
ALTER TABLE `semillero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `universidad`
--
ALTER TABLE `universidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD CONSTRAINT `Actividad_ibfk_1` FOREIGN KEY (`semillero_id`) REFERENCES `semillero` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Actividad_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyecto` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Actividad_ibfk_3` FOREIGN KEY (`evento_id`) REFERENCES `evento` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `actividad_responsable`
--
ALTER TABLE `actividad_responsable`
  ADD CONSTRAINT `Actividad_Responsable_ibfk_1` FOREIGN KEY (`actividad_id`) REFERENCES `actividad` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Actividad_Responsable_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `Evaluacion_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyecto` (`id`),
  ADD CONSTRAINT `Evaluacion_ibfk_2` FOREIGN KEY (`evaluador_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `evento`
--
ALTER TABLE `evento`
  ADD CONSTRAINT `Evento_ibfk_1` FOREIGN KEY (`coordinador_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `facultad`
--
ALTER TABLE `facultad`
  ADD CONSTRAINT `Facultad_ibfk_1` FOREIGN KEY (`universidad_id`) REFERENCES `universidad` (`id`);

--
-- Filtros para la tabla `programa`
--
ALTER TABLE `programa`
  ADD CONSTRAINT `Programa_ibfk_1` FOREIGN KEY (`facultad_id`) REFERENCES `facultad` (`id`);

--
-- Filtros para la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `Proyecto_ibfk_1` FOREIGN KEY (`semillero_id`) REFERENCES `semillero` (`id`),
  ADD CONSTRAINT `Proyecto_ibfk_2` FOREIGN KEY (`lider_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Proyecto_ibfk_3` FOREIGN KEY (`coordinador_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `proyecto_evento`
--
ALTER TABLE `proyecto_evento`
  ADD CONSTRAINT `Proyecto_Evento_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyecto` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Proyecto_Evento_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `evento` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD CONSTRAINT `Proyecto_Usuario_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyecto` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Proyecto_Usuario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `semillero`
--
ALTER TABLE `semillero`
  ADD CONSTRAINT `Semillero_ibfk_1` FOREIGN KEY (`coordinador_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Semillero_ibfk_2` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`);

--
-- Filtros para la tabla `semillero_usuario`
--
ALTER TABLE `semillero_usuario`
  ADD CONSTRAINT `Semillero_Usuario_ibfk_1` FOREIGN KEY (`semillero_id`) REFERENCES `semillero` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Semillero_Usuario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `Usuario_ibfk_1` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `Usuario_Rol_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Usuario_Rol_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
