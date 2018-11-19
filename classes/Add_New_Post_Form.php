<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 12/09/2018
 * Time: 9:35 PM
 */

namespace classes;
use phpformbuilder\Form;
require_once "Post_Form.php";

class Add_New_Post_Form extends Post_Form {
    protected function setRequiredAccessLevelsForPost() {
        $this->post_required_access_levels = array("owner","admin","collaborator");
    }

    protected function setPageTitle() {
        if (isset($_GET["edit"])) {
            $id = end($_GET); // Last element of array is always the most specific id
            $this->page_title = "Edit ".ucwords($this->form_ID).' '.$id;
        } else {
            $this->page_title = 'Add New '.ucwords($this->form_ID);
            Form::clear($this->form_ID);
        }
    }
}