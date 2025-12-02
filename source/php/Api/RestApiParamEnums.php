<?php 

namespace ModularityFrontendForm\Api;

enum RestApiParamEnums: string {
  case PostId = 'post-id';
  case HoldingPostId = 'holding-post-id';
  case ModuleId = 'module-id';
  case Token = 'token';
  case Nonce = 'nonce';
}
