<?php

namespace phpformbuilder;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use phpformbuilder\Validator\Validator;

/**
 * Form Class
 *
 * @version 3.4
 * @author Gilles Migliori - gilles.migliori@gmail.com
 *
 */

class Form
{
    /* general */

    protected $form_ID             = '';
    protected $form_attr           = '';
    protected $action              = '';
    protected $add_get_vars        = true;
    protected static $instances;

    /*  bs3_options, bs4_options, material_options, foundation_options :
    *   wrappers and classes styled with Bootstrap 3 or Material Design
    *   each can be individually updated with $form->setOptions();
    */

    protected $bs3_options = array(
        'ajax'                         => false,
        'formInlineClass'              => 'form-inline',
        'formHorizontalClass'          => 'form-horizontal',
        'formVerticalClass'            => '',
        'elementsWrapper'              => '<div class="form-group"></div>',
        'checkboxWrapper'              => '<div class="checkbox"></div>',
        'radioWrapper'                 => '<div class="radio"></div>',
        'helperWrapper'                => '<span class="help-block"></span>',
        'buttonWrapper'                => '<div class="form-group"></div>',
        'centeredButtonWrapper'        => '<div class="form-group text-center"></div>',
        'centerButtons'                => false,
        'wrapElementsIntoLabels'       => false,
        'wrapCheckboxesIntoLabels'     => true,
        'wrapRadiobtnsIntoLabels'      => true,
        'elementsClass'                => 'form-control',
        'wrapperErrorClass'            => 'has-error',
        'elementsErrorClass'           => '',
        'textErrorClass'               => 'text-danger',
        'verticalLabelWrapper'         => false,
        'verticalLabelClass'           => '',
        'verticalCheckboxLabelClass'   => '',
        'verticalRadioLabelClass'      => '',
        'horizontalLabelWrapper'       => false,
        'horizontalLabelClass'         => 'control-label',
        'horizontalLabelCol'           => 'col-sm-4',
        'horizontalOffsetCol'          => 'col-sm-offset-4',
        'horizontalElementCol'         => 'col-sm-8',
        'inlineCheckboxLabelClass'     => 'checkbox-inline',
        'inlineRadioLabelClass'        => 'radio-inline',
        'inlineCheckboxWrapper'        => '',
        'inlineRadioWrapper'           => '',
        'inputGroupAddonClass'         => 'input-group-addon',
        'btnGroupClass'                => 'btn-group',
        'requiredMark'                 => '<sup class="text-danger">* </sup>',
        'openDomReady'                 => 'jQuery(document).ready(function($) {',
        'closeDomReady'                => '});'
    );
    /*
        elementsWrapper:
            vertical forms:     form-group      (default)
            horizontal forms:   form-group row  (default)
    */
    protected $bs4_options = array(
        'ajax'                         => false,
        'elementsWrapper'              => '<div class="form-group"></div>',
        'formInlineClass'              => 'form-inline',
        'formHorizontalClass'          => '',
        'formVerticalClass'            => '',
        'checkboxWrapper'              => '<div class="form-check"></div>',
        'radioWrapper'                 => '<div class="form-check"></div>',
        'helperWrapper'                => '<small class="form-text text-muted"></small>',
        'buttonWrapper'                => '<div class="form-group"></div>',
        'centeredButtonWrapper'        => '<div class="form-group text-center"></div>',
        'centerButtons'                => false,
        'wrapElementsIntoLabels'       => false,
        'wrapCheckboxesIntoLabels'     => false,
        'wrapRadiobtnsIntoLabels'      => false,
        'elementsClass'                => 'form-control',
        'wrapperErrorClass'            => '',
        'elementsErrorClass'           => 'is-invalid',
        'textErrorClass'               => 'invalid-feedback',
        'verticalLabelWrapper'         => false,
        'verticalLabelClass'           => 'form-control-label',
        'verticalCheckboxLabelClass'   => 'form-check-label',
        'verticalRadioLabelClass'      => 'form-check-label',
        'horizontalLabelWrapper'       => false,
        'horizontalLabelClass'         => 'col-form-label',
        'horizontalLabelCol'           => 'col-sm-4',
        'horizontalOffsetCol'          => '',
        'horizontalElementCol'         => 'col-sm-8',
        'inlineCheckboxLabelClass'     => 'form-check-label',
        'inlineRadioLabelClass'        => 'form-check-label',
        'inlineCheckboxWrapper'        => '<div class="form-check form-check-inline"></div>',
        'inlineRadioWrapper'           => '<div class="form-check form-check-inline"></div>',
        'inputGroupAddonClass'         => '',
        'btnGroupClass'                => 'btn-group',
        'requiredMark'                 => '<sup class="text-danger">* </sup>',
        'openDomReady'                 => 'jQuery(document).ready(function($) {',
        'closeDomReady'                => '});'
    );

    protected $material_options = array(
        'ajax'                         => false,
        'formInlineClass'              => 'form-inline',
        'formHorizontalClass'          => 'form-horizontal',
        'formVerticalClass'            => '',
        'elementsWrapper'              => '<div class="input-field row"></div>',
        'checkboxWrapper'              => '<div class="checkbox"></div>',
        'radioWrapper'                 => '<div class="radio"></div>',
        'helperWrapper'                => '<span class="help-block"></span>',
        'buttonWrapper'                => '<div class="form-group"></div>',
        'centeredButtonWrapper'        => '<div class="form-group text-center"></div>',
        'centerButtons'                => false,
        'wrapElementsIntoLabels'       => false,
        'wrapCheckboxesIntoLabels'     => false,
        'wrapRadiobtnsIntoLabels'      => false,
        'elementsClass'                => 'form-control',
        'wrapperErrorClass'            => 'has-error',
        'elementsErrorClass'           => '',
        'textErrorClass'               => 'text-danger',
        'verticalLabelWrapper'         => false,
        'verticalLabelClass'           => '',
        'verticalCheckboxLabelClass'   => '',
        'verticalRadioLabelClass'      => '',
        'horizontalLabelWrapper'       => false,
        'horizontalLabelClass'         => '',
        'horizontalLabelCol'           => 'col-sm-4',
        'horizontalOffsetCol'          => 'col-sm-offset-4',
        'horizontalElementCol'         => 'col-sm-8',
        'inlineCheckboxLabelClass'     => 'checkbox-inline',
        'inlineRadioLabelClass'        => 'radio-inline',
        'inlineCheckboxWrapper'        => '',
        'inlineRadioWrapper'           => '',
        'inputGroupAddonClass'         => '',
        'btnGroupClass'                => 'btn-group',
        'requiredMark'                 => '<sup class="text-danger">* </sup>',
        'openDomReady'                 => 'jQuery(document).ready(function($) {',
        'closeDomReady'                => '});'
    );

    protected $foundation_options = array(
        'ajax'                         => false,
        'formInlineClass'              => '',
        'formHorizontalClass'          => '',
        'formVerticalClass'            => '',
        'elementsWrapper'              => '<div class="row"></div>',
        'checkboxWrapper'              => '<div class="foundation-checkbox"></div>',
        'radioWrapper'                 => '<div class="foundation-radio"></div>',
        'helperWrapper'                => '<p class="help-text"></p>',
        'buttonWrapper'                => '<div class="row"></div>',
        'centeredButtonWrapper'        => '<div class="row text-center"></div>',
        'centerButtons'                => false,
        'wrapElementsIntoLabels'       => false,
        'wrapCheckboxesIntoLabels'     => false,
        'wrapRadiobtnsIntoLabels'      => false,
        'elementsClass'                => '',
        'wrapperErrorClass'            => '',
        'elementsErrorClass'           => 'is-invalid-input',
        'textErrorClass'               => 'form-error is-visible',
        'verticalLabelWrapper'         => true,
        'verticalLabelClass'           => 'small-12 columns',
        'verticalCheckboxLabelClass'   => '',
        'verticalRadioLabelClass'      => '',
        'horizontalLabelWrapper'       => true,
        'horizontalLabelClass'         => '',
        'horizontalLabelCol'           => 'small-4 columns',
        'horizontalOffsetCol'          => '',
        'horizontalElementCol'         => 'small-8 columns',
        'inlineCheckboxLabelClass'     => 'checkbox-inline',
        'inlineRadioLabelClass'        => 'radio-inline',
        'inlineCheckboxWrapper'        => '',
        'inlineRadioWrapper'           => '',
        'inputGroupAddonClass'         => '',
        'btnGroupClass'                => 'button-group',
        'requiredMark'                 => '<sup style="color:red">* </sup>',
        'openDomReady'                 => 'jQuery(document).ready(function($) {',
        'closeDomReady'                => '});'
    );

    /* error fields + messages */

    protected $errors   = array();
    protected $error_fields = array();

    /* layout */

    protected $layout; /* horizontal | vertical | inline */

    /* init (no need to change anything here) */

    protected $btn_cancel             = '';
    protected $btn_reset              = '';
    protected $btn_submit             = '';
    protected $checkbox               = array();
    protected $checkbox_end_wrapper   = '';
    protected $checkbox_start_wrapper = '';
    protected $current_dependent_data = array();
    protected $elements_end_wrapper   = '';
    protected $elements_start_wrapper = '';
    protected $end_fieldset           = '';
    protected $form_end_wrapper       = '';
    protected $form_start_wrapper     = '';
    public $framework              = '';
    protected $group_name             = array();
    protected $has_file               = false;
    protected $hasDependentField      = false;
    protected $has_recaptcha_error    = false;
    protected $helper_end_wrapper     = '';
    protected $helper_start_wrapper   = '';
    protected $hidden_fields          = '';
    protected $html_element_content   = array(); // ex : $this->html_element_content[$element_name][$pos][] = $html
    protected $input_grouped          = array();
    protected $input_wrapper          = array();
    protected $method                 = 'POST';
    protected $option                 = array();
    protected $optiongroup_ID         = array();
    protected $plugins_path           = '';
    public $plugins_url            = '';
    protected $radio                  = array();
    protected $radio_end_wrapper      = '';
    protected $radio_start_wrapper    = '';
    protected $recaptcha_js_callback  = '';
    protected $recaptcha_error_text   = '';
    protected $token                  = '';
    protected $txt                    = '';
    public $error_msg                 = '';
    public $html                      = '';

    /* plugins */

    protected $js_plugins          = array();

    protected $css_includes       = array();
    protected $js_includes        = array();
    protected $js_code            = '';
    protected $fileupload_js_code = '';

    /**
     * Defines the layout (horizontal | vertical | inline).
     * Default is 'horizontal'
     * Clears values from session if self::clear has been called before
     * Catches posted errors
     * Adds hidden field with form ID
     * Sets elements wrappers
     *
     * @param string $form_ID   The ID of the form
     * @param string $layout    (Optional) Can be 'horizontal', 'vertical' or 'inline'
     * @param string $attr      (Optional) Can be any HTML input attribute or js event EXCEPT class
     *                          (class is defined in layout param).
     *                          attributes must be listed separated with commas.
     *                          Example : novalidate,onclick=alert(\'clicked\');
     * @param string $framework (Optional) bs3 | material | foundation (Bootstrap 3, Material design or Foundation)
     * @return $this
     */
    public function __construct($form_ID, $layout = 'horizontal', $attr = '', $framework = 'bs3')
    {
        if (Form::$instances === null) {
            Form::$instances = array(
                'css_files' => array(),
                'js_files' => array()
            );
        }
        $this->form_ID     = $form_ID;
        $this->form_attr   = $attr;
        $this->layout      = $layout;
        $this->action      = '';
        $this->framework   = $framework;
        $this->token       = $this->generateToken();
        $this->setPluginsUrl();

        // set framework options
        if ($framework == 'bs3') {
            $this->options = $this->bs3_options;
        } elseif ($framework == 'bs4') {
            $this->options = $this->bs4_options;
            if ($layout == 'horizontal') {
                $this->options['elementsWrapper']       = '<div class="form-group row justify-content-end"></div>';
                $this->options['checkboxWrapper']       = '<div class="form-check justify-content-end"></div>';
                $this->options['radioWrapper']          = '<div class="form-check justify-content-end"></div>';
                $this->options['buttonWrapper']         = '<div class="form-group row justify-content-end"></div>';
                $this->options['centeredButtonWrapper'] = '<div class="form-group row text-center justify-content-end"></div>';
            }
        } elseif ($framework == 'material') {
            $this->options = $this->material_options;
            if ($layout !== 'horizontal') {
                // remove row class from elements wrapper
                $this->setOptions(array('elementsWrapper' => '<div class="input-field"></div>'));
            }

            // include Materialize plugin
            $this->addPlugin('materialize', '#' . $this->form_ID);
        } elseif ($framework == 'foundation') {
            $this->options = $this->foundation_options;
            if ($layout !== 'horizontal') {
                $this->setOptions(
                    array(
                        'wrapElementsIntoLabels' => true,
                        'horizontalElementCol'   => 'small-12 columns'
                    )
                );
            }
        }
        if (!isset($_SESSION['clear_form'][$form_ID])) {
            $_SESSION['clear_form'][$form_ID] = false;
        } elseif ($_SESSION['clear_form'][$form_ID] === true) {
            $_SESSION['clear_form'][$form_ID] = false; // reset after clearing
        } elseif (isset($_POST[$form_ID])) {
            self::registerValues($form_ID);
        }
        if (isset($_SESSION['errors'][$form_ID])) {
            $this->registerErrors();
            unset($_SESSION['errors'][$form_ID]);
        }
        $this->checkbox_end_wrapper          = $this->defineWrapper($this->options['checkboxWrapper'], 'end');
        $this->checkbox_start_wrapper        = $this->defineWrapper($this->options['checkboxWrapper'], 'start');
        $this->elements_end_wrapper          = $this->defineWrapper($this->options['elementsWrapper'], 'end');
        $this->elements_start_wrapper        = $this->defineWrapper($this->options['elementsWrapper'], 'start');
        $this->helper_end_wrapper            = $this->defineWrapper($this->options['helperWrapper'], 'end');
        $this->helper_start_wrapper          = $this->defineWrapper($this->options['helperWrapper'], 'start');
        $this->inline_checkbox_end_wrapper   = $this->defineWrapper($this->options['inlineCheckboxWrapper'], 'end');
        $this->inline_checkbox_start_wrapper = $this->defineWrapper($this->options['inlineCheckboxWrapper'], 'start');
        $this->inline_radio_end_wrapper      = $this->defineWrapper($this->options['inlineRadioWrapper'], 'end');
        $this->inline_radio_start_wrapper    = $this->defineWrapper($this->options['inlineRadioWrapper'], 'start');
        $this->radio_end_wrapper             = $this->defineWrapper($this->options['radioWrapper'], 'end');
        $this->radio_start_wrapper           = $this->defineWrapper($this->options['radioWrapper'], 'start');
        if ($this->options['centerButtons'] === true) {
            $this->button_start_wrapper   = $this->defineWrapper($this->options['centeredButtonWrapper'], 'start');
            $this->button_end_wrapper     = $this->defineWrapper($this->options['centeredButtonWrapper'], 'end');
        } else {
            $this->button_start_wrapper   = $this->defineWrapper($this->options['buttonWrapper'], 'start');
            $this->button_end_wrapper     = $this->defineWrapper($this->options['buttonWrapper'], 'end');
        }
        $this->addInput('hidden', $form_ID . '-token', $this->token);
        $this->addInput('hidden', $form_ID, true);

        return $this;
    }

    /**
     * set sending method
     * @param string $method POST|GET
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
    * Redefines form action
    *
    * @param boolean $add_get_vars (Optional) If $add_get_vars is set to false,
    *                              url vars will be removed from destination page.
    *                              Example : www.myUrl.php?var=value => www.myUrl.php
    *
    * @return $this
    */
    public function setAction($url, $add_get_vars = true)
    {
        $this->action = $url;
        $this->add_get_vars = $add_get_vars;

        return $this;
    }

    /**
     * Sets form layout options to match your framework
     *
     * @param array $user_options (Optional) An associative array containing the
     *                            options names as keys and values as data.
     * @return $this
     */
    public function setOptions($user_options = array())
    {
        $formClassOptions = array('ajax', 'formInlineClass', 'formHorizontalClass', 'formVerticalClass', 'elementsWrapper', 'checkboxWrapper', 'radioWrapper', 'helperWrapper', 'buttonWrapper', 'centeredButtonWrapper', 'centerButtons', 'wrapElementsIntoLabels', 'wrapCheckboxesIntoLabels', 'wrapRadiobtnsIntoLabels', 'elementsClass', 'wrapperErrorClass', 'elementsErrorClass', 'textErrorClass', 'verticalLabelWrapper', 'verticalLabelClass', 'verticalCheckboxLabelClass', 'verticalRadioLabelClass', 'horizontalLabelWrapper', 'horizontalLabelClass', 'horizontalLabelCol', 'horizontalOffsetCol', 'horizontalElementCol', 'inlineCheckboxLabelClass', 'inlineRadioLabelClass', 'inlineCheckboxWrapper', 'inlineRadioWrapper', 'inputGroupAddonClass', 'btnGroupClass', 'requiredMark', 'openDomReady', 'closeDomReady');
        foreach ($user_options as $key => $value) {
            if (in_array($key, $formClassOptions)) {
                $this->options[$key] = $value;

                /* redefining starting & ending wrappers */

                if ($key == 'elementsWrapper') {
                    $this->elements_start_wrapper = $this->defineWrapper($this->options['elementsWrapper'], 'start');
                    $this->elements_end_wrapper   = $this->defineWrapper($this->options['elementsWrapper'], 'end');
                } elseif ($key == 'checkboxWrapper') {
                    $this->checkbox_start_wrapper = $this->defineWrapper($this->options['checkboxWrapper'], 'start');
                    $this->checkbox_end_wrapper   = $this->defineWrapper($this->options['checkboxWrapper'], 'end');
                } elseif ($key == 'inlineCheckboxWrapper') {
                    $this->inline_checkbox_start_wrapper = $this->defineWrapper($this->options['inlineCheckboxWrapper'], 'start');
                    $this->inline_checkbox_end_wrapper   = $this->defineWrapper($this->options['inlineCheckboxWrapper'], 'end');
                } elseif ($key == 'inlineRadioWrapper') {
                    $this->inline_radio_start_wrapper = $this->defineWrapper($this->options['inlineRadioWrapper'], 'start');
                    $this->inline_radio_end_wrapper   = $this->defineWrapper($this->options['inlineRadioWrapper'], 'end');
                } elseif ($key == 'helperWrapper') {
                    $this->helper_start_wrapper = $this->defineWrapper($this->options['helperWrapper'], 'start');
                    $this->helper_end_wrapper   = $this->defineWrapper($this->options['helperWrapper'], 'end');
                } elseif ($key == 'radioWrapper') {
                    $this->radio_start_wrapper = $this->defineWrapper($this->options['radioWrapper'], 'start');
                    $this->radio_end_wrapper   = $this->defineWrapper($this->options['radioWrapper'], 'end');
                } elseif ($key == 'buttonWrapper') {
                    $this->options['centerButtons'] = false;
                    $this->button_start_wrapper = $this->defineWrapper($this->options['buttonWrapper'], 'start');
                    $this->button_end_wrapper   = $this->defineWrapper($this->options['buttonWrapper'], 'end');
                } elseif ($key == 'centeredButtonWrapper') {
                    $this->options['centerButtons'] = true;
                    $this->button_start_wrapper = $this->defineWrapper($this->options['centeredButtonWrapper'], 'start');
                    $this->button_end_wrapper   = $this->defineWrapper($this->options['centeredButtonWrapper'], 'end');
                } elseif ($key == 'centerButtons') {
                    if ($value === true) {
                        $this->button_start_wrapper   = $this->defineWrapper($this->options['centeredButtonWrapper'], 'start');
                        $this->button_end_wrapper     = $this->defineWrapper($this->options['centeredButtonWrapper'], 'end');
                    } else {
                        $this->button_start_wrapper   = $this->defineWrapper($this->options['buttonWrapper'], 'start');
                        $this->button_end_wrapper     = $this->defineWrapper($this->options['buttonWrapper'], 'end');
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Shortcut for labels & cols options
     * @param number $label_col_number number of columns for label
     * @param number $field_col_number number of columns for fields
     * @param string $breakpoint Bootstrap's breakpoints : xs | sm | md |lg
     * @return $this
     */
    public function setCols($label_col_number, $field_col_number, $breakpoint = 'sm')
    {
        if ($this->framework == 'bs3' || $this->framework == 'bs4' || $this->framework == 'material' && $this->layout == 'horizontal') {
            $label_col          = '';
            $label_offset_col   = '';
            $element_col        = '';
            $options            = array();
            if (!empty($label_col_number) && $label_col_number > 0) {
                $label_col        = 'col-' . $breakpoint . '-' . $label_col_number;
                $label_offset_col = 'col-' . $breakpoint . '-offset-' . $label_col_number;
            }
            if (!empty($field_col_number) && $field_col_number > 0) {
                $element_col = 'col-' . $breakpoint . '-' . $field_col_number;
            }
            if ($this->framework == 'bs3') {
                // Bootstrap 3
                $options = array(
                    'horizontalLabelCol'   => $label_col,
                    'horizontalOffsetCol'  => $label_offset_col,
                    'horizontalElementCol' => $element_col
                );
            } elseif ($this->framework == 'bs4') {
                // Bootstrap 4

                // if no breakpoint (ie: col-6)
                $label_col = str_replace('--', '-', $label_col);
                $element_col = str_replace('--', '-', $element_col);

                // if negative, => "col" without number (auto-width)
                if ($label_col_number < 0) {
                    $label_col = 'col';
                    if (!empty($breakpoint)) {
                        $label_col .= '-' . $breakpoint;
                    }
                }
                if ($field_col_number < 0) {
                    $element_col = 'col';
                    if (!empty($breakpoint)) {
                        $element_col .= '-' . $breakpoint;
                    }
                }
                $options = array(
                    'horizontalLabelCol'   => $label_col,
                    'horizontalOffsetCol'  => '',
                    'horizontalElementCol' => $element_col
                );
            } elseif ($this->framework == 'material') {
                // Material Design
                if (!empty($label_col_number) && $label_col_number > 0) {
                    // Normal input with label in front
                    // elementsWrapper with 2 col divs inside for label & field
                    $options = array(
                        'horizontalLabelCol'   => $label_col,
                        'horizontalOffsetCol'  => $label_offset_col,
                        'horizontalElementCol' => $element_col
                    );
                } else {
                    // Material input-field with label inside
                    // elementsWrapper with row + col class, label & field directly inside
                    $options = array(
                        'horizontalLabelCol'   => '',
                        'horizontalOffsetCol'  => '',
                        'horizontalElementCol' => $element_col
                    );
                }
            }
        } elseif ($this->framework == 'foundation') {
            $label_col   = '';
            $element_col = '';
            $options     = array();
            $foundation_breakpoints = array(
                'xs' => 'small',
                'sm' => 'small',
                'md' => 'medium',
                'lg' => 'large'
            );
            $breakpoint = $foundation_breakpoints[$breakpoint];
            if ($this->layout == 'horizontal') {
                if (!empty($label_col_number) && $label_col_number > 0) {
                    $label_col        = $breakpoint . '-' . $label_col_number . ' columns';
                }
                if (!empty($field_col_number) && $field_col_number > 0) {
                    $element_col = $breakpoint . '-' . $field_col_number . ' columns';
                }
                $options = array(
                    'horizontalLabelCol'   => $label_col,
                    'horizontalElementCol' => $element_col
                );
            } elseif ($this->layout == 'vertical') {
                if (!empty($label_col_number) && $label_col_number > 0) {
                    $label_col        = $breakpoint . '-' . $label_col_number . ' columns';
                }
                $options = array(
                    'verticalLabelClass' => $label_col
                );
            }
        }
        $this->setOptions($options);
        $this->elements_start_wrapper = $this->defineWrapper($this->options['elementsWrapper'], 'start');
        $this->elements_end_wrapper   = $this->defineWrapper($this->options['elementsWrapper'], 'end');

        return $this;
    }

    /**
     * Shortcut to add element helper text
     *
     * @param string $helper_text    The helper text or html to add.
     * @param string $element_name   the helper text will be inserted just after the element.
     * @return $this
     */
    public function addHelper($helper_text, $element_name)
    {
        if (!isset($this->html_element_content[$element_name])) {
            $this->html_element_content[$element_name] = array('before', 'after');
        }
        $this->html_element_content[$element_name]['after'][] = $this->helper_start_wrapper . $helper_text . $this->helper_end_wrapper;

        return $this;
    }

    /**
     * @param  boolean $center
     * @return $this
     */
    public function centerButtons($center)
    {
        $this->setOptions(array('centerButtons' => $center));

        return $this;
    }

    /**
     * Adds HTML code at any place of the form
     *
     * @param string $html         The html code to add.
     * @param string $element_name (Optional) If not empty, the html code will be inserted
     *                             just before or after the element.
     * @param string $pos          (Optional) If $element_name is not empty, defines the position
     *                             of the inserted html code.
     *                             Values can be 'before' or 'after'.
     * @return $this
     */
    public function addHtml($html, $element_name = '', $pos = 'after')
    {
        if (!empty($element_name)) {
            if (!isset($this->html_element_content[$element_name])) {
                $this->html_element_content[$element_name] = array('before', 'after');
            }
            $this->html_element_content[$element_name][$pos][] = $html;
        } else {
            $this->html .= $html;
        }

        return $this;
    }

    /**
     * Wraps the element with html code.
     *
     * @param string $html         The html code to wrap the element with.
     *                             The html tag must be opened and closed.
     *                             Example : <div class="my-class"></div>
     * @param string $element_name The form element to wrap.
     * @return $this
     */
    public function addInputWrapper($html, $element_name)
    {
        $this->input_wrapper[$element_name] = $html;

        return $this;
    }

    /*=================================
    Elements
    =================================*/

    /**
     * Adds input to the form
     *
     * @param string $type  Accepts all input html5 types except checkbox and radio :
     *                      button, color, date, datetime, datetime-local,
     *                      email, file, hidden, image, month, number, password,
     *                      range, reset, search, submit, tel, text, time, url, week
     * @param string $name  The input name
     * @param string $value (Optional) The input default value
     * @param string $label (Optional) The input label
     * @param string $attr  (Optional) Can be any HTML input attribute or js event.
     *                      attributes must be listed separated with commas.
     *                      If you don't specify any ID as attr, the ID will be the name of the input.
     *                      Example : class=my-class,placeholder=My Text,onclick=alert(\'clicked\');
     * @return $this
     */
    public function addInput($type, $name, $value = '', $label = '', $attr = '')
    {
        if ($type == 'file') {
            $this->has_file = true;
        }
        $attr          = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values  = $this->getID($name, $attr); // if $attr contains no ID, field ID will be $name.
        $id            = $array_values['id'];
        $attr          = $array_values['attributs']; // if $attr contains an ID, we remove it.
        if ($type != 'hidden') {
            $attr = $this->addElementClass($name, $attr);
        }
        $value         = $this->getValue($name, $value);
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        if ($type == 'hidden') {
            $this->hidden_fields .= '<input name="' . $name . '" type="hidden" value="' . $value . '" ' . $attr . '>';
        } else {
            // form-group wrapper
            $start_wrapper = $this->setInputGroup($name, 'start', 'elementsWrapper');
            $start_wrapper .= $this->addErrorWrapper($name, 'start');

            // label
            if ($this->options['verticalLabelWrapper'] === true) {
                $start_label  .= $this->getLabelCol('start');
            }
            if (!empty($label)) {
                $start_label .= '<label for="' . $id . '"' . $this->getLabelClass() . '>' . $this->getRequired($label, $attr);
                $end_label = '</label>';
            }
            if ($this->options['verticalLabelWrapper'] === true) {
                $end_label   .= $this->getLabelCol('end');
            }

            // input
            $start_col .= $this->getElementCol('start', 'input', $label); // col-sm-8
            $element .= $this->getErrorInputWrapper($name, $label, 'start'); // has-error
            $element .= $this->getHtmlElementContent($name, 'before', 'outside_wrapper');
            if (isset($this->input_wrapper[$name])) {
                $element .= $this->defineWrapper($this->input_wrapper[$name], 'start'); // input-group
            }
            $element .= $this->getHtmlElementContent($name, 'before', 'inside_wrapper');
            $element .= '<input id="' . $id . '" name="' . $name . '" type="' . $type . '" value="' . $value . '" ' . $attr . '>';
            $element .= $this->getHtmlElementContent($name, 'after', 'inside_wrapper');
            if (isset($this->input_wrapper[$name])) {
                $element .= $this->defineWrapper($this->input_wrapper[$name], 'end'); // end input-group
            }
            $element .= $this->getHtmlElementContent($name, 'after', 'outside_wrapper');
            $element .= $this->getErrorInputWrapper($name, $label, 'end'); // end has-error
            $element .= $this->getError($name);
            $end_col .= $this->getElementCol('end', 'input', $label); // end col-sm-8
            $end_wrapper .= $this->addErrorWrapper($name, 'end');
            $end_wrapper .= $this->setInputGroup($name, 'end', 'elementsWrapper'); // end form-group

            // output
            $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        }
        $this->registerField($name, $attr);

        return $this;
    }

    /**
     * Creates an input with fileuploader plugin.
     *
     * The fileuploader plugin generates complete html, js and css code.
     * You'll just have to call printIncludes('css') and printIncludes('js')
     * where you wants to put your css/js codes (generaly in <head> and just before </body>).
     *
     * @param string $type              The type of the input, usualy 'file'
     * @param string $name              The upload field name.
     *                                  Use an array (ex : name[]) to allow multiple files upload
     * @param string $value             (Optional) The input default value
     * @param string $label             (Optional) The input label
     * @param string $attr              (Optional) Can be any HTML input attribute or js event.
     *                                  attributes must be listed separated with commas.
     *                                  If you don't specify any ID as attr, the ID will be the name of the input.
     *                                  Example : class=my-class,placeholder=My Text,onclick=alert(\'clicked\');.
     * @param array  $fileUpload_config An associative array containing :
     *                                  'xml'           [string]       => (Optional) The xml node where your plugin code is
     *                                                                    in plugins-config/fileuploader.xml
     *                                                                    Default: 'default'
     *                                  'uploader'      [string]       => (Optional) The PHP uploader file in phpformbuilder/plugins/fileuploader/[xml-node-name]/php/
     *                                                                    Default: 'ajax_upload_file.php'
     *                                  'upload_dir'    [string]       => (Optional) the directory to upload the files.
     *                                                                    Relative to phpformbuilder/plugins/fileuploader/default/php/ajax_upload_file.php
                                                                          Default: '../../../../../file-uploads/' ( = [project root]/file-uploads)
     *                                  'limit'         [null|Number]  => (Optional) The max number of files to upload
     *                                                                    Default: 1
     *                                  'extensions'    [null|array]   => (Optional) Allowed extensions or file types
     *                                                                    example: ['jpg', 'jpeg', 'png', 'audio/mp3', 'text/plain']
     *                                                                    Default: ['jpg', 'jpeg', 'png', 'gif']
     *                                  'fileMaxSize'   [null|Number]  => (Optional) Each file's maximal size in MB,
     *                                                                    Default: 5
     *                                  'thumbnails'    [Boolean]      => (Optional) Defines Wether if the uploader creates thumbnails or not.
     *                                                                    Thumbnails paths and sizing is done in the plugin php uploader.
     *                                                                    Default: false
     *                                  'editor'        [Boolean]      => (Optional)  Allows the user to crop/rotate the uploaded image
     *                                                                    Default: false
     *                                  'width'         [null|Number]  => (Optional) The uploaded image maximum width in px
     *                                                                    Default: null
     *                                  'height'        [null|Number]  => (Optional) The uploaded image maximum height in px
     *                                                                    Default: null
     *                                  'crop'          [Boolean]      => (Optional) Defines Wether if the uploader crops the uploaded image or not.
     *                                                                    Default: false
     *                                  'debug'         [Boolean]      => (Optional) log the result in the browser's console
     *                                                                    and shows an error text on the page if the uploader fails to parse the json result.
     *                                                                    Default: false
     * @return $this
     *
     */
    public function addFileUpload($type, $name, $value = '', $label = '', $attr = '', $fileUpload_config = '', $current_file = '')
    {
        $this->has_file = true;
        $attr           = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values   = $this->getID($name, $attr); // if $attr contains no ID, field ID will be $name.
        $attr           = $array_values['attributs']; // if $attr contains an ID, we remove it.
        $attr           = $this->addElementClass($name, $attr);
        $value          = $this->getValue($name, $value);
        $start_wrapper  = '';
        $end_wrapper    = '';
        $start_label    = '';
        $end_label      = '';
        $start_col      = '';
        $end_col        = '';
        $element        = '';
        $index          = 0;

        // set uploader index
        if (in_array('fileuploader', $this->js_plugins)) {
            $count = count($this->js_plugins);
            for ($i=0; $i < $count; $i++) {
                if ($this->js_plugins == 'fileuploader') {
                    $index ++;
                }
            }
        }

        /* hidden field which will be posted in JSON with the uploaded file names. */
        $attr .= ' data-fileuploader-listInput="' . $name . '"';

        /* adding plugin */

        $default_config = array(
            'xml'           => 'default',
            'uploader'      => 'ajax_upload_file.php',
            'upload_dir'    => '../../../../../file-uploads/',
            'limit'         => 1,
            'extensions'    => ['jpg', 'jpeg', 'png', 'gif'],
            'file_max_size' => 5,
            'thumbnails'    => false,
            'editor'        => false,
            'width'         => null,
            'height'        => null,
            'crop'          => false,
            'debug'         => false
        );

        $fileUpload_config = array_merge($default_config, $fileUpload_config);

        // replace boolean values for javascript
        $bool = array('thumbnails', 'editor', 'crop', 'debug');
        foreach ($bool as $b) {
            if ($fileUpload_config[$b] === true) {
                $fileUpload_config[$b] = 'true';
            } else {
                $fileUpload_config[$b] = 'false';
            }
        }

        if (is_array($fileUpload_config['extensions'])) {
            $fileUpload_config['extensions'] = "['" . implode("', '", $fileUpload_config['extensions']) . "']";
        }
        $xml_replacements = array(
            '%limit%'       => $fileUpload_config['limit'],
            '%uploader%'    => $fileUpload_config['uploader'],
            '%uploadDir%'   => $fileUpload_config['upload_dir'],
            '%extensions%'  => $fileUpload_config['extensions'],
            '%fileMaxSize%' => $fileUpload_config['file_max_size'],
            '%thumbnails%'  => $fileUpload_config['thumbnails'],
            '%editor%'      => $fileUpload_config['editor'],
            '%debug%'       => $fileUpload_config['debug'],
            '%width%'       => $fileUpload_config['width'],
            '%height%'      => $fileUpload_config['height'],
            '%crop%'        => $fileUpload_config['crop'],
            '%index%'       => $index,
            '%PLUGINS_URL%' => $this->plugins_url
        );
        $this->addPlugin('fileuploader', '#uploader-' . $name, $fileUpload_config['xml'], $xml_replacements);

        // form-group wrapper
        $start_wrapper = $this->setInputGroup($name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($name, 'start');

        // label
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label for="uploader-' . $name . '"' . $this->getLabelClass('fileinput') . '>';
            if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields))) {
                $start_label .= '<span class="' . $this->options['textErrorClass'] . '">' . $this->getRequired($label, $attr) . '</span>';
            } else {
                $start_label .=$this->getRequired($label, $attr);
            }
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }

        // input
        $start_col .= $this->getElementCol('start', 'input', $label); // col-sm-8
        $element .= $this->getErrorInputWrapper($name, $label, 'start'); // has-error
        $element .= $this->getHtmlElementContent($name, 'before', 'outside_wrapper');
        if (isset($this->input_wrapper[$name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$name], 'start'); // input-group
        }
        $element .= $this->getHtmlElementContent($name, 'before', 'inside_wrapper');
        $current_file_json_data = '';
        if (!empty($current_file) && is_array($current_file)) {
            if (isset($current_file[0])) {
                // if several files passed as array
                $json = '';
                foreach ($current_file as $cf) {
                    $json .= json_encode($cf) . ',';
                }
                $current_file = rtrim($json, ',');
            } else {
                $current_file = json_encode($current_file);
            }
            $current_file_json_data = ' data-fileuploader-files=\'[' . $current_file . ']\'';
        }
        $element .= '<input type="file" name="uploader-' . $name . '" id="uploader-' . $name . '"' . $attr . $current_file_json_data . '>';
        $element .= $this->getHtmlElementContent($name, 'after', 'inside_wrapper');
        if (isset($this->input_wrapper[$name])) {
            $element .= $this->getError($name, true);
            $element .= $this->defineWrapper($this->input_wrapper[$name], 'end'); // end input-group
        }
        $element .= $this->getHtmlElementContent($name, 'after', 'outside_wrapper');
        $element .= $this->getErrorInputWrapper($name, $label, 'end'); // end has-error
        $element .= $this->getError($name);
        $end_col .= $this->getElementCol('end', 'input', $label); // end col-sm-8
        $end_wrapper .= $this->addErrorWrapper($name, 'end');
        $end_wrapper .= $this->setInputGroup($name, 'end', 'elementsWrapper'); // end form-group
        // output
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        $this->registerField($name, $attr);

        return $this;
    }

    /**
     * Creates an input with fileupload plugin.
     * DEPRECATED
     *
     * The fileupload plugin generates complete html, js and css code.
     * You'll just have to call printIncludes('css') and printIncludes('js')
     * where you wants to put your css/js codes (generaly in <head> and just before </body>).
     *
     * @param string $type              The node of the plugins-config/jquery-fileupload.xml file where is your code.
     *                                  For example : 'default' or 'images'
     * @param string $name              The upload field name.
     *                                  Use an array (ex : name[]) to allow multiple files upload
     * @param string $value             (Optional) The input default value
     * @param string $label             (Optional) The input label
     * @param string $attr              (Optional) Can be any HTML input attribute or js event.
     *                                  attributes must be listed separated with commas.
     *                                  If you don't specify any ID as attr, the ID will be the name of the input.
     *                                  Example : class=my-class,placeholder=My Text,onclick=alert(\'clicked\');.
     * @param array  $fileUpload_config (Optional) An associative array containing :
     *                                  'xml'                 => The xml node where your plugin code is
     *                                  in plugins-config/jquery-fileupload.xml,
     *                                  'uploader'            => The php uploader file in
     *                                  plugins/jQuery-File-Upload-9.5.8/server/php/ folder
     *                                  'btn-text'            => The text of the upload button,
     *                                  'max-number-of-files' => The max number of files to upload
     * @return $this
     *
     */
    public function addJqueryFileUpload($type, $name, $value = '', $label = '', $attr = '', $fileUpload_config = '')
    {
        $this->has_file = true;
        $attr           = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values   = $this->getID($name, $attr); // if $attr contains no ID, field ID will be $name.
        $attr           = $array_values['attributs']; // if $attr contains an ID, we remove it.
        $attr           = $this->addElementClass($name, $attr);
        $value          = $this->getValue($name, $value);
        $start_wrapper  = '';
        $end_wrapper    = '';
        $start_label    = '';
        $end_label      = '';
        $start_col      = '';
        $end_col        = '';
        $element        = '';

        /* adding plugin */

        if (!isset($fileUpload_config['xml'])) {
            $fileUpload_config['xml'] = 'default';
        }
        if (!isset($fileUpload_config['uploader'])) {
            $fileUpload_config['uploader'] = 'defaultFileUpload.php';
        }
        if (!isset($fileUpload_config['btn-text'])) {
            $fileUpload_config['btn-text'] = 'Select files...';
        }
        if (!isset($fileUpload_config['max-number-of-files'])) {
            $fileUpload_config['max-number-of-files'] = 1;
        }

        /* remove [] from name if array of files */

        if (preg_match('`\[\]`', $name)) {
            $uploaderId = preg_replace('`\[\]`', '', $name);
        } else {
            $uploaderId = $name;
        }
        $xml_replacements = array('%uploader%' => $fileUpload_config['uploader'], '%max-number-of-files%' => $fileUpload_config['max-number-of-files'], '%PLUGINS_DIR%' => $this->plugins_url, '%file-input%' => $name, '%uploader-id%' => $uploaderId);
        $this->addPlugin('jquery-fileupload', '#' . $this->form_ID, $fileUpload_config['xml'], $xml_replacements);
        $start_wrapper = $this->elements_start_wrapper;
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label for="' . $uploaderId . '"' . $this->getLabelClass('fileinput') . '>';
            if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields))) {
                $start_label .= '<span class="' . $this->options['textErrorClass'] . '">' . $this->getRequired($label, $attr) . '</span>';
            } else {
                $start_label .=$this->getRequired($label, $attr);
            }
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }
        $element = $this->getElementCol('start', 'input', $label);
        if (isset($this->input_wrapper[$name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$name], 'start');
        }
        $element .= $this->getHtmlElementContent($name, 'before');

        /* getting html_code from xml */

        if (file_exists(dirname(__FILE__) . '/plugins-config-custom/jquery-fileupload.xml')) {
            // if custom config xml file
            $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config-custom/jquery-fileupload.xml');

            // if node doesn't exist, fallback to default xml
            if (!isset($xml->{$fileUpload_config['xml']}->html_code)) {
                $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/jquery-fileupload.xml');
            }
        } else {
            $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/jquery-fileupload.xml');
        }
        $html_code = $xml->{$fileUpload_config['xml']}->html_code;
        $search    = array('`%input_name%`', '`%btn-text%`');
        $replace   = array($name, $fileUpload_config['btn-text']);
        $element .= preg_replace($search, $replace, $html_code);
        $element .= $this->getHtmlElementContent($name, 'after');
        if (isset($this->input_wrapper[$name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$name], 'end');
        }
        $element .= $this->getElementCol('end', 'input', $label);
        $end_wrapper = $this->elements_end_wrapper;
        // output
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        $this->registerField($name, $attr);

        return $this;
    }

    /**
     * Adds textarea to the form
     * @param string $name  The textarea name
     * @param string $value (Optional) The textarea default value
     * @param string $label (Optional) The textarea label
     * @param string $attr  (Optional) Can be any HTML input attribute or js event.
     *                      attributes must be listed separated with commas.
     *                      If you don't specify any ID as attr, the ID will be the name of the textarea.
     *                      Example : cols=30, rows=4;
     * @return $this
     */
    public function addTextarea($name, $value = '', $label = '', $attr = '')
    {
        $attr          = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values  = $this->getID($name, $attr); // if $attr contains no ID, field ID will be $name.
        $id            = $array_values['id'];
        $attr          = $array_values['attributs']; // if $attr contains an ID, we remove it.
        $attr          = $this->addElementClass($name, $attr);
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        if ($this->framework == 'material') {
            $attr         = $this->addElementClass($name, 'class="materialize-textarea"');
        }
        $value        = $this->getValue($name, $value);
        // form-group wrapper
        $start_wrapper = $this->setInputGroup($name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($name, 'start');
        // label
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label for="' . $id . '"' . $this->getLabelClass() . '>' . $this->getRequired($label, $attr);
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }
        $start_col .= $this->getElementCol('start', 'textarea', $label);
        $element .= $this->getErrorInputWrapper($name, $label, 'start');
        $element .= $this->getHtmlElementContent($name, 'before');
        $element .= '<textarea id="' . $id . '" name="' . $name . '" ' . $attr . '>' . $value . '</textarea>';
        $element .= $this->getHtmlElementContent($name, 'after');
        $element .= $this->getError($name);
        $element .= $this->getErrorInputWrapper($name, $label, 'end');
        $end_col .= $this->getElementCol('end', 'textarea', $label);
        $end_wrapper = $this->addErrorWrapper($name, 'end');
        $end_wrapper .= $this->setInputGroup($name, 'end', 'elementsWrapper'); // end form-group
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        $this->registerField($name, $attr);

        return $this;
    }

    /**
     * Adds option to the $select_name element
     *
     * IMPORTANT : Always add your options BEFORE creating the select element
     *
     * @param string $select_name The name of the select element
     * @param string $value       The option value
     * @param string $txt         The text that will be displayed
     * @param string $group_name  (Optional) the optgroup name
     * @param string $attr        (Optional) Can be any HTML input attribute or js event.
     *                            attributes must be listed separated with commas.
     *                            If you don't specify any ID as attr, the ID will be the name of the option.
     *                            Example : class=my-class
     * @return $this
     */
    public function addOption($select_name, $value, $txt, $group_name = '', $attr = '')
    {
        $optionValues = array('value' => $value, 'txt' => $txt, 'attributs' => $attr);
        if (!empty($group_name)) {
            $this->option[$select_name][$group_name][] = $optionValues;
            if (!isset($this->group_name[$select_name])) {
                $this->group_name[$select_name] = array();
            }
            if (!in_array($group_name, $this->group_name[$select_name])) {
                $this->group_name[$select_name][] = $group_name;
            }
        } else {
            $this->option[$select_name][] = $optionValues;
        }

        return $this;
    }

    /**
     * Adds a select element
     *
     * IMPORTANT : Always add your options BEFORE creating the select element
     *
     * @param string $select_name        The name of the select element
     * @param string $label              (Optional) The select label
     * @param string $attr               (Optional)  Can be any HTML input attribute or js event.
     *                                   attributes must be listed separated with commas.
     *                                   If you don't specify any ID as attr, the ID will be the name of the input.
     *                                   Example : class=my-class
     * @param string $displayGroupLabels (Optional) True or false.
     *                                   Default is true.
     * @return $this
     */
    public function addSelect($select_name, $label = '', $attr = '', $displayGroupLabels = true)
    {
        $attr          = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values  = $this->getID($select_name, $attr); // if $attr contains no ID, field ID will be $select_name.
        $id            = $array_values['id'];
        $attr          = $array_values['attributs']; // if $attr contains an ID, we remove it.
        if ($this->framework !== 'material') { // don't add form-group if material
            $attr          = $this->addElementClass($select_name, $attr);
        }
        $form_ID       = $this->form_ID;
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';

        // form-group wrapper
        $start_wrapper = $this->setInputGroup($select_name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($select_name, 'start');
        // label
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label for="' . $id . '"' . $this->getLabelClass() . '>' . $this->getRequired($label, $attr);
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }
        $start_col .= $this->getElementCol('start', 'select', $label);
        $element .= $this->getErrorInputWrapper($select_name, $label, 'start');
        $element .= $this->getHtmlElementContent($select_name, 'before', 'outside_wrapper');
        if (isset($this->input_wrapper[$select_name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$select_name], 'start');
        }
        $element .= $this->getHtmlElementContent($select_name, 'before', 'inside_wrapper');
        $element .= '<select id="' . $id . '" name="' . $select_name . '" ' . $attr . '>';
        if (isset($this->group_name[$select_name])) {
            foreach ($this->group_name[$select_name] as $group_name) {
                $nbreOptions = count($this->option[$select_name][$group_name]);
                $groupLabel = '';
                if ($displayGroupLabels == true) {
                    $groupLabel = ' label="' . $group_name . '"';
                }
                $element .= '<optgroup' . $groupLabel . '>';
                for ($i=0; $i<$nbreOptions; $i++) {
                    $txt = $this->option[$select_name][$group_name][$i]['txt'];
                    $value = $this->option[$select_name][$group_name][$i]['value'];
                    $option_attr = $this->option[$select_name][$group_name][$i]['attributs'];
                    $option_attr = $this->getAttributes($option_attr);
                    $element .= '<option value="' . $value . '"';
                    $option_attr = $this->getCheckedOrSelected($select_name, $value, $option_attr, 'select');
                    $element .= ' ' . $option_attr . '>' . $txt . '</option>';
                }
                $element .= '</optgroup>';
            }
        } else {
            $nbreOptions = 0;
            if (isset($this->option[$select_name])) {
                $nbreOptions = count($this->option[$select_name]);
            }
            for ($i=0; $i<$nbreOptions; $i++) {
                $txt = $this->option[$select_name][$i]['txt'];
                $value = $this->option[$select_name][$i]['value'];
                $option_attr = $this->option[$select_name][$i]['attributs'];
                $option_attr = $this->getAttributes($option_attr);
                $element .= '<option value="' . $value . '"';
                $option_attr = $this->getCheckedOrSelected($select_name, $value, $option_attr, 'select');
                $element .= ' ' . $option_attr . '>' . $txt . '</option>';
            }
        }
        $element .= '</select>';
        $element .= $this->getHtmlElementContent($select_name, 'after', 'inside_wrapper');
        if (isset($this->input_wrapper[$select_name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$select_name], 'end');
        }
        $element .= $this->getHtmlElementContent($select_name, 'after', 'outside_wrapper');
        $element .= $this->getErrorInputWrapper($select_name, $label, 'end');
        $element .= $this->getError($select_name);
        $end_col .= $this->getElementCol('end', 'select', $label);
        $end_wrapper = $this->addErrorWrapper($select_name, 'end');
        $end_wrapper .= $this->setInputGroup($select_name, 'end', 'elementsWrapper'); // end form-group
        if (preg_match('`selectpicker`', $attr)) {
            if (!in_array('bootstrap-select', $this->js_plugins)) {
                $selectpicker_config = 'default-selectpicker';
                $selectpicker_icon_base = 'glyphicon';
                $selectpicker_tick_icon = 'glyphicon-ok';
                if (preg_match('`data-icon-base="([a-z-]+)"`', $attr, $out)) {
                    $selectpicker_icon_base = $out[1];
                }
                if (preg_match('`data-tick-icon="([a-z-]+)"`', $attr, $out)) {
                    $selectpicker_tick_icon = $out[1];
                }
                if ($this->framework == 'foundation') {
                    $selectpicker_config = 'default-selectpicker-with-bootstrap';
                }
                $this->addPlugin('bootstrap-select', '.selectpicker', $selectpicker_config, array('%iconBase%' => $selectpicker_icon_base, '%tickIcon%' => $selectpicker_tick_icon));
            }
        } elseif (preg_match('`select2`', $attr)) {
            // if (!in_array('select2', $this->js_plugins)) {
                $this->addPlugin('select2', '.select2', 'default', array('%theme%' => 'clean'));
            // }
        }

        // output
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        $this->registerField($select_name, $attr);

        return $this;
    }

    /**
     * adds a country select list with flags
     * @param array  $select_name
     * @param string $label        (Optional) The select label
     * @param string $attr         (Optional)  Can be any HTML input attribute or js event.
     *                             attributes must be listed separated with commas.
     *                             If you don't specify any ID as attr, the ID will be the name of the input.
     *                             Example : class=my-class
     * @param array  $user_options (Optional) :
     *                             lang            : MUST correspond to one subfolder of [$this->plugins_url]countries/country-list/country/cldr/
     *                             *** for example 'en', or 'fr_FR'                 Default : 'en'
     *                             flags           : true or false.                 Default : true
     *                             *** displays flags into option list
     *                             flag_size       : 16 or 32                       Default : 32
     *                             return_value    : 'name' or 'code'               Default : 'name'
     *                             *** type of the value that will be returned
     *                             show_tick       : true or false
     *                             *** shows a checkmark beside selected options    Default : true
     *                             live_search     : true or false                  Default : true
     * @return $this
    */
    public function addCountrySelect($select_name, $label = '', $attr = '', $user_options = array())
    {

        /* define options*/

        $options = array(
            'lang' => 'en',
            'flags' => true,
            'flag_size' => 32,
            'return_value' => 'name',
            'show_tick' => true,
            'live_search' => true
        );
        foreach ($user_options as $key => $value) {
            if (in_array($key, $options)) {
                $options[$key] = $value;
            }
        }
        $class = '';
        if (preg_match('`class(\s)?=(\s)?([^,]+)`', $attr, $out)) {
            $class = $out[3] . ' ';
        }
        if ($this->framework == 'bs4') {
            $class .= 'select2country ' . $this->options['elementsClass'];
        } else {
            $class .= 'selectpicker ' . $this->options['elementsClass'];
        }
        if ($options['flags'] == true) {
            $class .= ' f' . $options['flag_size'];
            $xml_node = 'countries-flags-' . $options['flag_size'];
        } else {
            if ($this->framework == 'bs4') {
                $xml_node = 'default';
            } else {
                $xml_node = 'default-selectpicker';
            }
            $xml_node = 'default-selectpicker';
        }
        if ($this->framework != 'bs4' && $options['show_tick'] == true) {
            $class .= ' show-tick';
        }
        if ($this->framework == 'foundation') {
            $xml_node .= '-with-bootstrap';
        }
        $live_search = '';
        if ($this->framework != 'bs4' && $options['live_search'] == true) {
            $live_search .= ' data-live-search="true" ';
        }
        if ($this->framework == 'bs4') {
            $this->addPlugin('select2', '.select2country', $xml_node, array('%theme%' => 'clean'));
        } else {
            $this->addPlugin('bootstrap-select', '.selectpicker', $xml_node);
        }
        $countries    = include $this->plugins_path . 'countries/country-list/country/cldr/' . $options['lang'] . '/country.php';
        $attr         = $this->getAttributes($attr); // returns linearised attributes (with ID)
        $array_values = $this->getID($select_name, $attr); // if $attr contains no ID, field ID will be $select_name.
        $id           = $array_values['id'];
        $attr         = $array_values['attributs']; // if $attr contains an ID, we remove it.
        $attr         = $this->removeAttr('class', $attr);
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        $start_wrapper = $this->setInputGroup($select_name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($select_name, 'start');
        // label
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label for="' . $id . '"' . $this->getLabelClass() . '>' . $this->getRequired($label, $attr);
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }
        $start_col .= $this->getElementCol('start', 'select', $label);
        $element .= $this->getErrorInputWrapper($select_name, $label, 'start');
        $element .= $this->getHtmlElementContent($select_name, 'before');
        $element .= '<select name="' . $select_name . '" id="' . $id . '" class="' . $class . '"' . $live_search . $attr . '>';
        $option_list = '';
        if ($options['return_value'] == 'name') {
            foreach ($countries as $country_code => $country_name) {
                $option_list .= '<option value="' . $country_name . '" class="flag ' . mb_strtolower($country_code) . '"';
                $option_attr = $this->getCheckedOrSelected($select_name, $country_name, '', 'select');
                $option_list .= ' ' . $option_attr . '>' . $country_name . '</option>';
            }
        } else {
            foreach ($countries as $country_code => $country_name) {
                $option_list .= '<option value="' . $country_code . '" class="flag ' . mb_strtolower($country_code) . '"';
                $option_attr = $this->getCheckedOrSelected($select_name, $country_code, '', 'select');
                $option_list .= ' ' . $option_attr . '>' . $country_name . '</option>';
            }
        }
        $element .= $option_list;
        $element .= '</select>';
        $element .= $this->getHtmlElementContent($select_name, 'after');
        $element .= $this->getError($select_name);
        $element .= $this->getErrorInputWrapper($select_name, $label, 'end');
        $end_col .= $this->getElementCol('end', 'select', $label);
        $end_wrapper = $this->addErrorWrapper($select_name, 'end');
        $end_wrapper .= $this->setInputGroup($select_name, 'end', 'elementsWrapper'); // end form-group

        // output
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, $this->options['wrapElementsIntoLabels']);
        $this->registerField($select_name, $attr);

        return $this;
    }

    /**
     * Adds radio button to $group_name element
     *
     * @param string $group_name The radio button groupname
     * @param string $label      The radio button label
     * @param string $value      The radio button value
     * @param string $attr       (Optional) Can be any HTML input attribute or js event.
     *                           attributes must be listed separated with commas.
     *                           Example : checked=checked
     * @return $this
     */
    public function addRadio($group_name, $label, $value, $attr = '')
    {
        $this->radio[$group_name]['label'][]  = $label;
        $this->radio[$group_name]['value'][]  = $value;
        $this->radio[$group_name]['attr'][]  = $attr;

        return $this;
    }

    /**
     * Prints radio buttons group.
     *
     * @param string $group_name The radio button group name
     * @param string $label      (Optional) The radio buttons group label
     * @param string $inline     (Optional) True or false.
     *                           Default is true.
     * @param string $attr       (Optional) Can be any HTML input attribute or js event.
     *                           attributes must be listed separated with commas.
     *                           Example : class=my-class
     * @return $this
     */
    public function printRadioGroup($group_name, $label = '', $inline = true, $attr = '')
    {
        $form_ID       = $this->form_ID;
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        $start_wrapper = $this->setInputGroup($group_name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($group_name, 'start');
        if (!empty($label)) {
            $attr          = $this->getAttributes($attr); // returns linearised attributes (with ID)
            $attr          = $this->addClass('main-label', $attr);
            if ($this->layout == 'horizontal') {
                if ($this->options['horizontalLabelWrapper'] === false) {
                    $class     = $this->options['horizontalLabelCol'] . ' ' . $this->options['horizontalLabelClass'];
                    $attr      = $this->addClass($class, $attr);
                    $start_label = '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label = '</label>';
                } else {
                    // wrap label into div with horizontalLabelClass
                    $class        = $this->options['horizontalLabelClass'];
                    $attr         = $this->addClass($class, $attr);
                    $start_label  = $this->getLabelCol('start');
                    $start_label .= '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label    = '</label>';
                    $end_label   .= $this->getLabelCol('end');
                }
            } else {
                if ($this->options['verticalLabelWrapper'] === false) {
                    $class     = $this->options['verticalLabelClass'];
                    $attr      = $this->addClass($class, $attr);
                    $start_label = '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label = '</label>';
                } else {
                    // wrap label into div with verticalLabelClass
                    $start_label  = $this->getLabelCol('start');
                    $start_label .= '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label    = '</label>';
                    $end_label   .= $this->getLabelCol('end');
                }
            }
        }
        $required = '';
        if (preg_match('`required`', $attr)) {
            $required = ' required';
        }
        $start_col .= $this->getElementCol('start', 'radio', $label);
        $element .= $this->getErrorInputWrapper($group_name, $label, 'start');
        $element .= $this->getHtmlElementContent($group_name, 'before');
        if (isset($this->input_wrapper[$group_name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$group_name], 'start'); // input-group
        }
        for ($i=0; $i < count($this->radio[$group_name]['label']); $i++) {
            $radio_start_label   = '';
            $radio_end_label     = '';
            $radio_input         = '';
            if (!empty($this->options['radioWrapper']) && $inline !== true) {
                $element .= $this->radio_start_wrapper;
            } elseif (!empty($this->options['inlineRadioWrapper']) && $inline === true) {
                $element .= $this->inline_radio_start_wrapper;
            }
            $radio_label  = $this->radio[$group_name]['label'][$i];
            $radio_value  = $this->radio[$group_name]['value'][$i];
            $radio_attr   = $this->getAttributes($this->radio[$group_name]['attr'][$i]); // returns linearised attributes (with ID)
            if ($this->framework == 'material') {
                $radio_attr = $this->addClass('with-gap', $radio_attr);
            } elseif ($this->framework == 'bs4') {
                $radio_attr = $this->addClass('form-check-input', $radio_attr);
            }
            $radio_start_label .= '<label for="' . $group_name . '_' . $i . '" ' . $this->getLabelClass('radio', $inline) . '>';
            $radio_input .= '<input type="radio" id="' . $group_name . '_' . $i . '" name="' . $group_name . '" value="' . $radio_value . '"';
            if (isset($_SESSION[$form_ID][$group_name])) {
                if ($_SESSION[$form_ID][$group_name] == $radio_value) {
                    if (!preg_match('`checked`', $radio_attr)) {
                        $radio_input .= ' checked="checked"';
                    }
                } else { // we remove 'checked' from $radio_attr as user has previously checked another, memorized in session.
                    $radio_attr = $this->removeAttr('checked', $radio_attr);
                }
            }
            $radio_input .= $required . ' ' . $radio_attr . '>';

            $radio_end_label = $radio_label . '</label>';
            if ($this->options['wrapRadiobtnsIntoLabels'] === true) {
                $element .= $radio_start_label . $radio_input . $radio_end_label;
            } else {
                $element .= $radio_input . $radio_start_label . $radio_end_label;
            }
            if ($inline !== true) {
                if (!empty($this->options['radioWrapper'])) {
                    $element .= $this->radio_end_wrapper;
                } else {
                    $element .= '<br>';
                }
            } elseif (!empty($this->options['inlineRadioWrapper'])) {
                $element .= $this->inline_radio_end_wrapper;
            }
        }
        if (isset($this->input_wrapper[$group_name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$group_name], 'end'); // end input-group
        }
        $element .= $this->getHtmlElementContent($group_name, 'after');
        $element .= $this->getError($group_name);
        $element .= $this->getErrorInputWrapper($group_name, $label, 'end');
        $end_col .= $this->getElementCol('end', 'radio', $label);
        $end_wrapper = $this->addErrorWrapper($group_name, 'end');
        $end_wrapper .= $this->setInputGroup($group_name, 'end', 'elementsWrapper'); // end form-group
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, false);
        $this->registerField($group_name, $attr);

        return $this;
    }

    /**
     * Adds checkbox to $group_name
     *
     * @param string $group_name The checkbox button groupname
     * @param string $label      The checkbox label
     * @param string $value      The checkbox value
     * @return $this
     */
    public function addCheckbox($group_name, $label, $value, $attr = '')
    {
        $form_ID                                             = $this->form_ID;
        $this->checkbox[$group_name]['label'][]              = $label;
        $this->checkbox[$group_name]['value'][]              = $value;
        $this->checkbox[$group_name]['attr'][]               = $attr;

        return $this;
    }

    /**
     * Prints checkbox group.
     *
     * @param string $var (Optional) description
     *
     * @param string $group_name The checkbox group name (will be converted to an array of indexed value)
     * @param string $label      (Optional) The checkbox group label
     * @param string $inline     (Optional) True or false.
     *                           Default is true.
     * @param string $attr       (Optional) Can be any HTML input attribute or js event.
     *                           attributes must be listed separated with commas.
     *                           Example : class=my-class
     * @return $this
     */
    public function printCheckboxGroup($group_name, $label = '', $inline = true, $attr = '')
    {
        $form_ID = $this->form_ID;
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        $start_wrapper = $this->setInputGroup($group_name, 'start', 'elementsWrapper');
        $start_wrapper .= $this->addErrorWrapper($group_name, 'start');
        if (!empty($label)) {
            $attr          = $this->getAttributes($attr); // returns linearised attributes (with ID)
            $attr          = $this->addClass('main-label', $attr);
            if ($this->layout == 'horizontal') {
                if ($this->options['horizontalLabelWrapper'] === false) {
                    $class     = $this->options['horizontalLabelCol'] . ' ' . $this->options['horizontalLabelClass'];
                    $attr      = $this->addClass($class, $attr);
                    $start_label = '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label = '</label>';
                } else {
                    // wrap label into div with horizontalLabelClass
                    $class        = $this->options['horizontalLabelClass'];
                    $attr         = $this->addClass($class, $attr);
                    $start_label  = $this->getLabelCol('start');
                    $start_label .= '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label    = '</label>';
                    $end_label   .= $this->getLabelCol('end');
                }
            } else {
                if ($this->options['verticalLabelWrapper'] === false) {
                    $class     = $this->options['verticalLabelClass'];
                    $attr      = $this->addClass($class, $attr);
                    $start_label = '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label = '</label>';
                } else {
                    // wrap label into div with verticalLabelClass
                    $start_label  = $this->getLabelCol('start');
                    $start_label .= '<label ' . $attr . '>' . $this->getRequired($label, $attr);
                    $end_label    = '</label>';
                    $end_label   .= $this->getLabelCol('end');
                }
            }
        }
        $start_col .= $this->getElementCol('start', 'checkbox', $label);
        $element .= $this->getErrorInputWrapper($group_name, $label, 'start');
        $element .= $this->getHtmlElementContent($group_name, 'before');
        for ($i=0; $i < count($this->checkbox[$group_name]['label']); $i++) {
            $checkbox_start_label   = '';
            $checkbox_end_label     = '';
            $checkbox_input         = '';
            if (!empty($this->options['checkboxWrapper']) && $inline !== true) {
                $element .= $this->checkbox_start_wrapper;
            } elseif (!empty($this->options['inlineCheckboxWrapper']) && $inline === true) {
                $element .= $this->inline_checkbox_start_wrapper;
            }
            $checkbox_label = $this->checkbox[$group_name]['label'][$i];
            $checkbox_value = $this->checkbox[$group_name]['value'][$i];
            $checkbox_attr = $this->getAttributes($this->checkbox[$group_name]['attr'][$i]);
            if ($this->framework == 'bs4') {
                $checkbox_attr = $this->addClass('form-check-input', $checkbox_attr);
            }
            $checkbox_start_label = '<label for="' . $group_name . '_' . $i . '"' . $this->getLabelClass('checkbox', $inline) . '>';
            $checkbox_input = '<input type="checkbox" id="' . $group_name . '_' . $i . '" name="' . $group_name . '[]" value="' . $checkbox_value . '"';
            $checkbox_attr = $this->getCheckedOrSelected($group_name, $checkbox_value, $checkbox_attr, 'checkbox');
            $checkbox_input .= ' ' . $checkbox_attr . '>';
            $checkbox_end_label = $checkbox_label . '</label>';

            if ($this->options['wrapCheckboxesIntoLabels'] === true) {
                $element .= $checkbox_start_label . $checkbox_input . $checkbox_end_label;
            } else {
                $element .= $checkbox_input . $checkbox_start_label . $checkbox_end_label;
            }
            if (!empty($this->options['checkboxWrapper']) && $inline !== true) {
                $element .= $this->checkbox_end_wrapper;
            } elseif (!empty($this->options['inlineCheckboxWrapper']) && $inline === true) {
                $element .= $this->inline_checkbox_end_wrapper;
            }
        }
        $element .= $this->getHtmlElementContent($group_name, 'after');
        $element .= $this->getError($group_name);
        $element .= $this->getErrorInputWrapper($group_name, $label, 'end');

        $end_col .= $this->getElementCol('end', 'checkbox', $label);
        $end_wrapper = $this->addErrorWrapper($group_name, 'end');
        $end_wrapper .= $this->setInputGroup($group_name, 'end', 'elementsWrapper'); // end form-group
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, false);
        $this->registerField($group_name, $attr);

        return $this;
    }

    /**
     * Adds button to the form
     *
     * If $btnGroupName is empty, the button will be automaticly displayed.
     * Otherwise, you'll have to call printBtnGroup to display your btnGroup.
     *
     * @param string $type         The html button type
     * @param string $name         The button name
     * @param string $value        The button value
     * @param string $text         The button text
     * @param string $attr         (Optional) Can be any HTML input attribute or js event.
     *                             attributes must be listed separated with commas.
     *                             If you don't specify any ID as attr, the ID will be the name of the input.
     *                             Example : class=my-class,onclick=alert(\'clicked\');
     * @param string $btnGroupName (Optional) If you wants to group several buttons, group them then call printBtnGroup.
     * @return $this
     */
    public function addBtn($type, $name, $value, $text, $attr = '', $btnGroupName = '')
    {

        /*  if $btnGroupName isn't empty, we just store values
        *   witch will be called back by printBtnGroup($btnGroupName)
        *   else we store the values in a new array, then call immediately printBtnGroup($btnGroupName)
        */

        if (empty($btnGroupName)) {
            $btnGroupName = 'btn-alone';
            $this->btn[$btnGroupName] = array();
        }

        /* Automagically add Ladda plugin */

        if (preg_match('`ladda-button`', $attr)) {
            $this->addPlugin('ladda', '.ladda-button');
            // $text = '<span class="ladda-label">' . $text . '</span>';
        }

        $this->btn[$btnGroupName]['type'][] = $type;
        $this->btn[$btnGroupName]['name'][] = $name;
        $this->btn[$btnGroupName]['value'][] = $value;
        $this->btn[$btnGroupName]['text'][] = $text;
        $this->btn[$btnGroupName]['attr'][] = $attr;

        /*  if $btnGroupName was empty the button is displayed. */

        if ($btnGroupName == 'btn-alone') {
            $this->printBtnGroup($btnGroupName);
        }

        return $this;
    }

    /**
     * Prints buttons group.
     *
     * @param string $btnGroupName The buttons group name
     * @param string $label        (Optional) The buttons group label
     * @return $this
     */
    public function printBtnGroup($btnGroupName, $label = '')
    {
        $btn_alone = false;
        $btn_name  = '';
        $start_wrapper = '';
        $end_wrapper   = '';
        $start_label   = '';
        $end_label     = '';
        $start_col     = '';
        $end_col       = '';
        $element       = '';
        if ($btnGroupName == 'btn-alone') {
            $btn_alone = true;
            $btn_name  = $this->btn[$btnGroupName]['name'][0];
        }
        $start_wrapper = $this->setInputGroup($btn_name, 'start', 'buttonWrapper');

        // label
        if ($this->options['verticalLabelWrapper'] === true) {
            $start_label  .= $this->getLabelCol('start');
        }
        if (!empty($label)) {
            $start_label .= '<label>' . $label;
            $end_label = '</label>';
        }
        if ($this->options['verticalLabelWrapper'] === true) {
            $end_label   .= $this->getLabelCol('end');
        }
        $start_col .= $this->getElementCol('start', 'button', $label);
        if (!empty($this->options['btnGroupClass']) && $btn_alone === false) {
            $element .= '<div class="' . $this->options['btnGroupClass'] . '">';
        }
        $element .= $this->getHtmlElementContent($btnGroupName, 'before');
        if ($btn_alone === true) {
            if (isset($this->input_wrapper[$btn_name])) {
                $element .= $this->defineWrapper($this->input_wrapper[$btn_name], 'start'); // input-group-btn
            }
        }
        for ($i=0; $i < count($this->btn[$btnGroupName]['type']); $i++) {
            $btn_type     = $this->btn[$btnGroupName]['type'][$i];
            $btn_name     = $this->btn[$btnGroupName]['name'][$i];
            $btn_value    = $this->btn[$btnGroupName]['value'][$i];
            $btn_text     = $this->btn[$btnGroupName]['text'][$i];
            $btn_attr     = $this->btn[$btnGroupName]['attr'][$i];
            $btn_attr     = $this->getAttributes($btn_attr); // returns linearised attributes (with ID)
            $array_values = $this->getID($btn_name, $btn_attr); // if $btn_attr contains no ID, field ID will be $btn_name.
            $id           = $array_values['id'];
            $btn_attr     = $array_values['attributs']; // if $btn_attr contains an ID, we remove it.
            $btn_value    = $this->getValue($btn_name, $btn_value);
            $element .= '<button type="' . $btn_type . '" name="' . $btn_name . '" value="' . $btn_value . '" ' . $btn_attr . '>' . $btn_text . '</button>';
        }
        if (isset($this->input_wrapper[$btn_name])) {
            $element .= $this->defineWrapper($this->input_wrapper[$btn_name], 'end'); // end input-group-btn
        }
        $element .= $this->getHtmlElementContent($btnGroupName, 'after');
        if (!empty($this->options['btnGroupClass']) && $btn_alone === false) {
            $element .= '</div>';
        }
        $end_col .= $this->getElementCol('end', 'button', $label);
        $end_wrapper .= $this->setInputGroup($btn_name, 'end', 'buttonWrapper');
        $this->html .= $this->outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element, false);

        return $this;
    }

    /**
     * Starts a fieldset tag.
     * @param string $legend (Optional) Legend of the fieldset.
     * @param string $fieldset_attr (Optional) Fieldset attributes.
     * @param string $legend_attr (Optional) Legend attributes.
     * @return $this
     */
    public function startFieldset($legend = '', $fieldset_attr = '', $legend_attr = '')
    {
        if (!empty($fieldset_attr)) {
            $fieldset_attr = ' ' . $this->getAttributes($fieldset_attr);
        }
        if (!empty($legend_attr)) {
            $legend_attr = ' ' . $this->getAttributes($legend_attr);
        }
        $this->html .= '<fieldset' . $fieldset_attr . '>';
        if (!empty($legend)) {
            $this->html .= '<legend' . $legend_attr . '>' . $legend . '</legend>';
        }

        return $this;
    }

    /**
     * Ends a fieldset tag.
     * @return $this
     */
    public function endFieldset()
    {
        if (!empty($this->btn_submit)) {
            // si endFieldset en fin de formulaire
            $this->end_fieldset .= '</fieldset>';
        } else {
            $this->html .= '</fieldset>';
        }

        return $this;
    }

    /**
     * Adds a Google recaptcha field
     * @param [string] $sitekey Google recaptcha key
     * @param [boolean] $center Center recaptcha (false|true)
     * @return $this
     */
    public function addRecaptcha($sitekey, $recaptcha_id = 'recaptcha', $center = false)
    {
        $start_wrapper = $this->setInputGroup('', 'start', 'elementsWrapper');
        $start_col     = $this->getElementCol('start', 'recaptcha');
        $end_col       = $this->getElementCol('end', 'recaptcha');
        $end_wrapper   = $this->setInputGroup('', 'end', 'elementsWrapper');
        $this->addHtml($start_wrapper);
        $this->addHtml($start_col);
        $center_style = '';
        if ($center === true) {
             $center_style = 'style="text-align:center;"';
        }
        $this->addHtml('<div class="recaptcha-wrapper"' . $center_style . '><div id="' . $recaptcha_id . '" class="g-recaptcha" data-sitekey="' . $sitekey . '"></div></div>');

        if ($this->has_recaptcha_error == true) {
            $this->addHtml('<p class="has-error ' . $this->options['textErrorClass']. '">' . $this->recaptcha_error_text . '</p>');
        }
        $this->addHtml('<br>');
        $this->addHtml($end_col);
        $this->addHtml($end_wrapper);

        // recaptcha callback must be defined before including Google's recaptcha/api.js
        // that's why it's added to the form html output.
        $this->recaptcha_js_callback = '<script>var recaptchaCallBack = function() {
    setTimeout(function() {
        jQuery(".g-recaptcha").each(function() {
            var recaptchaId = jQuery(this).attr("id");
            grecaptcha.render(recaptchaId, {
              "sitekey" : "' . $sitekey . '",
              "theme" : "light"
            });
        });
        scaleCaptcha();
    }, 200);
};</script>';
        $this->addPlugin('recaptcha', '#' . $recaptcha_id . '', 'default');

        return $this;
    }

    /**
     * shortcut to prepend or append icon to an input
     * @param string $input_name the name of target input
     * @param string $icon_html  icon html code
     * @param string $pos        before | after
     * @return $this
     */
    public function addIcon($input_name, $icon_html, $pos)
    {
        if ($this->framework == 'bs3' || $this->framework == 'bs4' || $this->framework == 'foundation') {
            $this->addInputWrapper('<div class="input-group"></div>', $input_name);
        } elseif ($this->framework == 'material') {
            $icon_html = $this->addClass('prefix', $icon_html);
        }
        if ($this->framework == 'bs4') {
            if ($pos == 'before') {
                $input_group_addon_class = 'input-group-prepend';
            } else {
                $input_group_addon_class = 'input-group-append';
            }
            $this->addHtml('<div class="' . $input_group_addon_class . '"><span class="input-group-text">' . $icon_html . '</span></div>', $input_name, $pos);
        } elseif (!empty($this->options['inputGroupAddonClass'])) {
            $this->addHtml('<div class="' . $this->options['inputGroupAddonClass'] . '">' . $icon_html . '</div>', $input_name, $pos);
        } else {
            $this->addHtml($icon_html, $input_name, $pos);
        }

        return $this;
    }

    /**
     * Start a hidden block
     * which can contain any element and html
     * Hiden block will be shown on $parent_field change
     * if $parent_field value matches one of $show_values
     * @param  string $parent_field name of the field which will trigger show/hide
     * @param  string $show_values  single value or comma separated values which will trigger show.
     * @param  boolean $inverse  if true, dependent fields will be shown if any other value than $show_values is selected.
     * @return $this
     */
    public function startDependentFields($parent_field, $show_values, $inverse = false)
    {
        $this->addHtml('<div class="hidden-wrapper off" data-parent="' . $parent_field . '" data-show-values="' . $show_values . '" data-inverse="' . $inverse . '">');
        if (!in_array('dependent-fields', $this->js_plugins)) {
            $this->addPlugin('dependent-fields', '.hidden-wrapper');
        }

        // register data to transmit to dependent fields. Data will be used to know if dependent fields are required or not, depending on parent posted value.
        $this->current_dependent_data = array(
            'parent_field' => $parent_field,
            'show_values'  => $show_values,
            'inverse'      => $inverse
        );

        return $this;
    }

    /**
     * Ends a dependent field block
     * @return $this
     */
    public function endDependentFields()
    {
        $this->addHtml('</div>');

        // reset current_dependent_data
        $this->current_dependent_data = array();

        return $this;
    }

    /**
     * Allows to group inputs in the same wrapper
     *
     * Arguments can be :
     *     -    a single array with fieldnames to group
     *     OR
     *     -    fieldnames given as string
     *
     * @param string|array $input1 The name of the first input of the group
     *                             OR
     *                             array including all fieldnames
     *
     * @param string $input2 The name of the second input of the group
     * @param string $input3 [optional] The name of the third input of the group
     * @param string $input4 [optional] The name of the fourth input of the group
     * @param string ...etc.
     * @return $this
     */
    public function groupInputs($input1, $input2 = '', $input3 = '', $input4 = '', $input5 = '', $input6 = '', $input7 = '', $input8 = '', $input9 = '', $input10 = '', $input11 = '', $input12 = '')
    {
        $group = array();

        if (is_array($input1)) {
            // if array given
            for ($i=1; $i <= count($input1); $i++) {
                $group['input_' . $i] = $input1[$i-1];
            }
        } else {
            // if strings given
            for ($i=1; $i < 13; $i++) {
                $input = 'input' . $i;
                if (!empty($$input)) {
                    $group['input_' . $i] = $$input;
                }
            }
        }
        $this->input_grouped[] = $group;

        return $this;
    }

    /*=================================
    js-plugins
    =================================*/

    /**
     * Gets and tests plugins url ($this->plugins_url).
     * Adds a javascript plugin to the selected field(s)
     * @param string $plugin_name     The name of the plugin,
     *                                must be the name of the xml file
     *                                in plugins-config dir
     *                                without extension.
     *                                Example : colorpicker
     * @param string $selector        The jQuery style selector.
     *                                Examples : #colorpicker
     *                                .colorpicker
     * @param string $js_content      (Optional) The xml node where your plugin code is
     *                                in plugins-config/[your-plugin.xml] file
     * @param array  $js_replacements (Optional) An associative array containing
     *                                the strings to search as keys
     *                                and replacement values as data.
     *                                Strings will be replaced with data
     *                                in js_code xml node of your
     *                                plugins-config/[your-plugin.xml] file.
     * @return $this
     */
    public function addPlugin($plugin_name, $selector, $js_content = 'default', $js_replacements = '')
    {
        $keep_original_selector_plugins = array('modal', 'popover');

        if (!in_array($plugin_name, $keep_original_selector_plugins)) {
            // add the form id to selector
            if (!preg_match('`' . $this->form_ID . '`', $selector) && !preg_match('`form`', $selector)) {
                $selector = '#' . $this->form_ID . ' ' . $selector;
            }
        }
        if ($plugin_name == 'icheck' && $this->framework == 'material') {
            $this->buildErrorMsg('ICHECK PLUGIN + MATERIAL<br>iCheck plugin cannot be used with Material plugin.');
        } elseif ($plugin_name == 'select2' && $this->framework == 'material') {
            $this->buildErrorMsg('SELECT2 PLUGIN + MATERIAL<br>select2 plugin cannot be used with Material plugin.');
        } elseif ($plugin_name == 'nice-check' && $this->framework == 'material') {
            $this->buildErrorMsg('NICE-CHECK PLUGIN + MATERIAL<br>nice-check plugin cannot be used with Material plugin.');
        } elseif ($plugin_name == 'bootstrap-select' && $this->framework == 'bs4') {
            $this->buildErrorMsg('BOOTSTRAP SELECT PLUGIN + BOOTSTRAP 4<br>Bootstrap Select plugin cannot be used with Bootstrap 4.<br>Use <em>select2</em> instead.');
        }
        if (!in_array($plugin_name, $this->js_plugins)) {
            $this->js_plugins[] = $plugin_name;
        }
        if (!isset($this->js_fields[$plugin_name]) || !in_array($selector, $this->js_fields[$plugin_name])) {
            $this->js_fields[$plugin_name][]       = $selector;
            $this->js_content[$plugin_name][]      = $js_content;
            $this->js_replacements[$plugin_name][] = $js_replacements;
        }

        return $this;
    }

    /**
     * Prints html code to include css or js dependancies required by plugins.
     * i.e.:
     *     <link rel="stylesheet" ... />
     *     <script src="..."></script>
     *
     * @param string  $type                 value : 'css' or 'js'
     * @param boolean $debug                (Optional) True or false.
     *                                      If true, the html code will be displayed
     * @param boolean $display              (Optional) True or false.
     *                                      If false, the html code will be returned but not displayed.
     * @param boolean $combine_and_compress (Optional) True or false.
     *                                      If true, dependancies are combined and compressed into plugins/min/ folder.
     * @return $this
     */
    public function printIncludes($type, $debug = false, $display = true, $combine_and_compress = true)
    {
        $this->getIncludes($type);
        $normal_output        = '';
        $compressed_output    = '';
        if (!empty($this->framework)) {
            $compressed_file_url  = $this->plugins_url . 'min/' . $type . '/' . $this->framework . '-' . $this->form_ID . '.min.' . $type;
            $compressed_file_path = $this->plugins_path . 'min/' . $type . '/' . $this->framework . '-' . $this->form_ID . '.min.' . $type;
        } else {
            $compressed_file_url  = $this->plugins_url . 'min/' . $type . '/' . $this->form_ID . '.min.' . $type;
            $compressed_file_path = $this->plugins_path . 'min/' . $type . '/' . $this->form_ID . '.min.' . $type;
        }
        $final_output         = '';
        $inline_style         = '';
        $plugins_files        = array();

        // will check if we have to recreate minified combined file later
        $rewrite_combined_file = false;

        if ($combine_and_compress === true) {
            if (!file_exists($compressed_file_path)) {
                // if minified combined file doesn't exist
                $rewrite_combined_file = true;
            } else {
                $parent_file_time   = filemtime($_SERVER['SCRIPT_FILENAME']);
                $combined_file_time = filemtime($compressed_file_path);
                if ($parent_file_time > $combined_file_time) {
                    $rewrite_combined_file = true;
                }
            }
        }
        if ($type == 'css') {
            $compressed_output = '<link href="' . $compressed_file_url . '" rel="stylesheet" media="screen">' . "\n";
            foreach ($this->css_includes as $plugin_name) {
                foreach ($plugin_name as $css_file) {
                    if (strlen($css_file) > 0) {
                        if (!in_array($css_file, Form::$instances['css_files'])) {
                            Form::$instances['css_files'][] = $css_file;
                            if (!preg_match('`^(http(s)?:)?//`', $css_file)) { // if absolute path in XML
                                $css_file = $this->plugins_url . $css_file;
                            }
                            if ($combine_and_compress === true) {
                                $plugins_files[] = $css_file;
                                if ($rewrite_combined_file !== true) {
                                    // check if we have to recreate minified combined file
                                    $ftime = @filemtime(str_replace($this->plugins_url, $this->plugins_path, $css_file));
                                    if ($ftime === false || $ftime > $combined_file_time) {
                                        // if file is newer than combined one
                                        $rewrite_combined_file = true;
                                    }
                                }
                            }
                            $normal_output .= '<link href="' . $css_file . '" rel="stylesheet" media="screen">' . "\n";
                        }
                    }
                }
            }

            // add inline style to adjust bottom margins with horizontal forms and grouped fields on small screens
            $inline_style .= '/* Small screens */';
            $inline_style .= '@media only screen and (max-width : 767px) {';
            $inline_style .= '    .form-group > div[class*="col-"]:not(:last-child) {';
            $inline_style .= '        margin-bottom: 15px;';
            $inline_style .= '    }';
            $inline_style .= '}';
            $inline_style .= '.append {';
            $inline_style .= '    margin-left: 7px;';
            $inline_style .= '}';
            $inline_style .= '.prepend {';
            $inline_style .= '    margin-right: 7px;';
            $inline_style .= '}';

            // Bootstrap 4 hack to add vertical padding to the 1st radio|checkbox - https://codepen.io/migli/pen/yPKbwL
            if ($this->framework == 'bs4') {
                $inline_style .= '@media (min-width: 576px) {';
                $inline_style .= '    .col-form-label ~ div[class*="col"] {';
                $inline_style .= '        padding-top: 8px; /* col-form-label padding */';
                $inline_style .= '    }';
                $inline_style .= '    .col-form-label ~ div[class*="col"] .select2-container {';
                $inline_style .= '        margin-top: -0.5rem;';
                $inline_style .= '    }';
                $inline_style .= '    input[type="radio"] ~ .check {';
                $inline_style .= '        top: 0;';
                $inline_style .= '    }';
                $inline_style .= '    input[type="checkbox"] ~ .check {';
                $inline_style .= '        top: 0;';
                $inline_style .= '    }';
                $inline_style .= '}';
                $inline_style .= '.form-check-input {';
                $inline_style .= '    margin-top: .4rem;';
                $inline_style .= '}';
                if (in_array('select2', $this->js_plugins)) {
                    $inline_style .= '.select2-results__option.flag {';
                    $inline_style .= '    padding-top: 0;';
                    $inline_style .= '    padding-bottom: 0;';
                    $inline_style .= '}';
                    $inline_style .= '.f32 .flag .text {';
                    $inline_style .= '    font-size: 14px;';
                    $inline_style .= '}';
                }
                if (in_array('icheck', $this->js_plugins)) {
                    $inline_style .= 'div[class*="icheckbox_"], div[class*="iradio_"] {';
                    $inline_style .= '    margin: 0 10px 0 20px;';
                    $inline_style .= '}';
                }
            } elseif ($this->framework == 'foundation') {
                // Foundation specific css
                $inline_style .= '.bootstrap-select {';
                $inline_style .= '    margin-bottom: 14px;';
                $inline_style .= '}';
                $inline_style .= '.bootstrap-select button.btn {';
                $inline_style .= '    line-height: 1.5;';
                $inline_style .= '    height: 2.4375rem;';
                $inline_style .= '    border-radius: 0;';
                $inline_style .= '}';
                $inline_style .= '.bootstrap-select.btn-group .dropdown-toggle .caret {';
                $inline_style .= '    margin-top: 0;';
                $inline_style .= '}';
                $inline_style .= '.bootstrap-select .pull-left {';
                $inline_style .= '    float: left;';
                $inline_style .= '}';
                $inline_style .= '.bootstrap-select.input-group-field {';
                $inline_style .= '    margin-bottom: 0;';
                $inline_style .= '}';
                $inline_style .= '.bootstrap-select + .help-text {';
                $inline_style .= '    margin-top: 7px;';
                $inline_style .= '}';
                $inline_style .= '.select2-container {';
                $inline_style .= '    margin-bottom: 1rem !important;';
                $inline_style .= '}';
                $inline_style .= '.mce-tinymce.mce-panel {';
                $inline_style .= '    margin-bottom: 14px !important;';
                $inline_style .= '}';
                $inline_style .= '.foundation-radio, .main-label, .radio-inline, .foundation-checkbox {';
                $inline_style .= '    margin-bottom: 7px !important;';
                $inline_style .= '}';
                $inline_style .= '.foundation-radio [class^="iradio"], .foundation-radio [class^="icheck"] {';
                $inline_style .= '    margin-left: 0 !important;';
                $inline_style .= '}';
                $inline_style .= '.foundation-radio [class^="iradio"], .foundation-radio [class^="icheck"], div[class^="iradio"], div[class^="icheck"] {';
                $inline_style .= '    margin-left: 0 !important;';
                $inline_style .= '}';
                $inline_style .= 'div[class^="iradio"]+label {';
                $inline_style .= '    display: inline-block;';
                $inline_style .= '    padding-right: 20px;';
                $inline_style .= '    padding-bottom: 20px;';
                $inline_style .= '}';
                $inline_style .= '.button.warning {';
                $inline_style .= '    background-color: #E8AF13;';
                $inline_style .= '}';
                $inline_style .= '.button.danger {';
                $inline_style .= '    color: #fefefe;';
                $inline_style .= '    background-color: #a53b2a;';
                $inline_style .= '}';
                $inline_style .= '.input-group-label.fa {';
                $inline_style .= '    display: flex;';
                $inline_style .= '}';
                $inline_style .= '.intl-tel-input {';
                $inline_style .= '    margin-bottom: 1rem !important;';
                $inline_style .= '}';
            } elseif ($this->framework == 'material') {
                // Material specific css
                $inline_style .= '.form-horizontal .bootstrap-select.btn-group {';
                $inline_style .= '    margin-top: 0;';
                $inline_style .= '}';
                $inline_style .= '.form-horizontal .bootstrap-select.btn-group .btn {';
                $inline_style .= '    height: 31px;';
                $inline_style .= '}';
            }
            if (in_array('recaptcha', $this->js_plugins)) {
                $inline_style .= '.recaptcha-wrapper {';
                $inline_style .= '    width: 100%;';
                $inline_style .= '    overflow: hidden;';
                $inline_style .= '}';
                $inline_style .= '.g-recaptcha {';
                $inline_style .= '    transform-origin: left top;';
                $inline_style .= '    -webkit-transform-origin: left top;';
                $inline_style .= '}';
            }
            if ($this->has_file === true) {
                $inline_style .= '.fade.in {';
                $inline_style .= '    opacity: 1 !important;';
                $inline_style .= '}';
            }

            // overwrite bootstrap-select z-index for modal
            $inline_style .= '.bootstrap-select.btn-group.bs-container .dropdown-menu {z-index: 10000 !important}' . "\n";
            $normal_output .= '<style type="text/css">' . $inline_style .'</style>' . "\n";
        } elseif ($type == 'js') {
            $compressed_output = '<script src="' . $compressed_file_url . '"></script>';
            foreach ($this->js_includes as $plugin_name) {
                foreach ($plugin_name as $js_file) {
                    if (strlen($js_file) > 0) {
                        if (!in_array($js_file, Form::$instances['js_files'])) {
                            Form::$instances['js_files'][] = $js_file;
                            $is_google_recaptcha = false;
                            if (preg_match('`www.google.com/recaptcha`', $js_file)) {
                                $is_google_recaptcha = true;
                            };
                            if (!preg_match('`^(http(s)?:)?//`', $js_file)) { // if relative path in XML
                                $js_file = $this->plugins_url . $js_file;
                            }
                            if ($combine_and_compress === true) {
                                $plugins_files[] = $js_file;
                                if ($rewrite_combined_file !== true) {
                                    // recaptcha is not combined
                                    if ($is_google_recaptcha === true) {
                                        $compressed_output = '<script src="' . $js_file . '"  async defer></script>' . $compressed_output;
                                    } else {
                                        // check if we have to recreate minified combined file
                                        $ftime = @filemtime(str_replace($this->plugins_url, $this->plugins_path, $js_file));
                                        if ($ftime === false || $ftime > $combined_file_time) {
                                            // if file is newer than combined one
                                            $rewrite_combined_file = true;
                                        }
                                    }
                                }
                            }
                            $normal_output .= '<script src="' . $js_file . '"></script>' . "\n";
                        }
                    }
                }
            }
        }
        if ($combine_and_compress === true) {
            $final_output = $compressed_output;
            $error_msg = '';
            if ($rewrite_combined_file === true) {
                if (!file_exists($this->plugins_path . '/min')) {
                    if (!mkdir($this->plugins_path . '/min')) {
                        $error_msg = 'Unable to create <strong><i>/min</i></strong> folder<br>Try to change permissions (chmod 0755) on your server<br> or call <i>printIncludes()</i> function with <i>$combine_and_compress</i> argument to <i>false</i><br><br>$form->printIncludes(\'css\', false, true, false);';
                    }
                }
                if (!file_exists($this->plugins_path . '/min/' . $type)) {
                    if (!mkdir($this->plugins_path . '/min/' . $type)) {
                        $error_msg = 'Unable to create <strong><i>/min/' . $type . '</i></strong> folder<br>Try to change permissions (chmod 0755) on your server<br> or call <i>printIncludes()</i> function with <i>$combine_and_compress</i> argument to <i>false</i><br><br>$form->printIncludes(\'css\', false, true, false);';
                    }
                }
                $new_file_content = '';
                foreach ($plugins_files as $file) {
                    $is_google_recaptcha = false;
                    if (preg_match('`www.google.com/recaptcha`', $file)) {
                        $is_google_recaptcha = true;
                    };
                    if ($is_google_recaptcha !== true) {
                        $current_file_content = file_get_contents(str_replace($this->plugins_url, $this->plugins_path, $file)) . "\n";
                        if ($type == 'css') {
                            // workaround to use $this in preg_replace_callback with php < 5.4
                            $self = $this;
                            // convert relative urls to absolute
                            $current_file_content = preg_replace_callback(
                                '`url\(([^\)]+)\)`',
                                function ($matches) use ($self, $file) {
                                    return 'url(' . $self->rel2abs($matches[1], $file) . ')';
                                },
                                $current_file_content
                            );
                        }
                        $new_file_content .= $current_file_content;
                    }
                }
                if ($type == 'css') {
                    $new_file_content .= $inline_style;
                }

                // remove comments
                $new_file_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $new_file_content);
                try {
                    //open file for writing
                    if (file_put_contents($compressed_file_path, $new_file_content) === false) {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    $error_msg = 'Unable to open ' . $compressed_file_path . ' for writing.<br>Try to change permissions (chmod 0755) on your server<br> or call <i>printIncludes()</i> function with <i>$combine_and_compress</i> argument to <i>false</i><br><br>$form->printIncludes(\'css\', false, true, false);';
                }
                if (!empty($error_msg)) {
                    $this->buildErrorMsg($error_msg);
                    $final_output = $normal_output;
                }
            }
        } else {
            $final_output = $normal_output;
        }
        if ($debug == true) {
            echo '<pre class="prettyprint">' . htmlspecialchars($final_output) . '</pre>';
        }
        if ($display === false) {
            return $final_output;
        } else {
            echo $final_output;
        }

        return $this;
    }

    /**
     * convert relative url to absolute
     *
     * @param string $rel   the url to convert
     * @param string $base  the url of the origin file
     *
     * @return the absolute url
     */
    private function rel2abs($rel, $base)
    {
        // remove beginning & ending quotes
        $rel = preg_replace('`^([\'"]?)([^\'"]+)([\'"]?)$`', '$2', $rel);

        // parse base URL  and convert to local variables: $scheme, $host,  $path
        extract(parse_url($base));

        if (strpos($rel, "//") === 0) {
            return $scheme . ':' . $rel;
        }

        // return if already absolute URL
        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        // queries and anchors
        if ($rel[0] == '#' || $rel[0] == '?') {
            return $base . $rel;
        }

        // remove non-directory element from path
        $path = preg_replace('#/[^/]*$#', '', $path);

        // destroy path if relative url points to root
        if ($rel[0] ==  '/') {
            $path = '';
        }

        // dirty absolute URL
        $abs = $host . $path . "/" . $rel;

        // replace '//' or  '/./' or '/foo/../' with '/'
        $abs = preg_replace("/(\/\.?\/)/", "/", $abs);
        $abs = preg_replace("/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs);

        // absolute URL is ready!
        return $scheme . '://' . $abs;
    }

    /**
     * Prints js code generated by plugins.
     * @param boolean $debug   (Optional) True or false.
     *                         If true, the html code will be displayed
     * @param boolean $display (Optional) True or false.
     *                         If false, the html code will be returned but not displayed.
     * @return $this
     */
    public function printJsCode($debug = false, $display = true)
    {
        $this->getJsCode();
        if ($debug == true) {
            echo '<pre class="prettyprint">' . htmlspecialchars($this->js_code) . '</pre>';
            echo '<pre class="prettyprint">' . htmlspecialchars($this->fileupload_js_code) . '</pre>';
        }
        if ($display === false) {
            return $this->js_code . $this->fileupload_js_code;
        } else {
            echo $this->js_code;
            echo $this->fileupload_js_code;
        }

        return $this;
    }

    /* =============================================
    popover
    ============================================= */

    /**
     * wrap form in a popover
     * @param string $popover_link the id of the link which triggers popover
     * @param array  $options      (Optional) An associative array containing
     *                             'placement' : auto,top,right,bottom,left,top-right,top-left,bottom-right,bottom-left,auto-top,auto-right,auto-bottom,auto-left,horizontal,vertical
     *                             'width' : 'auto' or number
     *                             'height' : 'auto' or number
     *                             'closeable' : display close button or not
     *                             'animation' : pop with animation,values: null, pop,fade
     *                             'backdrop' : if backdrop is set to true, popover will use backdrop on open
     * @return $this
     */
    public function popover($popover_link, $options = array())
    {
        $default_options = array(
            'placement' => 'bottom',
            'width' =>  'auto',
            'height' =>  'auto',
            'closeable' => true,
            'animation' => 'pop',
            'backdrop' => true
        );
        $replacements = array('%formID%' => $this->form_ID);
        foreach ($default_options as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
            if (is_numeric($options[$key])) {
                $replacements['%' . $key . '%'] = $options[$key];
            } elseif (is_bool($options[$key])) {
                if ($options[$key] === true) {
                    $replacements['%' . $key . '%'] = "true";
                } else {
                    $replacements['%' . $key . '%'] = "false";
                }
            } else {
                $replacements['%' . $key . '%'] = "'" . $options[$key] . "'";
            }
        }
        $this->addPlugin('popover', $popover_link, 'default', $replacements);
        $this->form_start_wrapper = '<div class="hide hidden d-none">';
        $this->form_start_wrapper .= '<div id="' . $this->form_ID . '-content">';
        $this->form_end_wrapper = '</div>';
        $this->form_end_wrapper .= '</div>';

        return $this;
    }

    /* =============================================
    modal
    ============================================= */

    /**
     * wrap form in a modal
     * @param string $modal_target href attribute of the link to modal
     * @return $this
     */
    public function modal($modal_target)
    {
        $replacements = array('%formID%' => $this->form_ID);
        $this->addPlugin('modal', $modal_target, 'default', $replacements);
        $this->form_start_wrapper = '<div class="remodal" data-remodal-id="' . str_replace('#', '', $modal_target) . '" id="' . str_replace('#', '', $modal_target) . '">';
        $this->form_start_wrapper .= '<button type="button" data-remodal-action="close" class="remodal-close"></button>';
        $this->form_end_wrapper = '</div>';

        return $this;
    }

    /*=================================
    render
    =================================*/

    /**
     * Renders the html code of the form.
     *
     * @param boolean $debug   (Optional) True or false.
     *                         If true, the html code will be displayed
     * @param boolean $display (Optional) True or false.
     *                         If false, the html code will be returned but not displayed.
     * @return $this
     *
     */
    public function render($debug = false, $display = true)
    {
        // wrapper for popover | remodal plugins
        $html = $this->form_start_wrapper;

        if (!empty($this->error_msg)) { // if iCheck used with material
            $html .= $this->error_msg;
        }
        if (!empty($_SERVER['QUERY_STRING'])) {
            $get = '?' . $_SERVER['QUERY_STRING'];
        }
        if (empty($this->action)) {
            $this->action = htmlspecialchars($_SERVER["PHP_SELF"]);
        }
        if ($this->btn_reset != '') {
            $html .= $this->btn_reset;
        }
        $html .= '<form ';
        if (!empty($this->form_ID)) {
            $html .= 'id="' . $this->form_ID . '" ';
        }
        $html .= 'action="' . $this->action;
        if (isset($get) and $this->add_get_vars === true) {
            $html .= $get;
        }
        $html .= '" method="' . $this->method . '"';
        if ($this->has_file === true) {
            $html .= ' enctype="multipart/form-data"';
        }

        /* layout */

        $attr = $this->getAttributes($this->form_attr);

        if ($this->layout == 'horizontal' && !empty($this->options['formHorizontalClass'])) {
            $attr = $this->addClass($this->options['formHorizontalClass'], $attr);
        } elseif ($this->layout == 'inline' && !empty($this->options['formInlineClass'])) {
            $attr = $this->addClass($this->options['formInlineClass'], $attr);
        } elseif (!empty($this->options['formVerticalClass'])) {
            $attr = $this->addClass($this->options['formVerticalClass'], $attr);
        }
        $material_class = '';
        if ($this->framework == 'material') {
            $attr = $this->addClass('material-form', $attr);
        }
        if (!empty($attr)) {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($this->hidden_fields)) {
            $html .= '<div>' . $this->hidden_fields . '</div>';
        }
        $html .= $this->html;
        $html .= $this->btn_submit;
        if (!empty($this->btn_cancel)) {
            $html .= $this->btn_cancel;
        }
        if (!empty($this->txt)) {
            $html .= $this->txt;
        }
        if (!empty($this->end_fieldset)) {
            $html .= $this->end_fieldset;
        }
        $html .= '</form>';
        $html .= $this->form_end_wrapper;

        // add Recaptcha js callback function
        if (!empty($this->recaptcha_js_callback)) {
            $html .= $this->recaptcha_js_callback;
        }

        if ($debug == true) {
            $html = $this->cleanHtmlOutput($html); // beautify html
        }

        if ($this->options['ajax'] === true) { // if ajax option enabled
            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                $cssfiles = preg_replace("/\r|\n/", "", $this->printIncludes('css', false, false));
                $jsfiles  = preg_replace("/\r|\n/", "", $this->printIncludes('js', false, false));
                $html .= $jsfiles;
            }

            // script to submit with ajax
            $script = '<script>' . "\n";
            $script .= '    jQuery(document).ready(function () {' . "\n";
            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                $script .= '        jQuery("head").append(\'' . $cssfiles . '\');' . "\n";
            }
            $script .= '        var $form       = jQuery("#' . $this->form_ID . '");' . "\n";
            $script .= '        var submitEvent = "submit";' . "\n";
            $script .= '        if (typeof(FormValidation) != "undefined") {' . "\n";
            $script .= '            submitEvent = "success.form.fv";' . "\n";
            $script .= '        }' . "\n";
            $script .= '        $form.on(submitEvent, function (e) {' . "\n";
            $script .= '            e.preventDefault();' . "\n";
            $script .= '            jQuery.ajax({' . "\n";
            $script .= '                url: jQuery(this).attr("action"),' . "\n";
            $script .= '                type: "POST",' . "\n";
            $script .= '                data: jQuery(this).serialize()' . "\n";
            $script .= '            }).done(function (data) {' . "\n";
            $script .= '                jQuery(target).html(data);' . "\n";
            $script .= '            });' . "\n";
            $script .= '        });' . "\n";
            $script .= '    });' . "\n";
            $script .= '</script>' . "\n";

            $html .= $script;
            $html .= $this->printJsCode(false, false);
        }
        if ($debug == true) {
            echo '<pre class="prettyprint">' . htmlspecialchars($html) . '</pre>';
        } elseif ($display === false) {
            return $html;
        } else {
            echo $html;
        }

        return $this;
    }

    /**
     * set html output linebreaks and indent
     * @param  string $html
     * @return string clean html
     */
    public function cleanHtmlOutput($html)
    {
        include_once dirname(__FILE__) . '/FormatHtml.php';
        $cleaning_object = new FormatHtml();

        // set linebreaks & indent
        $html = $cleaning_object->html($html);

        // remove empty lines
        // $html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $html);

        return $html;
    }


    /* =============================================
    validation
    ============================================= */

    /**
     * create Validator object and auto-validate all required fields
     * @param  string $form_ID the form ID
     * @return object          the Validator
     */
    public static function validate($form_ID, $lang = 'en')
    {
        include_once 'Validator/Validator.php';
        include_once 'Validator/Exception.php';
        $validator = new Validator($_POST, $lang);
        $required = $_SESSION[$form_ID]['required_fields'];
        foreach ($required as $field) {
            $do_validate = true;

            // if dependent field, test parent posted value & validate only if parent posted value matches condition
            if (!empty($_SESSION[$form_ID]['required_fields_conditions'][$field])) {
                $parent_field = $_SESSION[$form_ID]['required_fields_conditions'][$field]['parent_field'];
                $show_values  = preg_split('`,(\s)?`', $_SESSION[$form_ID]['required_fields_conditions'][$field]['show_values']);
                $inverse      = $_SESSION[$form_ID]['required_fields_conditions'][$field]['inverse'];

                if (!isset($_POST[$parent_field])) {
                    // if parent field is not posted (nested dependent fields)
                    $do_validate = false;
                } elseif (!in_array($_POST[$parent_field], $show_values) && $inverse === false) {
                    // if posted parent value doesn't match show values
                    $do_validate = false;
                } elseif (in_array($_POST[$parent_field], $show_values) && $inverse === true) {
                    // if posted parent value does match show values but dependent is inverted
                    $do_validate = false;
                }
            }
            if ($do_validate === true) {
                if (isset($_POST[$field]) && is_array($_POST[$field])) {
                    $field = $field . '.0';
                }
                $validator->required()->validate($field);
            }
        }

        return $validator;
    }

    /*=================================
    email sending
    =================================*/

    public static function sendMail($options, $smtp_settings = array())
    {
        $default_options = array(
            'sender_email'          => '',
            'sender_name'           => '',
            'reply_to_email'        => '',
            'recipient_email'       => '',
            'cc'                    => '',
            'bcc'                   => '',
            'subject'               => 'Contact',
            'attachments'           => '',
            'template'              => 'default.html',
            'human_readable_labels' => true,
            'values'                => $_POST,
            'filter_values'         => '',
            'custom_replacements'   => array(),
            'sent_message'          => '<p class="alert alert-success">Your message has been successfully sent !</p>',
            'debug'                 => false,
            'smtp'                  => false
        );

        /* replace default options with user's */

        foreach ($default_options as $key => $value) {
            if (isset($options[$key])) {
                $$key = $options[$key];
            } else {
                $$key = $value;
            }
        }
        require_once 'mailer/phpmailer/src/PHPMailer.php';
        require_once 'mailer/phpmailer/src/SMTP.php';
        require_once 'mailer/phpmailer/src/Exception.php';
        require_once 'mailer/pelago/Emogrifier.php';
        require_once 'mailer/phpmailer/extras/htmlfilter.php';
        $mail = new PHPMailer;
        try {
            // if smtp
            if ($smtp === true) {
                if ($debug === true) {
                    $mail->SMTPDebug = 3;                           // Enable verbose debug output
                }
                $mail->isSMTP();                                    // Set mailer to use SMTP
                $mail->Host       = $smtp_settings['host'];         // Specify main and backup SMTP servers
                $mail->SMTPAuth   = $smtp_settings['smtp_auth'];    // Enable SMTP authentication
                $mail->Username   = $smtp_settings['username'];     // SMTP username
                $mail->Password   = $smtp_settings['password'];     // SMTP password
                $mail->SMTPSecure = $smtp_settings['smtp_secure'];  // Enable TLS encryption, `ssl` also accepted
                $mail->Port       = $smtp_settings['port'];         // TCP port to connect to
            }

            if ($sender_name != '') {
                if ($reply_to_email !== '') {
                    $mail->addReplyTo($reply_to_email);
                } else {
                    $mail->addReplyTo($sender_email, $sender_name);
                }
                $mail->From     = $sender_email;
                $mail->FromName = $sender_name;
            } else {
                if ($reply_to_email !== '') {
                    $mail->addReplyTo($reply_to_email);
                } else {
                    $mail->addReplyTo($sender_email);
                }
                $mail->From     = $sender_email;
                $mail->FromName = $sender_email;
            }
            $indiAdress = explode(',', $recipient_email);
            foreach ($indiAdress as $key => $value) {
                $mail->addAddress(trim($value));
            }
            if ($bcc != '') {
                $indiBCC = explode(',', $bcc);
                foreach ($indiBCC as $key => $value) {
                    $mail->addBCC(trim($value));
                }
            }
            if ($cc != '') {
                $indiCC = explode(',', $cc);
                foreach ($indiCC as $key => $value) {
                    $mail->addCC(trim($value));
                }
            }
            if ($attachments != '') {
                /*
                    =============================================
                    single file :
                    =============================================

                    $attachments = 'path/to/file';

                    =============================================
                    multiple files separated with commas :
                    =============================================

                    $attachments = 'path/to/file_1, path/to/file_2';

                    =============================================
                    single file with file_path + file_name :
                    (specially for posted files)
                    =============================================

                    $attachments =  arrray(
                                        'file_path' => 'path/to/file.jpg', // complete path with filename
                                        'file_name' => 'my-file.jpg'
                                    )

                    =============================================
                    multiple files array containing :
                        sub-arrays with file_path + file_name
                        or file_name strings
                    =============================================

                    $attachments =  arrray(
                                        'path/to/file_1',
                                        'path/to/file_2',
                                        arrray(
                                            'file_path' => 'path/to/file.jpg', // complete path with filename
                                            'file_name' => 'my-file.jpg'
                                        ),
                                        ...
                                    )
                 */

                if (is_array($attachments)) {
                    if (isset($attachments['file_path'])) {
                        $mail->addAttachment($attachments['file_path'], $attachments['file_name']);
                    } else {
                        foreach ($attachments as $key => $value) {
                            if (is_array($value)) {
                                $mail->addAttachment($value["file_path"], $value["file_name"]);
                            } else {
                                $attach = explode(",", $attachments);
                                foreach ($attach as $key => $value) {
                                    $mail->addAttachment(trim($value));
                                }
                            }
                        }
                    }
                } else {
                    $attach = explode(",", $attachments);
                    foreach ($attach as $key => $value) {
                        $mail->addAttachment(trim($value));
                    }
                }
            }
            $filter = explode(',', $filter_values);

            // filter recaptcha
            $filter[] = 'g-recaptcha-response';

            // add token to filter
            foreach ($values as $key => $value) {
                if (preg_match('`-token$`', $key) || preg_match('`submit-btn$`', $key)) {
                    $filter[] = $key;
                }
            }

            // sanitize filter values
            for ($i = 0; $i < count($filter); $i++) {
                $filter[$i] = trim(mb_strtolower($filter[$i]));
            }
            $mail->Subject = $subject;
        } catch (Exception $e) { //Catch all kinds of bad addressing
            throw new Exception($e);
        }
        try {
            /* Load html & css templates */

            $html_template_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, pathinfo(__FILE__, PATHINFO_DIRNAME) . '/mailer/email-templates/' . $template);
            $html_template_custom_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, pathinfo(__FILE__, PATHINFO_DIRNAME) . '/mailer/email-templates-custom/' . $template);

            if (file_exists($html_template_custom_path)) {
                $template_error_msg = '';
                $debug_msg = '';
                // try to load html template in email-templates-custom dir
                if (($html = file_get_contents($html_template_custom_path)) === false) {
                    $template_error_msg = 'Html template file doesn\'t exists';
                    $debug_msg          = $html_template_custom_path;
                }
            } elseif (file_exists($html_template_path)) {
                // try to load html template in email-templates dir
                if (($html = file_get_contents($html_template_path)) === false) {
                    $template_error_msg = 'Html template file doesn\'t exists';
                    $debug_msg          = $html_template_path;
                }
            } else {
                $template_error_msg = 'Html template file doesn\'t exists';
                $debug_msg          = $html_template_path;
            }
            $css_template_path        = str_replace('.html', '.css', $html_template_path);
            $css_template_custom_path = str_replace('.html', '.css', $html_template_custom_path);

            if (file_exists($css_template_custom_path) && empty($template_error_msg)) {
                // try to load css template in email-templates-custom dir
                if (($css = file_get_contents($css_template_custom_path)) === false) {
                    $template_error_msg = 'CSS template file doesn\'t exists';
                    $debug_msg          = $css_template_custom_path;
                }
            } elseif (file_exists($css_template_path) && empty($template_error_msg)) {
                // try to load css template in email-templates dir
                if (($css = file_get_contents($css_template_path)) === false) {
                    $template_error_msg = 'CSS template file doesn\'t exists';
                    $debug_msg          = $css_template_path;
                }
            } elseif (empty($template_error_msg)) {
                $template_error_msg = 'CSS template file doesn\'t exists';
                $debug_msg          = $css_template_path;
            }

            /* If html|css template not found */

            if (!empty($template_error_msg)) {
                if ($debug === true) {
                    $template_error_msg = $debug_msg . '<br>' . $template_error_msg;
                }

                throw new \Exception('<div style="line-height:30px;border-radius:5px;border-bottom:1px solid #ac2925;background-color: #c9302c;margin:10px auto;"><p style="color:#fff;text-align:center;font-size:16px;margin:0">' . $template_error_msg . '</p></div>');
            }

            /* Automatic table including all but filtered values */

            $email_table = '<table class="one-column">';
            $email_table .= '<tbody>';

            // prepare regex for human_readable_labels
            $find = array('`([a-zA-Z0-9])-([a-zA-Z0-9])`', '`([a-zA-Z0-9])_([a-zA-Z0-9])`');
            $replace = array('$1 $2', '$1 $2');
            foreach ($values as $key => $value) {
                if (!in_array(mb_strtolower($key), $filter)) {
                    // replace key (label) with human_readable_label
                    if ($human_readable_labels === true) {
                        $key = preg_replace($find, $replace, $key);
                    }
                    if (!is_array($value)) {
                        $email_table .= '<tr>';

                            // replace with custom if key exists
                        if (is_array($custom_replacements) && in_array($key, array_keys($custom_replacements))) {
                            $email_table .= '<th class="inner">' . ucfirst($custom_replacements[$key]) . ': ' . '</th>';
                        } else {
                            $email_table .= '<th class="inner">' . ucfirst($key) . ': ' . '</th>';
                        }
                        $email_table .= '<td class="inner">' . nl2br($value) . '</td>';
                        $email_table .= '</tr>';
                    } else {
                        foreach ($value as $key_array => $value_array) {
                            if (!is_array($value_array)) {
                                $email_table .= '<tr>';
                                $email_table .= '<th class="inner">' . ucfirst($key) . ' ' . ($key_array + 1) . ': ' . '</th>';
                                $email_table .= '<td class="inner">' . $value_array . '</td>';
                                $email_table .= '</tr>';
                            }
                        }
                    }
                }
            }
            $email_table .= '</tbody>';
            $email_table .= '</table>';

            $html = str_replace('{table}', $email_table, $html);
            $html = str_replace('{subject}', $subject, $html);


            /* replacements in html */

            // first, replace posted values in html
            foreach ($values as $key => $value) {
                if (!in_array(mb_strtolower($key), $filter) && !is_array($value)) {
                    $html = str_replace('{' . $key . '}', $value, $html);
                }
            }

            // then replace custom replacements in html
            foreach ($custom_replacements as $key => $value) {
                if (!in_array(mb_strtolower($key), $filter) && !is_array($value)) {
                    $html = str_replace('{' . $key . '}', $value, $html);
                }
            }

            /* custom replacements in css */

            if (!empty($css)) {
                foreach ($custom_replacements as $key => $value) {
                    if (!in_array(mb_strtolower($key), $filter) && !is_array($value)) {
                        $css = str_replace('{' . $key . '}', $value, $css);
                    }
                }

                $emogrifier = new \Pelago\Emogrifier();
                $emogrifier->addExcludedSelector('br');
                $emogrifier->enableCssToHtmlMapping();
                $emogrifier->setHtml($html);
                $emogrifier->setCss($css);
                $mergedHtml = $emogrifier->emogrify();
            } else {
                $mergedHtml = $html;
            }
            HTMLFilter($mergedHtml, '', false);
        } catch (\Exception $e) { //Catch all content errors
            return $e->getMessage();
        }
        $mail->msgHTML($mergedHtml, dirname(__FILE__), true);
        $charset = mb_detect_encoding($mergedHtml);
        $mail->CharSet = $charset;
        if (!$mail->send()) {
            if ($debug === true) {
                $sent_message = '<p class="alert alert-danger">Mailer Error: ' . $mail->ErrorInfo . '</p>';

                return $sent_message;
            }
        } else {
            return $sent_message;
        }
    }

    /**
     * stores the ID of the form to be cleared.
     * when next instance is created it will not store posted values in session
     * unsets all sessions vars attached to the form
     * @param string $form_ID
     */
    public static function clear($form_ID)
    {
        $_SESSION['clear_form'][$form_ID] = true;
        if (isset($_SESSION[$form_ID]['fields'])) {
            foreach ($_SESSION[$form_ID]['fields'] as $key => $name) {
                unset($_SESSION[$form_ID]['fields'][$key]);
                unset($_SESSION[$form_ID][$name]);
            }
        }
    }

    /*=================================
    protected & static functions
    =================================*/

    /**
     * output element html code including wrapper, label, element with group, icons, ...
     * @param  string $start_wrapper        i.e. <div class="row">
     * @param  string $end_wrapper          i.e. </div>
     * @param  string $start_label          i.e. <label class="small-3 columns text-right middle main-label">Vertical radio
     * @param  string $end_label            i.e. </label>
     * @param  string $start_col            i.e. <div class="small-9 columns">
     * @param  string $end_col              i.e. </div>
     * @param  string $element_html         i.e. <fieldset><input type="radio" id="vertical-radio_0" name="vertical-radio" value="1" ><label for="vertical-radio_0" >One</label></fieldset>
     * @param  boolean $wrap_into_label
     * @return string element html code
     */
    protected function outputElement($start_wrapper, $end_wrapper, $start_label, $end_label, $start_col, $end_col, $element_html, $wrap_into_label)
    {
        $html = $start_wrapper;
        if (!empty($start_label) && $wrap_into_label === true) {
            $html .= $start_label . $start_col . $element_html . $end_col . $end_label;
        } else {
            $label_col = 0;
            if (preg_match('`([0-9]+)`', $this->options['horizontalLabelCol'], $out)) {
                $label_col = $out[1];
            }
            if ($this->framework === 'material') {
                if ($label_col < 1) {
                    // label after element if label is into col div
                    $html .= $start_col . $element_html . $start_label . $end_label . $end_col;
                } else {
                    // label before element
                    $html .= $start_label . $end_label . $start_col . $element_html . $end_col;
                }
            } else {
                // label before element
                $html .= $start_label . $end_label . $start_col . $element_html . $end_col;
            }
        }
        $html .= $end_wrapper;

        return $html;
    }

    /**
     * wrap element itself with error div if input is grouped or if $label is empty
     * @param  string $name
     * @param  string $label
     * @param  string $pos   'start' | 'end'
     * @return string div start | end
     */
    protected function getErrorInputWrapper($name, $label, $pos)
    {
        $isGrouped = $this->isGrouped($name);
        if (($isGrouped == true || $label == '') && !empty($this->options['wrapperErrorClass'])) {
            if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields))) {
                if ($pos == 'start') {
                    return '<div class="' . $this->options['wrapperErrorClass'] . '">';
                } else {
                    return '</div>';
                }
            }
        }
    }

    /**
     * check if name belongs to a group input
     * @param  string  $name
     * @return boolean
     */
    protected function isGrouped($name)
    {
        foreach ($this->input_grouped as $input_grouped) {
            if (in_array($name, $this->input_grouped)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Allows to group inputs in the same wrapper (12 inputs max.)
     * @param string $name        The input name
     * @param string $wrapper_pos start | end
     * @param string $wrapper | end     elementsWrapper | checkboxWrapper | radioWrapper | buttonWrapper
     */
    protected function setInputGroup($name, $wrapper_pos, $wrapper)
    {
        if (!empty($this->options[$wrapper])) {
            $grouped = false;
            $input_pos = ''; // start | middle | end
            $pattern_2_wrappers = '`<([^>]+)><([^>]+)></([^>]+)></([^>]+)>`';
            if ($wrapper == 'elementsWrapper') {
                $start_wrapper = $this->elements_start_wrapper;
                $end_wrapper   = $this->elements_end_wrapper;
            } elseif ($wrapper == 'checkboxWrapper') {
                $start_wrapper = $this->checkbox_start_wrapper;
                $end_wrapper   = $this->checkbox_end_wrapper;
            } elseif ($wrapper == 'radioWrapper') {
                $start_wrapper = $this->radio_start_wrapper;
                $end_wrapper   = $this->radio_end_wrapper;
            } elseif ($wrapper == 'buttonWrapper') {
                $start_wrapper = $this->button_start_wrapper;
                $end_wrapper   = $this->button_end_wrapper;
            }
            if ($wrapper_pos == 'start') {
                foreach ($this->input_grouped as $input_grouped) {
                    for ($i=1; $i < 12; $i++) {
                        $input = 'input_' . ($i + 1);
                        $next_input = 'input_' . ($i + 2);
                        if (isset($input_grouped[$input]) && $name == $input_grouped[$input]) {
                            $grouped = true;
                            if (isset($input_grouped[$next_input])) {
                                $input_pos = 'middle';
                            } else {
                                $input_pos = 'end';
                            }
                        }
                    }
                }
                if ($grouped == true && $input_pos == 'middle' || $input_pos == 'end') {
                    if (preg_match($pattern_2_wrappers, $this->options[$wrapper], $out)) {
                        return '<' . $out[2] . '>';
                    } else {
                        return '';
                    }
                } else {
                    return $start_wrapper;
                }
            } elseif ($wrapper_pos == 'end') {
                foreach ($this->input_grouped as $input_grouped) {
                    for ($i=0; $i < 12; $i++) {
                        $input = 'input_' . ($i + 1);
                        $next_input = 'input_' . ($i + 2);
                        if ($i == 0 && $name == $input_grouped[$input]) {
                            $grouped = true;
                            $input_pos = 'start';
                        } elseif (isset($input_grouped[$input]) && $name == $input_grouped[$input] && isset($input_grouped[$next_input])) {
                            $input_pos = 'middle';
                        }
                    }
                }
                if ($grouped === true && $input_pos == 'start' || $input_pos == 'middle') {
                    if (preg_match($pattern_2_wrappers, $this->options[$wrapper], $out)) {
                        return '</' . $out[3] . '>';
                    } else {
                        return '';
                    }
                } else {
                    return $end_wrapper;
                }
            }
        } else {
            return '';
        }
    }

    /**
    * When the form is posted, values are passed in session
    * to be keeped and displayed again if posted values aren't correct.
    */
    protected function registerField($name, $attr)
    {
        $form_ID = $this->form_ID;
        if (!isset($_SESSION[$form_ID])) {
            $_SESSION[$form_ID]           = array();
        }
        if (!isset($_SESSION[$form_ID]['fields'])) {
            $_SESSION[$form_ID]['fields'] = array();
        }
        if (!isset($_SESSION[$form_ID]['required_fields'])) {
            $_SESSION[$form_ID]['required_fields'] = array();
        }
        $name = preg_replace('`(.*)\[\]`', '$1', $name); // if $name is an array, we register without hooks ([])
        if (!in_array($name, $_SESSION[$form_ID]['fields'])) {
            $_SESSION[$form_ID]['fields'][] = $name;
        }
        if (!isset($_SESSION[$form_ID]['required_fields_conditions'])) {
            $_SESSION[$form_ID]['required_fields_conditions'] = array();
        } else {
            // reset dependent field condition
            $_SESSION[$form_ID]['required_fields_conditions'][$name] = '';
        }

        // register required fields
        if (preg_match('`required`', $attr) && !in_array($name, $_SESSION[$form_ID]['required_fields'])) {
            $_SESSION[$form_ID]['required_fields'][] = $name;
        }

        // register required conditions if we're into dependent fields
        if (!empty($this->current_dependent_data)) {
            $_SESSION[$form_ID]['required_fields_conditions'][$name] = $this->current_dependent_data;
        }
    }

    /**
     * register posted values in session
     * @param string $form_ID
     */
    public static function registerValues($form_ID)
    {
        if (!isset($_SESSION[$form_ID])) {
            $_SESSION[$form_ID]           = array();
            $_SESSION[$form_ID]['fields'] = array();
        }
        foreach ($_SESSION[$form_ID]['fields'] as $index => $name) {
            if (isset($_POST[$name])) {
                $value = $_POST[$name];
                if (!is_array($value)) {
                    $_SESSION[$form_ID][$name] = trim($value);
                    // echo $name . ' => ' . $value . '<br>';
                } else {
                    $_SESSION[$form_ID][$name] = array();
                    foreach ($value as $array_key => $array_value) {
                        $_SESSION[$form_ID][$name][$array_key] = trim($array_value);
                        // echo $name . ' ' . $array_key . ' ' . $array_value . '<br>';
                    }
                }
            } else { // if checkbox unchecked, it hasn't been posted => we store empty value
                $_SESSION[$form_ID][$name] = '';
            }
        }
    }

    /**
     * merge previously registered session vars => values of each registered form
     * @param  array $forms_array array of forms IDs to merge
     * @return array pairs of names => values
     *                           ex : $values[$field_name] = $value
     */
    public static function mergeValues($form_names_array)
    {
        $values = array();
        foreach ($form_names_array as $form_name) {
            $fields = $_SESSION[$form_name]['fields'];
            foreach ($fields as $key => $field_name) {
                if (isset($_SESSION[$form_name][$field_name])) {
                    $values[$field_name] = $_SESSION[$form_name][$field_name];
                }
            }
        }

        return $values;
    }

    /**
     * generate token to protect against CSRF
     * @return string $token
     */
    protected function generateToken()
    {
        $token = uniqid(rand(), true);
        $token_name = $this->form_ID;
        $_SESSION[$token_name . '_token'] = $token;
        $_SESSION[$token_name . '_token_time'] = time();

        return $token;
    }

    /**
     * validate token to protect against CSRF
     */
    public static function testToken($form_ID)
    {
        if (isset($_SESSION[$form_ID . '_token']) && isset($_SESSION[$form_ID . '_token_time']) && isset($_POST[$form_ID . '-token'])) {
            if ($_SESSION[$form_ID.'_token'] == $_POST[$form_ID . '-token']) {
                // validity for token = 1800 sec. = 30mn.
                if ($_SESSION[$form_ID.'_token_time'] >= (time() - 1800)) {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /**
    * Gets errors stored in session
    */
    protected function registerErrors()
    {
        $form_ID = $this->form_ID;
        foreach ($_SESSION['errors'][$form_ID] as $field => $message) {
            /* remove dot syntax (field.index => field */

            $field = preg_replace('`\.(.*)+`', '', $field);
            $this->error_fields[$field] = $message;
        }

        // register recaptcha error
        if (isset($_SESSION['errors'][$form_ID])) {
            $error_keys = array_keys($_SESSION['errors'][$form_ID]);
            if (in_array('g-recaptcha-response', $error_keys)) {
                $this->has_recaptcha_error  = true;
                $this->recaptcha_error_text = $_SESSION['errors'][$form_ID]['g-recaptcha-response'];
            }
        }
    }

    /**
    * Gets html code to start | end elements wrappers
    *
    * @param string $html The html wrapper code
    * @param string $pos 'start' or 'end'
    * @return string Starting or ending html tag
    */
    protected function defineWrapper($html, $pos)
    {
        /* if 2 wrappers */

        $pattern_2_wrappers = '`<([^>]+)><([^>]+)></([^>]+)></([^>]+)>`';
        if (preg_match($pattern_2_wrappers, $html, $out)) {
            if ($pos == 'start') {
                return '<' . $out[1] . '>' . '<' . $out[2] . '>';
            } else {
                return '</' . $out[3] . '>' . '</' . $out[4] . '>';
            }
        }

        /* if only 1 wrapper */

        $pattern_1_wrapper = '`<([^>]+)></([^>]+)>`';
        if (preg_match($pattern_1_wrapper, $html, $out)) {
            if ($pos == 'start') {
                return '<' . $out[1] . '>';
            } else {
                return '</' . $out[2] . '>';
            }
        }
    }

    /**
    * Adds warnings class to elements wrappers
    *
    * @param string $start_wrapper The html wrapper code
    * @param string $name The element name
    * @return string Wrapper Html tag with or without error class
    */
    protected function addErrorWrapper($name, $pos)
    {
        $error_wrapper = '';
        if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields)) && !empty($this->options['wrapperErrorClass'])) {
            if ($pos == 'start') {
                $error_wrapper = '<div class="' . $this->options['wrapperErrorClass'] . '">';
            } else {
                $error_wrapper = '</div>';
            }
        }

        return $error_wrapper;
    }

    /**
    * Gets element value
    *
    * Returns default value if not empty
    * Else returns session value if it exists
    * Else returns an emplty string
    *
    * @param string $name The element name
    * @param string $value The default value
    * @return string The element value
    */
    protected function getValue($name, $value)
    {
        $form_ID = $this->form_ID;
        if (!empty($value) || is_numeric($value)) {
            return htmlspecialchars($value);
        } elseif (isset($_SESSION[$form_ID][$name])) {
            return htmlspecialchars($_SESSION[$form_ID][$name]);
        } elseif (preg_match('`([^\\[]+)\[([^\\]]+)\]`', $name, $out)) { // arrays
            $array_name = $out[1];
            $array_key = $out[2];
            if (isset($_SESSION[$form_ID][$array_name][$array_key])) {
                return htmlspecialchars($_SESSION[$form_ID][$array_name][$array_key]);
            } else {
                return htmlspecialchars($_SESSION[$form_ID][$name]);
            }
        } else {
            return '';
        }
    }

    /**
    * Adds warnings if the form was posted with errors
    *
    * Warnings are stored in session, and will be displayed
    * even if your form was called back with header function.
    *
    * @param string $name The element name
    * @return string The html error
    */
    protected function getError($name)
    {
        $no_hook_name = str_replace('[]', '', $name);
        if (in_array($no_hook_name, array_keys($this->error_fields))) {
            return '<p class="' . $this->options['textErrorClass'] . '">' . $this->error_fields[$no_hook_name] . '</p>';
        } else {
            return '';
        }
    }

    /**
    * Automaticaly adds requiredMark (see options) to labels's required fields
    * @param string $label The element label
    * @param string $attr The element attributes
    * @return string The element label if required html markup if needed
    */
    protected function getRequired($label, $attr)
    {
        if (preg_match('`required`', $attr)) {
            if (preg_match('`([^>]+>)+([^<:]+)(: )*(</[^>]+>)*`', $label, $out)) {
                for ($i = 1; $i < 5; $i++) {
                    if (!isset($out[$i])) {
                        $out[$i] = '';
                    }
                }
                return $out[1] . $out[2] . $out[3] . $this->options['requiredMark'] . $out[4];
            } else {
                preg_match('`([^:]+)(: )*(.*)`', $label, $out);

                return $out[1] . $this->options['requiredMark'] . $out[2] . $out[3];
            }
        } else {
            return $label;
        }
    }

    /**
    * Returns linearised attributes.
    * @param string $attr The element attributes
    * @return string Linearised attributes
    *                Exemple : size=30, required=required => size="30" required="required"
    */
    protected function getAttributes($attr)
    {
        if (empty($attr)) {
            return '';
        } else {
            $clean_attr = '';

            // replace protected commas with expression
            $attr = str_replace('\\,', '[comma]', $attr);

            // replace protected equals with expression
            $attr = str_replace('\\=', '[equal]', $attr);

            // split with commas
            $attr = preg_split('`,`', $attr);
            foreach ($attr as $a) {
                // add quotes
                if (preg_match('`=`', $a)) {
                    $a = preg_replace('`\s*=\s*`', '="', trim($a)) .  '" ';
                } else {
                    // no quote for single attributes
                    $a = trim($a) . ' ';
                }
                $clean_attr .= $a;
            }

            // get back protected commas, equals and trim
            $clean_attr = trim(str_replace(array('[comma]', '[equal]'), array(',', '='), $clean_attr));

            return $clean_attr;
        }
    }

    /**
     * used for chexkboxes | select options only.
     * adds or remove 'checked' or 'selected' according to default / session values.
     * @param  string $field_name
     * @param  string $value
     * @param  string $attr       ex : checked="checked", class="my-class"
     * @param  string $field_type select | checkbox
     * @return string $attr
     */
    protected function getCheckedOrSelected($field_name, $value, $attr, $field_type)
    {
        $form_ID = $this->form_ID;
        $name_without_hook = preg_replace('`(.*)\[\]`', '$1', $field_name);
        if ($field_type == 'select') {
            $attr_selected = 'selected';
        } else {
            $attr_selected = 'checked';
        }
        if (isset($_SESSION[$form_ID][$name_without_hook])) {
            if (!is_array($_SESSION[$form_ID][$name_without_hook])) {
                if ($_SESSION[$form_ID][$name_without_hook] == $value) {
                    if (!preg_match('`' . $attr_selected . '`', $attr)) {
                        $attr = $this->addAttribute($attr_selected, $attr);
                    }
                } else { // we remove 'selected' from $checkbox_attr as user has previously selected another, memorized in session.
                    $attr = $this->removeAttr($attr_selected, $attr);
                }
            } else {
                if (in_array($value, $_SESSION[$form_ID][$name_without_hook])) {
                    if (!preg_match('`' . $attr_selected . '`', $attr)) {
                        $attr = $this->addAttribute($attr_selected, $attr);
                    }
                } else { // we remove 'selected' from $attr as user has previously selected another, memorized in session.
                    $attr = $this->removeAttr('selected', $attr);
                }
            }
        }

        return $attr;
    }

    /**
     * used to add 'checked="checked"' | 'selected="selected"' to select options or checkboxes
     * @param  string $attr_to_add
     * @param  string $attr_string
     * @return string attributes with the added one
     */
    protected function addAttribute($attr_to_add, $attr_string)
    {
        if (empty($attr_string)) {
            $attr_string = ' ' . $attr_to_add . '="' . $attr_to_add . '"';
        } else {
            $attr_string = ' ' . $attr_to_add . '="' . $attr_to_add . '" ' . $attr_string;
        }

        return $attr_string;
    }

    /**
     * removes specific attribute from list (ex : removes 'checked="checked"' from radio in other than default has been stored in session)
     * @param  string $attr_to_remove ex : checked
     * @param  string $attr_string    ex : checked="checked", required
     * @return string attributes without the removed one
     */
    protected function removeAttr($attr_to_remove, $attr_string)
    {
        if (preg_match('`,(\s)?' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?)?`', $attr_string)) { // beginning comma
            $attr_string = preg_replace('`,(\s)?' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?)?`', '', $attr_string);
        } elseif (preg_match('`' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?(\s)?)?,`', $attr_string)) { // ending comma
            $attr_string = preg_replace('`' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?(\s)?)?,`', '', $attr_string);
        } elseif (preg_match('`' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?(\s)?)?`', $attr_string)) { // no comma
            $attr_string = preg_replace('`' . $attr_to_remove . '((\s)?=(\s)?([\'|"])?' . $attr_to_remove . '([\'|"])?(\s)?)?`', '', $attr_string);
        }

        return $attr_string;
    }

    /**
    * Gets element ID.
    *
    * @param string $name The element name
    * @param string $attr The element attributes
    * @return string returns ID present in $attr if any,
    *                else returns field's name
    */
    protected function getID($name, $attr)
    {
        if (empty($attr)) {  //
            $array_values['id'] = preg_replace('`\[\]`', '', $name); // if $name is an array, we delete '[]'
            $array_values['attributs'] = '';
        } else {
            if (preg_match('`id="([a-zA-Z0-9_-]+)"`', $attr, $out)) {
                $array_values['id'] = $out[1];
                $array_values['attributs'] = preg_replace('`id="([a-zA-Z0-9_-]+)"`', '', $attr);
            } else {
                $array_values['id'] = preg_replace('`\[\]`', '', $name);
                $array_values['attributs'] = $attr;
            }
        }

        return $array_values;
    }

    /**
    * Add warnings class to elements wrappers
    *
    * @param string $name The element name
    * @return string The element start_wrapper html code with or without error class
    */
    protected function addElementErrorClass($name)
    {
        if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields))) {
            if (preg_match('`class="`', $start_wrapper)) {
                $start_wrapper = preg_replace('`class="`', 'class="' . $this->options['wrapperErrorClass'] . ' ', $start_wrapper);
            } else {
                $start_wrapper = preg_replace('`>`', ' class="' . $this->options['wrapperErrorClass'] . '">', $start_wrapper);
            }
        }

        return $start_wrapper;
    }

    /**
    * Add new class to $attr.(see options).
    *
    * @param string $newclassname The new class
    * @param string $attr The element attributes
    * @return string $attr including new class.
    */
    protected function addClass($newclassname, $attr)
    {

        /* if $attr already contains a class we keep it and add newclassname */

        if (preg_match('`class="([^"]+)"`', $attr, $out)) {
            $new_class =  'class="' . $out[1] . ' ' . $newclassname . '"';

            return preg_replace('`class="([^"]+)"`', $new_class, $attr);
        } else { // if $attr contains no class we add elementClass
            return $attr . ' class="' . $newclassname . '"';
        }
    }

    /**
    * Add default element class to $attr.(see options).
    *
    * @param string $name The element name
    * @param string $attr The element attributes
    * @return string The element class with the one defined in options added.
    */
    protected function addElementClass($name, $attr)
    {

        /* we retrieve error if any */

        $error_class = '';
        if (in_array(str_replace('[]', '', $name), array_keys($this->error_fields)) && !empty($this->options['elementsErrorClass'])) {
            $error_class = ' ' . $this->options['elementsErrorClass'];
        }

        /* if $attr already contains a class we keep it and add elementClass */

        if (preg_match('`class="([^"]+)"`', $attr, $out)) {
            $new_class =  'class="' . $out[1] . ' ' . $this->options['elementsClass'] . $error_class . '"';

            return preg_replace('`class="([^"]+)"`', $new_class, $attr);
        } else { /* if $attr contains no class we add elementClass */
            if (empty($this->options['elementsClass'])) {
                if (empty($error_class)) {
                    return $attr;
                } else {
                    return ' class="' . $error_class . '"';
                }
            } else {
                return $attr . ' class="' . $this->options['elementsClass'] . $error_class . '"';
            }
        }
    }

    /**
    * Gets label class. (see options).
    *
    * @param string $element (Optional) 'standardElement', 'radio' or 'checkbox'
    * @param string $inline True or false
    * @return string The element class defined in form options.
    */
    protected function getLabelClass($element = 'standardElement', $inline = '')
    {
        $class = '';
        if ($element == 'standardElement' || $element == 'fileinput') { // input, textarea, select
            if ($this->layout == 'horizontal') {
                if ($this->options['horizontalLabelWrapper'] === false) {
                    $class = $this->options['horizontalLabelCol'] . ' ' . $this->options['horizontalLabelClass'];
                } else {
                    $class = $this->options['horizontalLabelClass'];
                }
                if ($element == 'fileinput') {
                    $class .= ' fileinput-label';
                }
            } elseif ($this->layout == 'vertical') {
                if ($this->options['verticalLabelWrapper'] === false) {
                    $class     = $this->options['verticalLabelClass'];
                }
            }
            $class = trim($class);
            if (!empty($class)) {
                return ' class="' . $class . '"';
            } else {
                return '';
            }
        } elseif ($element == 'radio') {
            if ($inline === true && !empty($this->options['inlineRadioLabelClass'])) {
                return ' class="' . $this->options['inlineRadioLabelClass'] . '"';
            } elseif ($inline === false && !empty($this->options['verticalRadioLabelClass'])) {
                return ' class="' . $this->options['verticalRadioLabelClass'] . '"';
            } else {
                return '';
            }
        } elseif ($element == 'checkbox') {
            if ($inline === true && !empty($this->options['inlineCheckboxLabelClass'])) {
                return ' class="' . $this->options['inlineCheckboxLabelClass'] . '"';
            } elseif ($inline === false && !empty($this->options['verticalCheckboxLabelClass'])) {
                return ' class="' . $this->options['verticalCheckboxLabelClass'] . '"';
            } else {
                return '';
            }
        }
    }

    /**
    * Wrapps label with col if needed (see options).
    *
    * @param string $pos 'start' or 'end'
    * @return string The html code of the element wrapper.
    */
    protected function getLabelCol($pos)
    {
        if ($this->layout == 'horizontal' && !empty($this->options['horizontalLabelCol'])) {
            if ($pos == 'start') {
                return '<div class="' . $this->options['horizontalLabelCol'] . '">';
            } else { // end
                return '</div>';
            }
        } elseif ($this->layout == 'vertical' && !empty($this->options['verticalLabelClass'])) {
            if ($pos == 'start') {
                return '<div class="' . $this->options['verticalLabelClass'] . '">';
            } else { // end
                return '</div>';
            }
        } else {
            return '';
        }
    }

    /**
    * Wrapps element with col if needed (see options).
    *
    * @param string $pos 'start' or 'end'
    * @param string $label The element label
    * @param string $field_type input|textarea|select|radio|checkbox|button|recaptcha
    * @return string The html code of the element wrapper.
    */
    protected function getElementCol($pos, $field_type, $label = '')
    {
        if ($this->layout == 'horizontal' && !empty($this->options['horizontalElementCol'])) {
            if ($pos == 'start') {
                if (empty($label)) {
                    return '<div class="' . $this->options['horizontalOffsetCol'] . ' ' . $this->options['horizontalElementCol'] . '">';
                } else {
                    return '<div class="' . $this->options['horizontalElementCol'] . '">';
                }
            } else { // end
                return '</div>';
            }
        } elseif ($this->framework == 'foundation' && ($field_type == 'radio' || $field_type == 'checkbox' || $field_type == 'button' || $field_type == 'recaptcha')) {
            if ($pos == 'start') {
                // foundation checkboxes, radio & button need column wrapper - in both horizontal & vertical forms
                return '<div class="' . $this->options['horizontalElementCol'] . '">';
            } else { // end
                return '</div>';
            }
        } else {
            return '';
        }
    }

    /**
     * Gets html code to insert just berore or after the element
     *
     * @param  string $name                    The element name
     * @param  string $pos                     'start' or 'end'
     * @param  string $pos_relative_to_wrapper 'inside_wrapper' or 'outside_wrapper' (input groups are inside wrapper, help blocks are outside). Only for inputs.
     * @return string $return                  The html code to insert just before or after the element, inside or outside element wrapper
     *
     */
    protected function getHtmlElementContent($name, $pos, $pos_relative_to_wrapper = '')
    {
        $return = '';
        if (isset($this->html_element_content[$name][$pos])) {
            for ($i=0; $i < count($this->html_element_content[$name][$pos]); $i++) {
                $html = $this->html_element_content[$name][$pos][$i];
                if (empty($pos_relative_to_wrapper)) {
                    $return .= $html;
                } else {
                    if ($pos_relative_to_wrapper == 'outside_wrapper') {
                        if ($this->framework == 'foundation') {
                            if ($pos_relative_to_wrapper == 'outside_wrapper' && preg_match('`help-text`', $html)) {
                                $return .= $html;
                            }
                        } elseif (!preg_match('`' . $this->options['inputGroupAddonClass'] . '`', $html)) {
                            $return .= $html;
                        }
                    } elseif ($pos_relative_to_wrapper == 'inside_wrapper' && preg_match('`' . $this->options['inputGroupAddonClass'] . '`', $html) && ($this->framework != 'foundation' || ($this->framework == 'foundation' && !preg_match('`help-text`', $html)))) {
                        $return .= $html;
                    }
                }
            }

            return $return;
        } else {
            return '';
        }
    }

    /**
    * Gets css or js files needed for js plugins
    *
    * @param string $type 'css' or 'js'
    * @return html code to include needed files
    */
    protected function getIncludes($type)
    {
        foreach ($this->js_plugins as $plugin_name) {
            for ($i=0; $i < count($this->js_content[$plugin_name]); $i++) {
                $js_content      = $this->js_content[$plugin_name][$i]; // default, custom, ...
                $js_replacements = $this->js_replacements[$plugin_name][$i];
                if (file_exists(dirname(__FILE__) . '/plugins-config-custom/' . $plugin_name . '.xml')) {
                    // if custom config xml file
                    $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config-custom/' . $plugin_name . '.xml');

                    // if node doesn't exist, fallback to default xml
                    if (!isset($xml->{$js_content})) {
                        $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/' . $plugin_name . '.xml');
                    }
                } else {
                    $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/' . $plugin_name . '.xml');
                }

                /* if custom include path doesn't exist, we keep default path */

                $path = '/root/' . $js_content . '/includes/' . $type . '/file';
                if ($xml->xpath($path) == false) {
                    $path = '/root/default/includes/' . $type . '/file';
                }
                $files = $xml->xpath($path);
                if (!isset($this->css_includes[$plugin_name])) {
                    $this->css_includes[$plugin_name] = array();
                }
                if (!isset($this->js_includes[$plugin_name])) {
                    $this->js_includes[$plugin_name] = array();
                }
                foreach ($files as $file) {
                    if (is_array($js_replacements)) {
                        foreach ($js_replacements as $key => $value) {
                            $file = preg_replace('`' . $key . '`', $value, $file);
                        }
                    }
                    if ($type == 'css' && !in_array($file, $this->css_includes[$plugin_name])) {
                        $this->css_includes[$plugin_name][] = (string) $file;
                    } elseif ($type == 'js' && !in_array($file, $this->js_includes[$plugin_name])) {
                        $this->js_includes[$plugin_name][] = (string) $file;
                    }
                }
            }
        }
    }

    /**
     * Gets js code generated by js plugins
     * Scroll to user error if any
     */
    protected function getJsCode()
    {
        $nbre_plugins = count($this->js_plugins);
        $recaptcha_js = '';
        $this->js_code = '<script type="text/javascript">' . "\n";
        $this->js_code .= $this->options['openDomReady'] . "\n";
        for ($i=0; $i < $nbre_plugins; $i++) {
            $plugin_name = $this->js_plugins[$i]; // ex : colorpicker
            $nbre = count($this->js_fields[$plugin_name]);
            for ($j=0; $j < $nbre; $j++) {
                $selector = $this->js_fields[$plugin_name][$j];
                $js_replacements = $this->js_replacements[$plugin_name][$j];
                $js_content    = $this->js_content[$plugin_name][$j];
                if (file_exists(dirname(__FILE__) . '/plugins-config-custom/' . $plugin_name . '.xml')) {
                    // if custom config xml file
                    $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config-custom/' . $plugin_name . '.xml');

                    // if node doesn't exist, fallback to default xml
                    if (!isset($xml->{$js_content})) {
                        $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/' . $plugin_name . '.xml');
                    }
                } else {
                    $xml = simplexml_load_file(dirname(__FILE__) . '/plugins-config/' . $plugin_name . '.xml');
                }
                if ($plugin_name == 'jquery-fileupload') { // fileupload
                    $this->fileupload_js_code .= preg_replace('`%selector%`', $selector, $xml->$js_content->js_code);
                } elseif ($plugin_name == 'recaptcha') {
                    $recaptcha_js .= preg_replace('`%selector%`', $selector, $xml->$js_content->js_code);
                } else { // others
                    $this->js_code .= preg_replace('`%selector%`', $selector, $xml->$js_content->js_code);
                }
                if (is_array($js_replacements)) {
                    foreach ($js_replacements as $key => $value) {
                        if ($plugin_name == 'jquery-fileupload') { // fileupload
                            $this->fileupload_js_code = preg_replace('`' . $key . '`', $value, $this->fileupload_js_code);
                        } elseif ($plugin_name == 'recaptcha') {
                            $recaptcha_js = preg_replace('`' . $key . '`', $value, $recaptcha_js);
                        } else { // others
                            $this->js_code = preg_replace('`' . $key . '`', $value, $this->js_code);
                        }
                    }
                }
            }
        }

        // scroll to user error
        if (!empty($this->options['wrapperErrorClass']) && !in_array('modal', $this->js_plugins) && !in_array('popover', $this->js_plugins)) {
            $this->js_code .= "\n" . '    if ($(".' . $this->options['wrapperErrorClass'] . '")[0]) {' . "\n";
            $this->js_code .= '        $("html, body").animate({' . "\n";
            $this->js_code .= '            scrollTop: $($(".' . $this->options['wrapperErrorClass'] . '")[0]).offset().top - 400' . "\n";
            $this->js_code .= '        }, 800);' . "\n";
            $this->js_code .= '    }' . "\n";
        }
        $this->js_code .= $this->options['closeDomReady'] . "\n";

        // recaptcha callback has to be outside domready
        $this->js_code .= $recaptcha_js;
        $this->js_code .= '</script>' . "\n";
    }

    /**
     * get plugins folder url from Form.php path + DOCUMENT_ROOT path
     * plugins_url will be the complete url to plugins dir
     * i.e. http(s)://www.your-site.com[/subfolder(s)]/phpformbuilder/plugins/
     *
     * @param string $forced_url    optional URL
     */
    public function setPluginsUrl($forced_url = '')
    {
        // reliable document_root (https://gist.github.com/jpsirois/424055)
        $root_path = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);
        if (!empty($forced_url)) {
            $this->plugins_url = $forced_url;
        } elseif (empty($this->plugins_url)) {
            $form_class_path    = dirname(__FILE__);
            $this->plugins_path = $form_class_path . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;

            // reliable document_root with symlinks resolved
            $info = new \SplFileInfo($root_path);
            $real_root_path = $info->getRealPath();

            // sanitize directory separator
            $form_class_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $form_class_path);
            $real_root_path  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $real_root_path);

            $this->plugins_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim(str_replace(array($real_root_path, DIRECTORY_SEPARATOR), array('', '/'), $this->plugins_path), '/');
        }
    }

    /**
     * display error message if
     *     - iCheck used with material
     * @param string $msg
     */
    protected function buildErrorMsg($msg)
    {
        $this->error_msg .= '<div style="line-height:30px;border-radius:5px;border-bottom:1px solid #ac2925;background-color: #c9302c;margin:10px auto;"><p style="color:#fff;text-align:center;font-size:16px;margin:0">' . $msg . '</p></div>';
    }
}
