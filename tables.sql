-- CREATE DATABASE BioBase;

CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL,
  email VARCHAR(50) NOT NULL,
  password CHAR(128) NOT NULL
);

-- This contains all the information relating to the project
CREATE TABLE IF NOT EXISTS projects (
  project_name VARCHAR (150) NOT NULL,
  biorealm VARCHAR (100) DEFAULT NULL,
  country VARCHAR (100) DEFAULT NULL,
  region VARCHAR (100) DEFAULT NULL,
  PRIMARY KEY (project_name)
);

-- This contains all the information relating to the core
CREATE TABLE IF NOT EXISTS cores (
  core_id VARCHAR (45) NOT NULL,
  project_name VARCHAR (150) NOT NULL,
  description VARCHAR (600),
  tags VARCHAR (200),
  PRIMARY KEY (core_id, project_name),
  FOREIGN KEY (project_name) REFERENCES projects(project_name)
);

-- This contains all the information relating to the sample
-- Not sure if we bother storing determined fields at this stage?????
CREATE TABLE IF NOT EXISTS samples (
  sample_id VARCHAR (45) NOT NULL,
  core_id VARCHAR (45) NOT NULL,
  analyst_first_name VARCHAR (60) NOT NULL,
  analyst_last_name VARCHAR (60) NOT NULL,
  start_date DATE NOT NULL,
  top_depth DECIMAL(19,2),
  bottom_depth DECIMAL(19,2),
  mid_depth DECIMAL(19,2),
  modelled_age INT (11), -- ????
  lycopodium INT (11),
  charcoal INT (11),
  last_edit DATE NOT NULL,
  -- tags VARCHAR (200), not tags at present
  PRIMARY KEY (sample_id, core_id),
  FOREIGN KEY (core_id) REFERENCES cores(core_id)
);

CREATE TABLE IF NOT EXISTS specimen (
  spec_id VARCHAR (45) PRIMARY KEY NOT NULL,
  family VARCHAR (45) DEFAULT NULL,
  genus VARCHAR (45) DEFAULT NULL,
  species VARCHAR (45) DEFAULT NULL,
  poll_spore VARCHAR (10),
  grain_arrangement VARCHAR (45),
  grain_morphology VARCHAR (45),
  polar_axis_length DECIMAL(19,1),
  equatorial_axis_length DECIMAL(19,1),
  size VARCHAR (45),-- ***determined*** 6 (integer)
  equatorial_shape_major VARCHAR (45),-- ***determined*** 7 character
  equatorial_shape_minor VARCHAR (45) DEFAULT 'rounded',
  polar_shape VARCHAR (45),
  surface_pattern VARCHAR (60),
  wall_thickness DECIMAL(19,1),
  wall_evenness VARCHAR (45),
  exine_type VARCHAR (45),
  colporus VARCHAR (45),
  L_P VARCHAR (45),
  L_E VARCHAR (45),
  pore_protrusion VARCHAR (45),
  pore_shape_e VARCHAR (45),
  pore_shape_size VARCHAR (45), -- depends on pore_shape_e
  pore_shape_p VARCHAR (45),
  pore_margin VARCHAR (45),
  colpus_sulcus_length_c DECIMAL(19,1),
  colpus_sulcus_shape VARCHAR (45),
  -- ***determined*** 22
  colpus_sulcus_margin VARCHAR (45),
  apocolpium_width_e DECIMAL(19,1),
  -- ***determined*** 24.b
  trilete_scar_arm_length DECIMAL(19,1),
  trilete_scar_shape VARCHAR (45),
  p_sacci_size DECIMAL(19,1),
  e_sacci_size DECIMAL(19,1),
  morphology_notes VARCHAR (600),
  image_folder VARCHAR(200),
  primary_image VARCHAR(100)
  -- tags VARCHAR (200) no tags at present
);

/*-- Images related to specimen
CREATE TABLE IF NOT EXISTS images (
  spec_id VARCHAR (45) NOT NULL,
  image_path VARCHAR (200) NOT NULL,
  image_type VARCHAR (200),
  image_order INT (11),
  PRIMARY KEY (spec_id, image_path),
  FOREIGN KEY (spec_id) REFERENCES specimen(spec_id)
);*/

/*-- Tags related to specimen
CREATE TABLE IF NOT EXISTS tags (
  poll_id VARCHAR (45),
  tag VARCHAR (300),
  PRIMARY KEY (poll_id, tag)
)*/

-- This contains the amount of pollen found at the specific location.
-- It also shows the date it was last found.
CREATE TABLE IF NOT EXISTS found_specimen (
  sample_id VARCHAR (45) NOT NULL,
  spec_id VARCHAR (45) NOT NULL,
  count INT (11) DEFAULT 0,
  last_update DATETIME DEFAULT NULL,
  PRIMARY KEY (sample_id, spec_id),
  FOREIGN KEY (sample_id) REFERENCES samples (sample_id),
  FOREIGN KEY (spec_id) REFERENCES specimen(spec_id)
);