CREATE DATABASE PollenBase;

-- This contains all the information relating to the location that is being examined.
CREATE TABLE sample (
  sample_id VARCHAR (45),
  /*address VARCHAR (100),
  city VARCHAR (50),
  postal_code VARCHAR (30),
  state VARCHAR (50),*/
  address VARCHAR (50),
  age INT (11), --????
  -- Other info
  lycopodium INT (11),
  PRIMARY KEY (sample_id)
);

CREATE TABLE thingo (
  id VARCHAR (45) PRIMARY KEY NOT NULL,
  family VARCHAR (45) DEFAULT NULL,
  genus VARCHAR (45) DEFAULT NULL,
  species VARCHAR (45) DEFAULT NULL,
  poll_spore VARCHAR (10),
  grain_arrangement VARCHAR (45),
  grain_morphology VARCHAR (45),
  polar_axis_length VARCHAR (45),
  equatorial_axis_length VARCHAR (45),
  -- ***determined*** 6
  -- ***determined*** 7
  equatorial_shape VARCHAR (45),
  polar_shape VARCHAR (45),
  surface_pattern VARCHAR (45),
  wall_thickness VARCHAR (45),
  wall_evenness VARCHAR (45),
  exine_type VARCHAR (45),
  colporus VARCHAR (45),
  L_P VARCHAR (45),
  L_E VARCHAR (45),
  pore_protrusion VARCHAR (45),
  pore_shape_e VARCHAR (45),
  pore_shape_size VARCHAR (45),
  pore_shape_p VARCHAR (45),
  pore_margin VARCHAR (45),
  colpus_sulcus_length_c VARCHAR (45),
  colpus_sulcus_shape VARCHAR (45),
  -- ***determined*** 22
  colpus_sulcus_margin VARCHAR (45),
  apocolpium_width_e VARCHAR (45),
  -- ***determined*** 24.b
  trilete_scar_arm_length VARCHAR (45),
  trilete_scar_shape VARCHAR (45),
  p_sacci_size VARCHAR (45),
  e_sacci_size VARCHAR (45),
  tags VARCHAR (100)
)

/*-- This contains information relating to the individual pollen.
-- Currently using tags and attributes to describe
CREATE TABLE pollen (
  poll_id VARCHAR (45),
  name VARCHAR (50),
  arrangement VARCHAR(20),
  morphology VARCHAR(100),
  polar_axis_length DECIMAL(6,3),
  equatorial_axis_length DECIMAL(6,3),
  size DECIMAL(6,3),
  equatorial_shape_major VARCHAR(30),
  equatorial_shape_minor VARCHAR(30),



  image_folder VARCHAR (100),

  tags VARCHAR (300),
  -- Will have many other attributes redundant information is not necessary
  PRIMARY KEY (poll_id)
);*/

/*-- This contains information relating to the individual pollen.
-- Currently using tags and attributes to describe
CREATE TABLE spore (
  spore_id VARCHAR (45),
  image_folder VARCHAR (100),
  name VARCHAR (50),
  tags VARCHAR (300),
  -- Will have many other attributes redundant information is not necessary
  PRIMARY KEY (spore_id)
);*/

/*-- Images related to pollen
CREATE TABLE images (
  poll_id VARCHAR (45),
  image_path VARCHAR (200),
  image_order INT (11),
  PRIMARY KEY (poll_id, image_path)
)

-- Tags related to pollen
CREATE TABLE tags (
  poll_id VARCHAR (45),
  tag VARCHAR (300),
  PRIMARY KEY (poll_id, tag)
)*/

-- This contains the amount of pollen found at the specific location.
-- It also shows the date it was last found.
CREATE TABLE found_pollen (
  sample_id VARCHAR (45),
  poll_id VARCHAR (45),
  count INT (11) DEFAULT 0,
  last_update DATETIME DEFAULT NULL,
  PRIMARY KEY (sample_id, poll_id),
  FOREIGN KEY (sample_id) REFERENCES sample(sample_id),
  FOREIGN KEY (poll_id) REFERENCES pollen(poll_id)
);

/*-- This contains the amount of pollen found at the specific location.
-- It also shows the date it was last found.
CREATE TABLE found_spore (
  sample_id VARCHAR (45),
  spore_id VARCHAR (45),
  spore_count INT (11) DEFAULT 0,
  last_update DATETIME DEFAULT NULL,
  PRIMARY KEY (sample_id, spore_id),
  FOREIGN KEY (sample_id) REFERENCES sample(sample_id),
  FOREIGN KEY (spore_id) REFERENCES spore(spore_id)
);*/