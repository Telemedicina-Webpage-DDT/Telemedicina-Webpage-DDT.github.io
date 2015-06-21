<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

abstract class liveagent_Form_Base extends liveagent_Base {

    const TYPE_FORM = 'default';
    const TYPE_TEMPLATE = 'template';

    /**
     * @var HTMLForm
     */
    protected $form;
    private $settings;
    private $formName;
    protected $connectionSucc = false;   
    
    public function setErrorMessages(array $messages) {
        if (count($messages) == 0) {
            return;
        }
        $html = '<div class="error">';
        foreach ($messages as $message) {
            $html .= '<p><strong>' . __('ERROR', LIVEAGENT_PLUGIN_NAME) . '</strong>: ' . $message . '</p>';
        }
        $html .= $this->getUnderErrorMessagesText();
        $html .='</div>';
        $this->addHtml('errorMessages', $html);
    }
    
    protected function getUnderErrorMessagesText() {
    }
    
    public function setInfoMessages(array $messages) {
        if (count($messages) == 0) {
            return;
        }
        $html = '<div class="updated">';
        foreach ($messages as $message) {
            $html .= '<p>' . $message . '</p>';
        }
        $html .='</div>';
        $this->addHtml('infoMessages', $html);
    }
    
    protected function addTranslation($code, $translation) {                        
        $this->form->add('html', $code, $translation);
    }

    public function __construct($name = null, $action = null) {
        $this->formName = $name;
        if ($name !== null && $action !== null) {
            $this->loadSettingsString($name);
            $this->form = new HTMLForm($name, 'post', $action, '', $this->getType());
        } else {
            $this->form = new HTMLForm($name, '', '', '', $this->getType());
        }
        $this->initForm();
    }

    private function loadSettingsString($name) {
        ob_start();
        settings_fields($name);
        $this->settings = ob_get_contents();
        ob_end_clean();
    }

    protected abstract function getType();

    protected function initForm() {        
    }

    protected abstract function getTemplateFile();

    protected function addSubmit() {
        $this->form->add('submit', 'submit', __('Save', LIVEAGENT_PLUGIN_NAME), array('class'=>'button-primary'));
    }

    protected function addHtml($name, $code) {
        $this->form->add('html', $name, $code);
    }
    
    protected function addLink($name, $caption, $url, $target = '_blank') {
        $this->form->add('html', $name, '<a href="'.$url.'" target=".$target.">'.$caption.'</a>');
    }

    protected function getOption($name) {
        return get_option($name);
    }

    protected function addCheckbox($name, $templateName = null, $additionalCode = '') {
        if ($this->getOption($name) == 'true') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        if ($templateName === null) {
            $templateName = $name;
        }
        $this->form->add('html', $templateName, '<input type="checkbox" name="'.$name.'" id="'.$name.'_" value="true" '.$checked.' '.$additionalCode.'></input>');
    }

    protected function parseBlock($name, $variables) {
        $this->form->parseBlock($name, $variables);
    }

    protected function addSelect($name, $options) {
        //options = assoc. arr, key(value) and value(name) od select option
        $select = $this->form->add('select', $name, $this->getOption($name));
        $select->addOptions($options);
        return $select;
    }

    protected function addPassword($name, $size = 20, $class = null) {
        $options = array('size' => $size, 'value' => $this->getOption($name));
        if ($size !== null) {
            $options['size'] = $size;
        }
        if ($class !== null) {
            $options['class'] = $class;
        }
        $this->form->add('password', $name, '', $options);
    }

    protected function addTextBox($name, $size = 20, $class = null) {
        $options = array('value' => $this->getOption($name));
        if ($class !== null) {
            $options['class'] = $class;
        }
        if ($size !== null) {
            $options['size'] = $size;
        }
        $this->form->add('text', $name, '', $options);
    }

    protected function addTextArea($name, $cols=10, $rows=1, $class = null) {
        $value = $this->getOption($name);
        $this->form->add('html', $name, '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" id="'.$name.'" class="'.$class.'">'.$value.'</textarea>');
    }

    public function render($toVar = false) {
        if ($this->formName != null) {
            $this->form->add('html', 'form-settings', $this->settings);
        }
        return $this->form->render($this->getTemplateFile(), $toVar);
    }

    public function renderTemplate($templateFile) {
        $this->form->render($templateFile);
    }
}
?>