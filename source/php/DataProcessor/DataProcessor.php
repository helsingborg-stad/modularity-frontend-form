<?php 

/**
 * This class will validate and process the data.
 * Input must fully validate before it is passed to the handlers.
 * Handlers are responsible inserting or sending the data somewhere.
 */

namespace ModularityFrontendForm\DataProcessor;

use ModularityFrontendForm\DataProcessor\DataProcessorInterface;
use WP_Error;

class DataProcessor implements DataProcessorInterface {

    private array $errors = [];
    private bool $useNullHandler = true;

    public function __construct(
        private array $validators,
        private array $handlers,
        private $nullHandler
    ) {}

    /**
     * @inheritDoc
     */
    public function process(array $data): bool {

        var_dump($data);
        die;

        //1. Validate the data
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($data);

            if($validationResult->getIsValid()) {
                continue;
            }

            foreach($validationResult->getErrors() as $error) {
                $this->errors[] = $error;
            }
        }

        //2. If there are no errors, process the data
        if(empty($this->errors)) {

            $this->useNullHandler = empty($this->handlers) ? true : false;

            foreach ($this->handlers as $handler) {

                $handlerResult = $handler->handle($data);

                if($handlerResult->isOk()) {continue;}

                foreach($handlerResult->getErrors() as $error) {
                    $this->errors[] = $error;
                }
            }

            if($this->useNullHandler === true) {
                $nullHandlerResult = $this->nullHandler->handle($data);
                if(!$nullHandlerResult->isOk()) {
                    foreach($nullHandlerResult->getErrors() as $error) {
                        $this->errors[] = $error;
                    }
                }
            }
        }
        
        return $this->errors ? false : true;
    }

    /**
     * @inheritDoc
     */
    public function getFirstError(): ?WP_Error {
        
        if(empty($this->errors)) {
            return null;
        }

        $wpError = $this->errors[0];

        $remainingErrors = $this->getRemainingErrors();
        if($remainingErrors) {
            foreach($remainingErrors as $error) {
                $wpError->add($error->get_error_code(), $error->get_error_message(), $error->get_error_data());
            }
        }
        return $wpError;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): ?array {
        return $this->errors ?: null;
    }

    /**
     * Get the remaining errors (not the primary/first error).
     * 
     * @return array|null An array of errors or null if no errors.
     */
    private function getRemainingErrors(): ?array {
        if(empty($this->errors)) {
            return null;
        }
        $remainingErrors = $this->errors;
        array_shift($remainingErrors);

        return $remainingErrors ?: null;
    }
}