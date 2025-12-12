<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers\Response;

class FileHandlerResponse {

    private ?array $responseData = null;

    private string $status; 
    private string $message;


    public function get() {
        return[
            'status' => $this->status,
            'message' => $this->message,
            'data' => [
              [
                'id' => 123,
                'url' => 'https://example.com/wp-content/uploads/2024/06/file1.jpg',
                'path' => '/wp-content/uploads/2024/06/file1.jpg'
              ],
              [
                'id' => 124,
                'url' => 'https://example.com/wp-content/uploads/2024/06/file2.jpg',
                'path' => '/wp-content/uploads/2024/06/file2.jpg'
              ]
            ]
        ];
    }

    public function setStatus(string $status) {
        $this->status = $status;
    }

    public function setMessage(string $message) {
        $this->message = $message;
    }
    
    public function add(int $id, string $url, string $path) {
        if($this->responseData === null) {
            $this->responseData = [];
        }
        $this->responseData[] = [
            'id' => $id,
            'url' => $url,
            'path' => $path
        ];
    }
}