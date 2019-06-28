<?php

namespace Instamojo\Exception;

class InvalidRequestException extends JuspayException {
    public function __construct($errorMessage) {
        parent::__construct (null, null, $errorMessage);
    }
}