<?php

namespace VanOns\LaravelAttachmentLibrary\Test\DataTransferObjects;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;
use VanOns\LaravelAttachmentLibrary\Exceptions\ClassDoesNotExistException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class FilenameTest extends TestCase
{
    use WithFaker;

    public static function fileNameProvider(): array
    {
        return [
            ['test.jpg', 'test', 'jpg'],
            ['test.php.jpg', 'test.php', 'jpg'],
            ["te\u{00A0}\u{1680}\u{180E}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{200B}\u{202F}\u{205F}\u{3000}\u{FEFF}st.jpg", 'te                   st', 'jpg'], // Whitespaces should replace into regular spaces (\u{0020})
            ["te\u{00AD}st.jpg", 'te-st', 'jpg'], // Soft-hyphen
        ];
    }

    /**
     * @dataProvider fileNameProvider
     */
    public function testAssertCorrectFilename(string $name, string $expectedName, string $expectedExtension)
    {
        $filename = new Filename($name);

        $this->assertEquals($expectedName, $filename->name);
        $this->assertEquals($expectedExtension, $filename->extension);
    }

    public function testAssertNonExistingClass()
    {
        $this->expectException(ClassDoesNotExistException::class);

        Config::set('attachment-library.file_namers', ['iets']);

        new Filename('asd');
    }

    public function testAssertIncompatibleClass()
    {
        $this->expectException(IncompatibleClassMappingException::class);

        Config::set('attachment-library.file_namers', [(new class
        {
        })::class => null]);

        new Filename('asd');
    }
}
