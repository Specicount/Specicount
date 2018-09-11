/*
This is the table structure of the database (unfortunately might not be
fully updated since changes were made directly on server).
This probably won't need to be used since we can do a database
backup and restore both the table structure and their
respective data fairly easily.
Nice for reverse ER diagrams and crap :p

Important Note: Tables need to have their primary keys in order specificity for code to work properly
E.g. the 'samples' table should have sample_id, core_id, project_id in that order since sample_id is the most
specific thing that identifies a sample because it lives in a hierarchy of project->core->sample
 */

CREATE DATABASE BioBase;

-- USE BioBase; -- Uncomment this line if you want to execute this script in your SQL server

-- All the information we store about users
CREATE TABLE IF NOT EXISTS users (
  username VARCHAR(30) NOT NULL,
  `password` CHAR(128) NOT NULL,
  email VARCHAR(50) NOT NULL,
  first_name VARCHAR(30) NOT NULL,
  last_name VARCHAR(30) NOT NULL,
  institution VARCHAR(100),
  PRIMARY KEY (username)
);

-- This contains all the information relating to the project
CREATE TABLE IF NOT EXISTS projects (
  project_id VARCHAR (150) NOT NULL,
  biorealm VARCHAR (100) DEFAULT NULL,
  country VARCHAR (100) DEFAULT NULL,
  region VARCHAR (100) DEFAULT NULL,
  is_global BOOLEAN DEFAULT FALSE, -- determines whether any user can use this project's specimens in their own project
  PRIMARY KEY (project_id)
);

-- This contains all the information relating to the project
CREATE TABLE IF NOT EXISTS user_project_access (
  project_id VARCHAR (150) NOT NULL,
  username VARCHAR(30) NOT NULL,
  access_level ENUM('visitor','collaborator','admin') NOT NULL,
  PRIMARY KEY (project_id, username),
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE ON UPDATE CASCADE
);

-- This contains all the information relating to the core
CREATE TABLE IF NOT EXISTS cores (
  core_id VARCHAR (45) NOT NULL,
  project_id VARCHAR (150) NOT NULL,
  description VARCHAR (600),
  tags VARCHAR (200),
  PRIMARY KEY (core_id, project_id),
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- This contains all the information relating to the sample
CREATE TABLE IF NOT EXISTS samples (
  sample_id VARCHAR (45) NOT NULL,
  core_id VARCHAR (45) NOT NULL,
  project_id VARCHAR (150) NOT NULL,
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
  PRIMARY KEY (sample_id, core_id, project_id),
  FOREIGN KEY (core_id) REFERENCES cores(core_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS specimens (
  specimen_id VARCHAR (45) NOT NULL,
  project_id VARCHAR (150) NOT NULL,
  family VARCHAR (45) DEFAULT NULL,
  genus VARCHAR (45) DEFAULT NULL,
  species VARCHAR (45) DEFAULT NULL,
  poll_spore VARCHAR (10),
  grain_arrangement VARCHAR (45),
  grain_morphology VARCHAR (200),
  polar_axis_length DECIMAL(19,1),
  polar_axis_n INT (11),
  equatorial_axis_length DECIMAL(19,1),
  equatorial_axis_n INT (11),
  size VARCHAR (45),-- ***determined*** 6 (integer)
  equatorial_shape_major VARCHAR (45),-- ***determined*** 7 character
  equatorial_shape_minor VARCHAR (45) DEFAULT 'rounded',
  polar_shape VARCHAR (200),
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
  plant_function_type VARCHAR (200),
  morphology_notes VARCHAR (600),
  image_folder VARCHAR(200),
  primary_image VARCHAR(100),
  PRIMARY KEY (specimen_id, project_id),
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE

  -- tags VARCHAR (200) no tags at present
);

/*-- Images related to specimen
CREATE TABLE IF NOT EXISTS images (
  specimen_id VARCHAR (45) NOT NULL,
  image_path VARCHAR (200) NOT NULL,
  image_type VARCHAR (200),
  image_order INT (11),
  PRIMARY KEY (specimen_id, image_path),
  FOREIGN KEY (specimen_id) REFERENCES specimen(specimen_id)
);*/

/*-- Tags related to specimen
CREATE TABLE IF NOT EXISTS tags (
  poll_id VARCHAR (45),
  tag VARCHAR (300),
  PRIMARY KEY (poll_id, tag)
)*/

-- This contains the amount of pollen found at the specific location.
-- It also shows the date it was last found.
CREATE TABLE IF NOT EXISTS found_specimens (
  specimen_id VARCHAR (45) NOT NULL,
  specimen_project_id VARCHAR (45) NOT NULL, -- The project_id that identifies where the specimen is from
  sample_id VARCHAR (45) NOT NULL,
  core_id VARCHAR (45) NOT NULL,
  project_id VARCHAR (150) NOT NULL,         -- The project_id that identifies the sample the found_specimen is in
  `order` INT (11) DEFAULT NULL,
  `count` INT (11) DEFAULT 0,
  last_update DATETIME DEFAULT NULL,
  PRIMARY KEY (specimen_id, sample_id, core_id, project_id),
  FOREIGN KEY (specimen_project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (sample_id) REFERENCES samples (sample_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (core_id) REFERENCES cores (core_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (specimen_id) REFERENCES specimens(specimen_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- This contains the data for the concentration curve
CREATE TABLE IF NOT EXISTS concentration_curve (
  tally_count INT (11) NOT NULL, -- X axis
  unique_spec INT (11) NOT NULL, -- Y axis
  sample_id VARCHAR (45) NOT NULL,
  core_id VARCHAR (45) NOT NULL,
  project_id VARCHAR (150) NOT NULL,
  PRIMARY KEY (unique_spec, sample_id, core_id, project_id),
  FOREIGN KEY (sample_id) REFERENCES samples (sample_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (core_id) REFERENCES cores (core_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE ON UPDATE CASCADE
);