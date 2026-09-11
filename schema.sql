-- ============================================================================
-- SANTIAGO SOFTWARE - SCRIPT COMPLETO DE RESETEOS Y CREACION DE TABLAS
-- Copia y pega este script completo en el SQL Editor de Supabase
-- ============================================================================

-- 1. LIMPIEZA DE TABLAS PREVIAS (DROP TABLES WITH CASCADE)
DROP TABLE IF EXISTS public.audit_logs CASCADE;
DROP TABLE IF EXISTS public.grades CASCADE;
DROP TABLE IF EXISTS public.students CASCADE;
DROP TABLE IF EXISTS public.reincorporaciones CASCADE;
DROP TABLE IF EXISTS public.leads CASCADE;
DROP TABLE IF EXISTS public.teacher_assignments CASCADE;
DROP TABLE IF EXISTS public.subjects CASCADE;
DROP TABLE IF EXISTS public.schools CASCADE;
DROP TABLE IF EXISTS public.users CASCADE;
DROP TABLE IF EXISTS public.profiles CASCADE;

-- Limpieza de funciones y tipos previos
DROP FUNCTION IF EXISTS public.fn_audit_trigger() CASCADE;
DROP TYPE IF EXISTS public.user_role CASCADE;
DROP TYPE IF EXISTS public.contact_channel CASCADE;
DROP TYPE IF EXISTS public.contact_status CASCADE;

-- 2. HABILITAR EXTENSIONES CRIPTOGRAFICAS
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 3. CREACION DE LA TABLA DE USUARIOS (BCRYPT PASSWORDS)
CREATE TABLE public.users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    ci VARCHAR(50),
    phone VARCHAR(50),
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'profesor')),
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. CREACION DE LA TABLA DE ESCUELAS / CARRERAS
CREATE TABLE public.schools (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. CREACION DE LA TABLA DE MATERIAS
CREATE TABLE public.subjects (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    school_id UUID REFERENCES public.schools(id) ON DELETE CASCADE,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    credits INT DEFAULT 3,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. ASIGNACIONES PROFESOR - MATERIA
CREATE TABLE public.teacher_assignments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    teacher_id UUID REFERENCES public.users(id) ON DELETE CASCADE,
    subject_id UUID REFERENCES public.subjects(id) ON DELETE CASCADE,
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(teacher_id, subject_id, period)
);

-- 7. CONTROL DE CAPTACION DE ASPIRANTES (CRM DE 7 BANDERAS)
CREATE TABLE public.leads (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    full_name VARCHAR(255) NOT NULL,
    ci VARCHAR(50),
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    address TEXT,
    high_school VARCHAR(255),
    school_id UUID REFERENCES public.schools(id) ON DELETE SET NULL,
    carrera_cursar VARCHAR(255) DEFAULT 'Por Decidir',
    referred_by VARCHAR(255),
    contact_date DATE,
    channel VARCHAR(50) DEFAULT 'WhatsApp',
    call_date DATE,
    call_status VARCHAR(50) DEFAULT 'EN_ESPERA',
    contact_result TEXT,
    -- Banderas booleanas de captación
    se_inscribio_link_pre_univ BOOLEAN DEFAULT FALSE,
    se_inscribio_pre_universitario BOOLEAN DEFAULT FALSE,
    asistio_pre_universitario BOOLEAN DEFAULT FALSE,
    tiene_dudas_carrera BOOLEAN DEFAULT FALSE,
    ya_tiene_definida_carrera BOOLEAN DEFAULT FALSE,
    manifesto_no_inscribirse BOOLEAN DEFAULT FALSE,
    se_inscribio BOOLEAN DEFAULT FALSE,
    created_by UUID REFERENCES public.users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. CONTROL DE REINCORPORACIONES
CREATE TABLE public.reincorporaciones (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    full_name VARCHAR(255) NOT NULL,
    ci VARCHAR(50),
    phone VARCHAR(50),
    email VARCHAR(255),
    carrera_cursar VARCHAR(255),
    call_status VARCHAR(50) DEFAULT 'EN_ESPERA',
    response_notes TEXT,
    school_id UUID REFERENCES public.schools(id) ON DELETE SET NULL,
    remitido_por VARCHAR(255),
    student_status VARCHAR(100) DEFAULT 'Reincorporación Regular (Por Inscribir)',
    responsible VARCHAR(255),
    se_inscribio BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 9. TABLA DE ESTUDIANTES REGULARES
CREATE TABLE public.students (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ci VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    school_id UUID REFERENCES public.schools(id) ON DELETE RESTRICT,
    status VARCHAR(50) DEFAULT 'Activo',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 10. EVALUACION DE NOTAS DE PROFESORES
CREATE TABLE public.grades (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    student_id UUID REFERENCES public.students(id) ON DELETE CASCADE,
    subject_id UUID REFERENCES public.subjects(id) ON DELETE CASCADE,
    teacher_id UUID REFERENCES public.users(id) ON DELETE SET NULL,
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    corta1 NUMERIC(4,2) CHECK (corta1 >= 0 AND corta1 <= 20),
    corta2 NUMERIC(4,2) CHECK (corta2 >= 0 AND corta2 <= 20),
    corta3 NUMERIC(4,2) CHECK (corta3 >= 0 AND corta3 <= 20),
    final_grade NUMERIC(4,2) CHECK (final_grade >= 0 AND final_grade <= 20),
    observations TEXT,
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(student_id, subject_id, period)
);

-- 11. AUDITORIA CON FIRMAS SHA-256
CREATE TABLE public.audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    table_name VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    record_id UUID,
    old_data JSONB,
    new_data JSONB,
    performed_by UUID REFERENCES public.users(id) ON DELETE SET NULL,
    user_email VARCHAR(255),
    user_role VARCHAR(50),
    ip_address VARCHAR(100),
    hash_checksum VARCHAR(64) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================================
-- POBLADO DE DATOS SEMILLA (INSERCIONES INICIALES)
-- ============================================================================

-- Insertar Escuelas
INSERT INTO public.schools (id, code, name) VALUES
('11111111-1111-1111-1111-111111111101', 'ING-CIVIL', 'Ingeniería Civil'),
('11111111-1111-1111-1111-111111111102', 'ING-ELEC', 'Ingeniería Electrónica'),
('11111111-1111-1111-1111-111111111103', 'ING-ELEK', 'Ingeniería Eléctrica'),
('11111111-1111-1111-1111-111111111104', 'ING-SIST', 'Ingeniería de Sistemas'),
('11111111-1111-1111-1111-111111111105', 'ING-QUIM', 'Ingeniería Química'),
('11111111-1111-1111-1111-111111111106', 'ING-MEC', 'Ingeniería Mecánica (Mtto)'),
('11111111-1111-1111-1111-111111111107', 'ING-IND', 'Ingeniería Industrial'),
('11111111-1111-1111-1111-111111111108', 'ARQ', 'Arquitectura'),
('11111111-1111-1111-1111-111111111109', 'POR-DECIDIR', 'Por Decidir');

-- Insertar Usuarios Predeterminados
INSERT INTO public.users (id, email, password_hash, full_name, role) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'admin@santiagosoftware.com', '$2y$12$6/h95oYtK1J0FzKkW5bIduN2J5f/8h1Q3oU.Z1K5L4m3N2o1P0qSa', 'Administrador Principal', 'admin'),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'profesor@santiagosoftware.com', '$2y$12$8.k1L2M3N4O5P6Q7R8S9TuV1W2X3Y4Z5A6B7C8D9E0F1G2H3I4J5K', 'Prof. Manuel Alfonzo', 'profesor');

-- Insertar Aspirantes Iniciales
INSERT INTO public.leads (id, period, full_name, ci, phone, email, address, high_school, carrera_cursar, referred_by, contact_date, channel, call_date, call_status, contact_result, se_inscribio_link_pre_univ, se_inscribio_pre_universitario, asistio_pre_universitario, tiene_dudas_carrera, ya_tiene_definida_carrera, manifesto_no_inscribirse, se_inscribio) VALUES
('c1000000-0000-0000-0000-000000000001', '2026-2', 'Victoria Novoa Calojero', '33232308', '0412-9827554', 'novoavctr@gmail.com', 'Porlamar, Mcpio. Mariño', 'UE Educacional Porlamar', 'Por Decidir', 'ELIFRANK SALAZAR', '2026-08-04', 'WhatsApp', '2026-08-05', 'SI', 'SE LE BRINDO ASESORIA A LA ESTUDIANTE EL CUAL NOTIFICO QUE QUERIA ESTUDIAR INGENIERIA GEOLOGICA, YA QUE NO CONTAMOS SE LE OFRECIO INGENIERIA CIVIL', false, false, false, false, false, false, false),
('c1000000-0000-0000-0000-000000000002', '2026-2', 'JUAN DIEGO ROMERO', '31.455.814', '0424-8462796', '', '', '', 'Ingeniería Electrónica', 'AURORI ALFONZO', '2026-08-05', 'WhatsApp', '2026-08-05', 'NO_RESPONDE', 'NO CONTESTO Y SE LE DEJO MENSAJE VIA WHATSAPP', false, false, false, false, false, false, false),
('c1000000-0000-0000-0000-000000000008', '2026-2', 'MELBIN JAVIER CALDERIN BOADAS', '34162204', '0412-7266949', '', '', '', 'Ingeniería Civil', 'AURORI ALFONZO', '2026-08-05', 'LLAMADA', '2026-08-05', 'SI', 'CONFIRMO E INSCRITO EN SISTEMA', true, true, true, false, true, false, true);

-- Insertar Reincorporaciones Iniciales
INSERT INTO public.reincorporaciones (id, full_name, ci, phone, email, carrera_cursar, remitido_por, student_status, responsible, se_inscribio) VALUES
('r2000000-0000-0000-0000-000000000001', 'Alondra Marval', '28570556', '0424-8966606', 'alondramarval09@gmail.com', 'Arquitectura', 'Elifrank Salazar', 'Reincorporación Regular (Por Inscribir)', 'Manuel', false),
('r2000000-0000-0000-0000-000000000002', 'ENYER MARCANO', '', '0412-9809261', '', 'Ingeniería de Sistemas', 'JAVIER AMUNDARAY', 'Reincorporación Regular (Por Inscribir)', 'Manuel', false);
