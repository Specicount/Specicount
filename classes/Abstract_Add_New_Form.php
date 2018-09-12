<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 12/09/2018
 * Time: 9:35 PM
 */

namespace classes;
require_once "Abstract_Form.php";

abstract class Abstract_Add_New_Form extends Abstract_Form {
    protected function setFormName() {
        if ($_GET["edit"]) {
            $this->form_name = 'add-new-'.$this->form_type.'-edit';
        } else {
            $this->form_name = 'add-new-'.$this->form_type;
        }
    }

    public function setSqlTableName() {
        $this->table_name = $this->form_type . 's';
    }

    protected function setPageTitle() {
        if ($_GET["edit"]) {
            $id = end($_GET); // Last element of array is always the most specific id
            $this->page_title = "Edit ".ucwords($this->form_type).' '.$id;
        } else {
            $this->page_title = 'Add New '.ucwords($this->form_type);
        }
    }

}