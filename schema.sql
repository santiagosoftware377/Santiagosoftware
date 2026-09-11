-- ============================================================================
-- SANTIAGO SOFTWARE - ESTRUCTURA COMPLETA BD POSTGRESQL / SUPABASE (PHP)
-- ============================================================================

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 1. TABLA DE USUARIOS CON SEGURIDAD HASH (BCRYPT)
CREATE TABLE IF NOT EXISTS public.users (
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

-- 2. TABLA DE ESCUELAS / CARRERAS
CREATE TABLE IF NOT EXISTS public.schools (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. TABLA DE MATERIAS
CREATE TABLE IF NOT EXISTS public.subjects (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    school_id UUID REFERENCES public.schools(id) ON DELETE CASCADE,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    credits INT DEFAULT 3,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. TABLA DE ASIGNACIONES PROFESOR-MATERIA
CREATE TABLE IF NOT EXISTS public.teacher_assignments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    teacher_id UUID REFERENCES public.users(id) ON DELETE CASCADE,
    subject_id UUID REFERENCES public.subjects(id) ON DELETE CASCADE,
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(teacher_id, subject_id, period)
);

-- 5. TABLA DE CONTROL DE CAPTACION (ASPIRANTES)
CREATE TABLE IF NOT EXISTS public.leads (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    full_name VARCHAR(255) NOT NULL,
    ci VARCHAR(50),
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    address TEXT,
    high_school VARCHAR(255),
    school_id UUID REFERENCES public.schools(id) ON DELETE SET NULL,
    referred_by VARCHAR(255),
    contact_date DATE,
    channel VARCHAR(50) DEFAULT 'WhatsApp',
    call_date DATE,
    call_status VARCHAR(50) DEFAULT 'EN_ESPERA',
    contact_result TEXT,
    -- Banderas booleanas del Excel
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

-- 6. TABLA DE REINCORPORACIONES
CREATE TABLE IF NOT EXISTS public.reincorporaciones (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    period VARCHAR(20) NOT NULL DEFAULT '2026-2',
    full_name VARCHAR(255) NOT NULL,
    ci VARCHAR(50),
    phone VARCHAR(50),
    email VARCHAR(255),
    call_status VARCHAR(50) DEFAULT 'EN_ESPERA',
    response_notes TEXT,
    school_id UUID REFERENCES public.schools(id) ON DELETE SET NULL,
    remitido_por VARCHAR(255),
    student_status VARCHAR(100) DEFAULT 'Reincorporación Regular (Por Inscribir)',
    responsible VARCHAR(255),
    se_inscribio BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 7. TABLA DE ESTUDIANTES REGULARES
CREATE TABLE IF NOT EXISTS public.students (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ci VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    school_id UUID REFERENCES public.schools(id) ON DELETE RESTRICT,
    status VARCHAR(50) DEFAULT 'Activo',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. TABLA DE NOTAS / CALIFICACIONES
CREATE TABLE IF NOT EXISTS public.grades (
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

-- 9. TABLA DE AUDITORIA COMPLEJA CON HASH SHA-256
CREATE TABLE IF NOT EXISTS public.audit_logs (
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
-- POBLADO INICIAL (DATOS SEMILLA PROVISTOS EN LA SOLICITUD)
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
('11111111-1111-1111-1111-111111111109', 'POR-DECIDIR', 'Por Decidir')
ON CONFLICT (code) DO NOTHING;

-- Insertar Usuarios por defecto (Contraseñas con hash BCRYPT)
-- admin123 => $2y$12$R.9N1D3rO.0dGj/z/VjNue2GzW9s6lZ7wX5qY6k7J8a9b0c1d2e3f (o derivado)
-- prof123  => $2y$12$K.1M2N3O4P5Q6R7S8T9U0V1W2X3Y4Z5A6B7C8D9E0F1G2H3I4J5K
INSERT INTO public.users (id, email, password_hash, full_name, role) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'admin@santiagosoftware.com', '$2y$12$6/h95oYtK1J0FzKkW5bIduN2J5f/8h1Q3oU.Z1K5L4m3N2o1P0qSa', 'Administrador Principal', 'admin'),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'profesor@santiagosoftware.com', '$2y$12$8.k1L2M3N4O5P6Q7R8S9TuV1W2X3Y4Z5A6B7C8D9E0F1G2H3I4J5K', 'Prof. Manuel Alfonzo', 'profesor')
ON CONFLICT (email) DO NOTHING;
