<?php 

namespace ModularityFrontendForm\Api;

enum RestApiResponseStatusEnums: string {
  case HandlerError     = 'handler_error';
  case FileError        = 'file_error';
  case ValidationError  = 'validation_error';
  case GenericError     = 'error';
  case Success          = 'success';
}
