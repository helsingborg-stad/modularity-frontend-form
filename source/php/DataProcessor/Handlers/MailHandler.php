<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use WpService\WpService; 
use AcfService\AcfService;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use ModularityFrontendForm\DataProcessor\FileHandlers\NullFileHandler;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use WP_Error;
use WP_REST_Request;

class MailHandler implements HandlerInterface {

  use GetModuleConfigInstanceTrait;

  public function __construct(
      private WpService $wpService,
      private AcfService $acfService,
      private ConfigInterface $config,
      private ModuleConfigInterface $moduleConfigInstance,
      private object $params,
      private HandlerResultInterface $handlerResult = new HandlerResult(),
      private ?FileHandlerInterface $fileHandler = null
  ) {
    if($this->fileHandler === null) {
      $this->fileHandler = new NullFileHandler($this->config, $this->moduleConfigInstance, $this->wpService);
    }
  }

  public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
  {
    $config = $this->moduleConfigInstance->getMailHandlerConfig();

    $reducedReciversToArray = array_reduce($config->Recivers ?? [], function($carry, $item) {
      $carry[] = trim($item['Email']);
      return $carry;
    }, []);

    if(!$this->toEmailIsSet($reducedReciversToArray ?? []) || !$this->validateToEmail($reducedReciversToArray ?? [])) {
      return $this->handlerResult;
    }

    $this->sendEmail(
      $reducedReciversToArray ?? [],
      $this->createEmailSubject($data),
      $this->createEmailBody($data)
    );

    return $this->handlerResult;
  }

  /**
   * Send the email
   *
   * @param string $emailTo The email address to send to
   * @param string $subject The subject of the email
   * @param string $body The body of the email
   * @param array $headers The headers for the email
   * @param array $attachments The attachments for the email
   * @return bool True if the email was sent successfully, false otherwise
   */
  private function sendEmail(array $emailTo, string $subject, string $body, array $headers = [], array $attachments = []): bool
  {
    // Send the email
    if (!$this->wpService->wpMail($emailTo, $subject, $body, $headers, $attachments)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Failed to send email.', 'modularity-frontend-form')
        )
      );
      return false;
    }
    return true;
  }

  /**
   * Validate the email address
   *
   * @param string $emailTo The email address to validate
   * @return bool True if the email address is valid, false otherwise
   */
  private function validateToEmail($emailTo): bool
  {
    $result = true;

    if(!is_array($emailTo)) {
      $emailTo = [$emailTo];
    }

    foreach ($emailTo as $email) {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->handlerResult->setError(
          new WP_Error(
            RestApiResponseStatusEnums::HandlerError->value, 
            $this->wpService->__('Invalid email address in recivers list.', 'modularity-frontend-form')
          )
        );
        $result = false;
      }
    }

    return $result;
  }

  /**
   * Check if the email address is set
   *
   * @param array $emailTo The email address to check
   * @return bool True if the email address is set, false otherwise
   */
  private function toEmailIsSet(array $emailTo): bool
  {
    if(empty($emailTo)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('No email address set in recivers list.', 'modularity-frontend-form')
        )
      );
      return false;
    }
    return true;
  }

  /**
   * Create the email body
   *
   * @param array $data The data to include in the email body
   * @return string The email body
   */
  private function createEmailBody(array $data): string
  {
      $rows = [];
      foreach ($data as $acfKey => $value) {
          if (in_array($acfKey, $this->config->getUnprintableKeys())) {
              continue;
          }
          $rows[] = [
            'key'   => $this->getFieldLabel($acfKey), 
            'value' => $this->sanitizeFieldValue($value)
          ];
      }

      return $this->renderEmailBodyTable($rows);
  }

  /**
   * Render the email body table
   *
   * @param array $rows Array of ['key' => string, 'value' => mixed]
   * @return string HTML table as string
   */
  private function renderEmailBodyTable(array $rows): string
  {
      $html = '<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse;">';
      foreach ($rows as $row) {
          $html .= '<tr>';
          $html .= '<th align="left" style="background:#f5f5f5;">' . esc_html($row['key']) . '</th>';
          $html .= '<td>' . esc_html(is_array($row['value']) ? implode(', ', $row['value']) : $row['value']) . '</td>';
          $html .= '</tr>';
      }
      $html .= '</table>';

      return $html;
  }

  /**
   * Create the email subject
   *
   * @param array $data The data to include in the email subject
   * @return string The email subject
   */
  private function createEmailSubject(array $data): string
  {
    return sprintf(
      $this->wpService->__(
        'New submission: %s', 
        'modularity-frontend-form'
      ), 
      $this->moduleConfigInstance->getModuleTitle()
    ); 
  }

  /**
   * Sanitize the field value
   *
   * @param mixed $value The value to sanitize
   * @return string The sanitized value
   */
  public function sanitizeFieldValue($value): string
  {
    return $this->wpService->sanitizeTextField($value);
  }

  /**
   * Get the field label for a given field key
   *
   * @param string $fieldKey The field key to get the label for
   * @return string The field label
   */
  private function getFieldLabel(string $fieldKey): string
  {
    $field = acf_get_field($fieldKey); //TODO: Implement in acf service
    if($field) {
      return $field['label'];
    }
    return $fieldKey;
  }

}