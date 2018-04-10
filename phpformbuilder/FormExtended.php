<?php
namespace phpformbuilder;

use phpformbuilder\Validator\Validator;

class FormExtended extends Form
{

    /* =============================================
        Complete contact form
    ============================================= */

    public function createContactForm()
    {
        $this->startFieldset('Please fill in this form to contact us');
        $this->addHtml('<p class="text-warning">All fields are required</p>');
        $this->groupInputs('user-name', 'user-first-name');
        $this->setCols(0, 6, 'xs');
        $this->addIcon('user-name', '<span class="glyphicon glyphicon-user"></span>', 'before');
        $this->addInput('text', 'user-name', '', '', 'required, placeholder=Name');
        $this->addIcon('user-first-name', '<span class="glyphicon glyphicon-user"></span>', 'before');
        $this->addInput('text', 'user-first-name', '', '', 'required, placeholder=First Name');
        $this->setCols(0, 12, 'xs');
        $this->addIcon('user-email', '<span class="glyphicon glyphicon-envelope"></span>', 'before');
        $this->addInput('email', 'user-email', '', '', 'required, placeholder=Email');
        $this->addIcon('user-phone', '<span class="glyphicon glyphicon-earphone"></span>', 'before');
        $this->addInput('text', 'user-phone', '', '', 'required, placeholder=Phone');
        $this->addTextarea('message', '', '', 'cols=30, rows=4, required, placeholder=Message');
        $this->addPlugin('word-character-count', '#message', 'default', array('%maxAuthorized%' => 100));
        $this->addCheckbox('newsletter', 'Suscribe to Newsletter', 1, 'checked=checked');
        $this->printCheckboxGroup('newsletter', '');
        $this->setCols(3, 9, 'sm');
        $this->addInput('text', 'captcha', '', 'Type the following characters :', 'size=15');
        $this->addPlugin('captcha', '#captcha');
        $this->addBtn('submit', 'submit-btn', 1, 'Send <span class="glyphicon glyphicon-envelope append"></span>', 'class=btn btn-success');
        $this->endFieldset();

        // Custom radio & checkbox css
        if($this->framework != 'material') {
            $this->addPlugin('nice-check', 'form', 'default', ['%skin%' => 'green']);
        }

        // jQuery validation
        $this->addPlugin('formvalidation', '#' . $this->form_ID);

        return $this;
    }

    /* =============================================
        Complete Foundation contact form
    ============================================= */

    public function createFoundationContactForm()
    {
        $this->startFieldset('Please fill in this form to contact us', 'class=fieldset');
        $this->addHtml('<p class="text-warning">All fields are required</p>');
        $this->groupInputs('user-name', 'user-first-name');
        $this->setCols(0, 6, 'xs');
        $this->addIcon('user-name', '<i class="input-group-label fi fi-torso"></i>', 'before');
        $this->addInput('text', 'user-name', '', '', 'class=input-group-field, required, placeholder=Name');
        $this->addIcon('user-first-name', '<i class="input-group-label fi fi-torso"></i>', 'before');
        $this->addInput('text', 'user-first-name', '', '', 'class=input-group-field, required, placeholder=First Name');
        $this->setCols(0, 12, 'xs');
        $this->addIcon('user-email', '<i class="input-group-label fi fi-mail"></i>', 'before');
        $this->addInput('email', 'user-email', '', '', 'class=input-group-field, required, placeholder=Email');
        $this->addIcon('user-phone', '<i class="input-group-label fi fi-telephone"></i>', 'before');
        $this->addInput('text', 'user-phone', '', '', 'class=input-group-field, required, placeholder=Phone');
        $this->addTextarea('message', '', '', 'cols=30, rows=4, required, placeholder=Message');
        $this->addPlugin('word-character-count', '#message', 'default', array('%maxAuthorized%' => 100));
        $this->addCheckbox('newsletter', 'Suscribe to Newsletter', 1, 'checked=checked');
        $this->printCheckboxGroup('newsletter', '');
        $this->setCols(3, 9, 'sm');
        $this->addInput('text', 'captcha', '', 'Type the following characters :', 'size=15');
        $this->addPlugin('captcha', '#captcha');
        $this->addBtn('submit', 'submit-btn', 1, 'Send <i class="fi fi-mail append"></i>', 'class=button success');
        $this->endFieldset();

        // Custom radio & checkbox css
        $this->addPlugin('nice-check', 'form', 'default', ['%skin%' => 'green']);

        // jQuery validation
        $this->addPlugin('formvalidation', '#' . $this->form_ID, 'foundation');

        return $this;
    }

    /* Contact form validation */

    public static function validateContactForm()
    {
        // create validator & auto-validate required fields
        $validator = self::validate('extended-contact-form');

        // additional validation
        $validator->maxLength(100)->validate('message');
        $validator->email()->validate('user-email');
        $validator->captcha('captcha')->validate('captcha');

        // check for errors

        if ($validator->hasErrors()) {
            $_SESSION['errors']['extended-contact-form'] = $validator->getAllErrors();

            return false;
        } else {

            return true;
        }
    }

    /* Contact form e-mail sending */

    public static function sendContactEmail($address, $form_ID)
    {

        // get hostname
        $hostname = 'phpformbuilder.pro';
        $email_config = array(
            'sender_email'    => 'contact@' . $hostname,
            'recipient_email' => $address,
            'subject'         => 'Message from ' . $hostname,
            'filter_values'   => $form_ID . ', captcha, submit-btn, captchaHash'
        );
        $sent_message = self::sendMail($email_config);
        self::clear($form_ID);

        return $sent_message;
    }

    /* =============================================
        Fields shorcuts and groups for users
    ============================================= */

    public function addAddress($i = '')
    {
        $index = $this->getIndex($i);
        $index_text = $this->getIndexText($i);
        $this->setCols(3, 9, 'md');
        $this->addTextarea('address' . $index, '', 'Address' . $index_text, 'required');
        $this->groupInputs('zip_code' . $index, 'city' . $index);
        $this->setCols(3, 4, 'md');
        $this->addInput('text', 'zip_code' . $index, '', 'Zip Code' . $index_text, 'required');
        $this->setCols(2, 3, 'md');
        $this->addInput('text', 'city' . $index, '', 'City' . $index_text, 'required');
        $this->setCols(3, 9, 'md');
        $this->addCountrySelect('country' . $index, 'Country' . $index_text, 'data-width=100%, required', ['flag_size' => 16, 'live_search' => true]);

        return $this;
    }

    public function addBirth($i = '')
    {
        $index = $this->getIndex($i);
        $index_text = $this->getIndexText($i);
        $this->setCols(3, 4, 'md');
        $this->groupInputs('birth_date' . $index, 'birth_zip_code' . $index);
        $this->addInput('text', 'birth_date' . $index, '', 'Birth Date' . $index_text, 'placeholder=click to open calendar');
        if ($this->framework == 'material') {
            $date_plugin = 'pickadate-material';
        } else {
            $date_plugin = 'pickadate';
        }
        $this->addPlugin($date_plugin, '#birth_date' . $index);
        $this->setCols(2, 3, 'md');
        $this->addInput('text', 'birth_zip_code' . $index, '', 'Birth Zip Code' . $index_text);
        $this->setCols(3, 4, 'md');
        $this->groupInputs('birth_city' . $index, 'birth_country' . $index);
        $this->addInput('text', 'birth_city' . $index, '', 'Birth  City' . $index_text);
        $this->setCols(2, 3, 'md');
        $this->addCountrySelect('birth_country' . $index, 'Birth Country' . $index_text, 'data-width=100%', ['flag_size' => 16, 'live_search' => true]);

        return $this;
    }

    public function addCivilitySelect($i = '')
    {
        $index = $this->getIndex($i);
        $index_text = $this->getIndexText($i);
        $this->addOption('civility' . $index, 'M.', 'M.');
        $this->addOption('civility' . $index, 'M<sup>rs</sup>', 'Mrs');
        $this->addOption('civility' . $index, 'M<sup>s</sup>', 'Ms');
        $this->addSelect('civility' . $index, 'Civility' . $index_text, 'class=selectpicker, required');

        return $this;
    }

    public function addContact($i = '')
    {
        $index = $this->getIndex($i);
        $index_text = $this->getIndexText($i);
        $this->groupInputs('phone' . $index, 'mobile_phone' . $index);
        $this->setCols(3, 4, 'md');
        $this->addInput('text', 'phone' . $index, '', 'Phone' . $index_text);
        $this->setCols(2, 3, 'md');
        $this->addInput('text', 'mobile_phone' . $index, '', 'Mobile' . $index_text, 'required');
        $this->setCols(3, 9, 'md');
        $this->addInput('email', 'email_professional' . $index, '', 'BuisnessE-mail' . $index_text, 'required');
        $this->addInput('email', 'email_private' . $index, '', 'Personal E-mail' . $index_text);

        return $this;
    }

    public function addIdentity($i = '')
    {
        $index = $this->getIndex($i);
        $index_text = $this->getIndexText($i);
        $this->groupInputs('civility' . $index, 'name' . $index);
        $this->setCols(3, 2, 'md');
        $this->addCivilitySelect($i);
        $this->setCols(2, 5, 'md');
        $this->addInput('text', 'name' . $index, '', 'Name' . $index_text, 'required');
        $this->setCols(3, 9, 'md');
        $this->startDependentFields('civility' . $index, 'Mrs');
        $this->addInput('text', 'maiden_name' . $index, '', 'Maiden Name' . $index_text);
        $this->endDependentFields();
        $this->groupInputs('firstnames' . $index, 'citizenship' . $index);
        $this->setCols(3, 4, 'md');
        $this->addInput('text', 'firstnames' . $index, '', 'Firstnames' . $index_text, 'required');
        $this->setCols(2, 3, 'md');
        $this->addInput('text', 'citizenship' . $index, '', 'Citizenship' . $index_text);

        return $this;
    }

    /* Submit buttons */

    public function addBackSubmit()
    {
        $this->setCols(0, 12);
        $this->addHtml('<p>&nbsp;</p>');
        $this->addBtn('submit', 'back-btn', 1, 'Back', 'class=btn btn-warning', 'submit_group');
        $this->addBtn('submit', 'submit-btn', 1, 'Submit', 'class=btn btn-success', 'submit_group');
        $this->printBtnGroup('submit_group');

        return $this;
    }

    public function addCancelSubmit()
    {
        $this->setCols(3, 9);
        $this->addHtml('<p>&nbsp;</p>');
        $cancel_class = 'btn btn-default';
        $submit_class = 'btn btn-success';
        if($this->framework == 'foundation') {
            $cancel_class = 'button warning';
            $submit_class = 'button primary';
        }
        $this->addBtn('button', 'cancel-btn', 1, 'Cancel', 'class=' . $cancel_class, 'submit_group');
        $this->addBtn('submit', 'submit-btn', 1, 'Submit', 'class=' . $submit_class, 'submit_group');
        $this->printBtnGroup('submit_group');

        return $this;
    }

    private function getIndex($i)
    {
        if ($i !== '') {
            return '-' . $i;
        }

        return false;
    }
    private function getIndexText($i)
    {
        if ($i !== '') {
            return ' ' . $i;
        }

        return false;
    }
}
