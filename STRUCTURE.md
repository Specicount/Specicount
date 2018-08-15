# General
The structure of this app is divided into two systems being the backend database and the front-end website.
Would be worth playing with the website to get a feel for it: [http://seprojgrp2b.anu.edu.au](http://seprojgrp2b.anu.edu.au).

# Database structure
The database structure can be found in tables.sql and denotes the structure of the database.

# Front-end structure
The front-end is very much based around a library called phpformbuilder.
All the documentation can be found here: [https://www.phpformbuilder.pro](https://www.phpformbuilder.pro).

Each of the add_***.php files primarily use phpformbuilder to create the forms required to add stuff to the database.
All of these files are also used to change entries in the database as well which is usually defined by the edit variable
in the URL (this is called a GET basically just "getting" crap from the URL).

When the form is submitted, what is called a POST is sent to the server which updates the database. All GET's and POST's
and what is shown on the website is done on the actual file.

Other than including crap from the phpformbuilder, the header, sidebar, css stuff, js stuff and any other garbage
is included according to what is needed. The header and sidebar is used for everything except the specimen_details.php
which is just a single table (better for printing).

Here are the pages (everything else is includes by these):
1. add_new_sample.php
2. add_new_core.php
3. add_new_project.php
4. add_new_specimen.php
5. index.php
6. sample.php
7. search_specimen.php
8. specimen_details.php

The *require_once* keyword is used to include files, so you can pretty easily see what includes what.

# More info
More information for crap can be found on Google Drive (hopefully is shared with you). Predominatly have a look at the
stuff in the *Software Documentation* folder.