<?php 

namespace ModularityFrontendForm\Handlers;

class HandlerFactory {
  private array $availableHandlers = [
      'email' => SendEmailHandler::class,
      'db' => StoreToDbHandler::class,
  ];

  public function createHandlers(array $params): array {
      $handlers = [];

      foreach ($this->availableHandlers as $key => $handlerClass) {
          if (!empty($params[$key]) && class_exists($handlerClass)) {
              $handlers[] = new $handlerClass();
          }
      }

      return $handlers;
  }
}