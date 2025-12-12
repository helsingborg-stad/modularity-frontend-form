<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

class NullFileHandler implements FileHandlerInterface {
    /**
     * Handles file attachments
     * 
     * @param array $files The files to handle
     * 
     * @return void
     */
    public function handle(array $files): void {
        // Do nothing
    }
}