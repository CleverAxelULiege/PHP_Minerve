

-- Core entity tables
CREATE TABLE buildings (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    location_code VARCHAR DEFAULT NULL,
    parking_code VARCHAR DEFAULT NULL,
    comments VARCHAR,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0)
);

CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    fullname VARCHAR,
    president VARCHAR,
    comments VARCHAR,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0)
);

CREATE TABLE fapse_users (
    id SERIAL PRIMARY KEY,
    ulg_id VARCHAR,
    lastname VARCHAR,
    firstname VARCHAR,
    surname VARCHAR,
    email VARCHAR,
    phone_number VARCHAR,
    personal_directory VARCHAR,
    comments VARCHAR,
    visible BOOLEAN DEFAULT TRUE,
    reachable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0)
);

CREATE TABLE services (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    fullname VARCHAR,
    manager_user_id INT REFERENCES fapse_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
	department_id INT REFERENCES departments(id) ON DELETE SET NULL ON UPDATE CASCADE,
    website_url VARCHAR DEFAULT NULL,
	website_ip_address VARCHAR DEFAULT NULL,
    registration_code VARCHAR DEFAULT NULL,
    comments VARCHAR DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) DEFAULT NULL
);

CREATE TABLE rooms (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    building_id INT REFERENCES buildings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    has_alarm BOOLEAN DEFAULT TRUE,
    comments VARCHAR DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) DEFAULT NULL
);

CREATE TABLE networks (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    plug_code VARCHAR DEFAULT NULL,
    firewall_zone VARCHAR DEFAULT NULL,
    gateway_ip VARCHAR DEFAULT NULL,
    subnet_mask VARCHAR DEFAULT NULL,
    comments VARCHAR DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0),
	building_id INT REFERENCES buildings(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE plugs (
    id SERIAL PRIMARY KEY,
    code VARCHAR,
    network_id INT REFERENCES networks(id) ON DELETE SET NULL ON UPDATE CASCADE,
    room_id INT REFERENCES rooms(id) ON DELETE SET NULL ON UPDATE CASCADE,
    service_id INT REFERENCES services(id) ON DELETE SET NULL ON UPDATE CASCADE,
	dns_name VARCHAR,
    ip_address VARCHAR,
    extern_plug_id VARCHAR,
    alias VARCHAR,
    network_branch VARCHAR,
    paid BOOLEAN DEFAULT TRUE,
    activation_date TIMESTAMP(0),
    comments VARCHAR,
    history TEXT,
    active BOOLEAN DEFAULT TRUE,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0),
	first_dns VARCHAR DEFAULT NULL,
    second_dns VARCHAR DEFAULT NULL,
	subnet_mask VARCHAR DEFAULT NULL,
	gateway_ip VARCHAR DEFAULT NULL
);

CREATE TABLE distributors (
    id SERIAL PRIMARY KEY,
    name VARCHAR,
    street VARCHAR,
    zip VARCHAR,
    city VARCHAR,
    country VARCHAR,
    fax VARCHAR,
    comments VARCHAR,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0)
);

CREATE TABLE distributor_contacts (
    id SERIAL PRIMARY KEY,
    distributor_id INT REFERENCES distributors(id) ON DELETE CASCADE ON UPDATE CASCADE,
    contact_type VARCHAR DEFAULT NULL,
    contact_name VARCHAR,
    phone_number VARCHAR,
    email VARCHAR,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP(0)
);

CREATE TABLE materials (
    id SERIAL PRIMARY KEY,
    ulg_mark VARCHAR,
    brand VARCHAR,
    model VARCHAR,
    type VARCHAR,
    identification_code VARCHAR,
	identification_number VARCHAR, --fapse xxxx
    serial_number VARCHAR,
    distributor_serial_number VARCHAR,
    domain VARCHAR,
    
    plug_id INT REFERENCES plugs(id) ON DELETE SET NULL ON UPDATE CASCADE,
	room_id INT REFERENCES rooms(id) ON DELETE SET NULL ON UPDATE CASCADE,
    price VARCHAR DEFAULT NULL,
    purchase_order VARCHAR,
    deployment_date TIMESTAMP(0),
    extern_netidentity_id VARCHAR,
    is_mobile BOOLEAN DEFAULT FALSE,
    comments VARCHAR,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0)
);

CREATE TABLE material_mac_addresses (
    id SERIAL PRIMARY KEY,
    material_id INT NOT NULL REFERENCES materials(id) ON DELETE CASCADE ON UPDATE CASCADE,
    mac_type VARCHAR DEFAULT NULL,
    mac_address VARCHAR DEFAULT NULL,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP(0)
);

CREATE TABLE departments_to_users (
    department_id INT NOT NULL REFERENCES departments(id) ON DELETE CASCADE ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services_to_users (
    service_id INT NOT NULL REFERENCES services(id) ON DELETE CASCADE ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE intervention_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR NOT NULL
);

CREATE TABLE intervention_subtypes (
    id SERIAL PRIMARY KEY,
    intervention_type_id INT NOT NULL REFERENCES intervention_types(id) ON UPDATE CASCADE,
    name VARCHAR NOT NULL,
    generic_solution VARCHAR DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE
);

CREATE TABLE interventions (
    id SERIAL PRIMARY KEY,
    
    request_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    request_ip VARCHAR DEFAULT NULL,

    requester_user_id INT REFERENCES fapse_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    intervention_target_user_id INT REFERENCES fapse_users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    locked_by_user_id INT REFERENCES fapse_users(id) ON DELETE SET NULL ON UPDATE CASCADE,

    intervention_subtype_id INT REFERENCES intervention_subtypes(id) ON DELETE SET NULL ON UPDATE CASCADE,
    intervention_type_id INT REFERENCES intervention_types(id) ON DELETE SET NULL ON UPDATE CASCADE,

    status VARCHAR DEFAULT NULL,
    description VARCHAR DEFAULT NULL,
    title VARCHAR DEFAULT NULL,

    material_id INT REFERENCES materials(id) ON UPDATE CASCADE,
    intervention_date TIMESTAMP(0) WITHOUT TIME ZONE,
	
	comments VARCHAR DEFAULT NULL,
	solution VARCHAR DEFAULT NULL
);

CREATE TABLE helpers_to_interventions (
    id SERIAL PRIMARY KEY,
    intervention_id INT NOT NULL REFERENCES interventions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE readers_to_interventions (
    id SERIAL PRIMARY KEY,
    intervention_id INT NOT NULL REFERENCES interventions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE materials_to_users (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    material_id INT NOT NULL REFERENCES materials(id) ON DELETE CASCADE ON UPDATE CASCADE,
	is_main_user BOOLEAN DEFAULT TRUE
);

CREATE TABLE materials_to_services (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES services(id) ON DELETE CASCADE ON UPDATE CASCADE,
    material_id INT NOT NULL REFERENCES materials(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE keywords (
    id SERIAL PRIMARY KEY,
    name VARCHAR NOT NULL
);

CREATE TABLE interventions_to_keywords (
    id SERIAL PRIMARY KEY,
    intervention_id INT NOT NULL REFERENCES interventions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    keyword_id INT NOT NULL REFERENCES keywords(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE computers (
    id SERIAL PRIMARY KEY,
    operating_system VARCHAR DEFAULT NULL,
    comments VARCHAR DEFAULT NULL,
    primary_wins_ip VARCHAR DEFAULT NULL,
    secondary_wins_ip VARCHAR DEFAULT NULL,
    material_id INT REFERENCES materials(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE softwares (
    id SERIAL PRIMARY KEY,
    name VARCHAR DEFAULT NULL,
    type VARCHAR DEFAULT NULL,
    comments VARCHAR DEFAULT NULL,
    visible BOOLEAN DEFAULT TRUE
);
-- DROP TABLE IF EXISTS intervention_messages CASCADE;
CREATE TABLE intervention_messages (
    id SERIAL PRIMARY KEY,
    intervention_id INT REFERENCES interventions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    author_user_id INT REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    message VARCHAR DEFAULT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

-- DROP TABLE IF EXISTS softwares_to_material CASCADE;

CREATE TABLE materials_to_softwares (
    id SERIAL PRIMARY KEY,
    material_id INT NOT NULL REFERENCES materials(id) ON DELETE CASCADE ON UPDATE CASCADE,
    software_id INT NOT NULL REFERENCES softwares(id) ON DELETE CASCADE ON UPDATE CASCADE,
    comments VARCHAR DEFAULT NULL,
    installation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

-- DROP TABLE IF EXISTS agenda CASCADE;
CREATE TABLE agenda (
    id SERIAL PRIMARY KEY,
    intervention_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    intervention_id INT NOT NULL REFERENCES interventions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    requester_user_id INT REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    assigned_user_id INT REFERENCES fapse_users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    comments VARCHAR DEFAULT NULL
);
