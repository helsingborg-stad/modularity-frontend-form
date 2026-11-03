<?php 

/**
 * This class will validate and process the data.
 * Input must fully validate before it is passed to the handlers.
 * Handlers are responsible inserting or sending the data somewhere.
 */

namespace ModularityFrontendForm\DataProcessor;

use ModularityFrontendForm\DataProcessor\DataProcessorInterface;
use ModularityFrontendForm\FieldFormatting\PhoneNormalizer;
use WP_Error;
use WP_REST_Request;

class DataProcessor implements DataProcessorInterface {

    private array $errors = [];
    private bool $useNullHandler = true;

    public function __construct(
        private array $validators,
        private array $handlers,
        private $nullHandler,
        private ?PhoneNormalizer $phoneNormalizer = null
    ) {}

    /**
     * @inheritDoc
     */
    public function process(array $data, WP_REST_Request $request): bool {

        //1. Validate the data
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($data, $request);

            if($validationResult->getIsValid()) {
                continue;
            }

            foreach($validationResult->getErrors() as $error) {
                $this->errors[] = $error;
            }
        }

        //2. If there are no errors, process the data
        if(empty($this->errors)) {
            $data = $this->phoneNormalizer?->normalize($data) ?? $data;

            $this->useNullHandler = empty($this->handlers) ? true : false;

            foreach ($this->handlers as $handler) {

                $handlerResult = $handler->handle($data, $request);

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

        return $this->errors[0];
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): ?array {
        return $this->errors ?: null;
    }

}