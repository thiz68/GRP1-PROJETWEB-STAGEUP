<?php
namespace grp1\STAGEUP\Controllers;

abstract class Controller {
    protected $model = null;
    protected $templateEngine = null;

    public function __construct($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    protected function render($template, $data = []) {
        echo $this->templateEngine->render($template, $data);
    }
}
