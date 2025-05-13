<?php 

/**
 * This class will validate and process the data.
 * Input must fully validate before it is passed to the handlers.
 * Handlers are responsible inserting or sending the data somewhere.
 */

namespace ModularityFrontendForm\DataProcessor;

class DataProcessor {
    public function __construct(
        private array $validators,
        private array $handlers
    ) {}

    /**
     * Process the data.
     *
     * @param array $data The data to process.
     * @return true|array True if the submission succeded, otherwise an array of errors.
     */
    public function process(array $data): true|array {
        $allErrors = [];

        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($data);

            if($validationResult->getIsValid()) {
                continue;
            }

            foreach($validationResult->getErrors() as $error) {
                $allErrors[] = $error;
            }
        }

        if(empty($allErrors)) {
            foreach ($this->handlers as $handler) {
                $handler->handle($data);
            }
            return true;
        }
        return $allErrors;
    }
}