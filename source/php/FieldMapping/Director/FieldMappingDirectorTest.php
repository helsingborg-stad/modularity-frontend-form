<?php

namespace ModularityFrontendForm\FieldMapping\Director;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\PhoneFieldMapper;
use PHPUnit\Framework\TestCase;
use WpService\WpService;

class FieldMappingDirectorTest extends TestCase
{
    public function testResolvesPhoneNumberField(): void
    {
        $wp = $this->createMock(WpService::class);
        $director = new FieldMappingDirector($wp, (object) [], new Config($wp, 'test'));
        self::assertInstanceOf(PhoneFieldMapper::class, $director->resolveMapper(['type' => 'phone_number']));
    }
}
