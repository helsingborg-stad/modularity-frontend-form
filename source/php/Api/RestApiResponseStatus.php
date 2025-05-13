<?php 

namespace ModularityFrontendForm\Api;

enum RestApiResponseStatus: string {
  case Success = 'success';
  case Error = 'error';
}