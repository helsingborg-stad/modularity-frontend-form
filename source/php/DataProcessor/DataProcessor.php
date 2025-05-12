<?php 

namespace ModularityFrontendForm\DataProcessor;
use ModularityFrontendForm\Validator\ValidatorInterface;
use ModularityFrontendForm\Handler\HandlerInterface;
use RuntimeException;

class DataProcessor {
    public function __construct(
        private array $validators,
        private array $handlers
    ) {}

    public function process(array $data): void {
        foreach ($this->validators as $validator) {
            if (!$validator->validate($data)) {
                throw new RuntimeException('Validation failed');
            }
        }

        foreach ($this->handlers as $handler) {
            $handler->handle($data);
        }
    }
}