<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

interface FileHandlerInterface {
    /**
     * Handles file attachments
     * 
     * @param array $files The files to handle
     * 
     * @return void
     */
    public function handle(array $files): void;
}