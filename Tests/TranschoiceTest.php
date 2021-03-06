<?php
namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class TranschoiceTest extends \PHPUnit_Framework_TestCase
{

    const LANGUAGE_DIR = './app/Resources/translations/';

    /**
     * @test
     * @dataProvider language_provider
     */
    public function all_transchoice_entries_should_have_a_correct_syntax($translator, $language_code, $message_ids)
    {
        foreach ($message_ids as $message_id) {
            $translator->transChoice($message_id, 1, array(), 'catroweb', $language_code);
            $translator->transChoice($message_id, 2, array(), 'catroweb', $language_code);
            $translator->transChoice($message_id, 10, array(), 'catroweb', $language_code);
        }
    }

    /**
     * @test
     */
    public function all_languages_must_have_a_two_letter_fallback()
    {
        $codes = $this->getLanguageCodesFromFilenames();
        $four_letter_codes = array_filter($codes, function ($val) {
            return strlen($val) > 2;
        });
        foreach ($four_letter_codes as $four_letter_code) {
            $two_letter_code = explode("_", $four_letter_code)[0];
            $this->assertContains($two_letter_code, $codes, "No fallback for language code " . $four_letter_code . " found!");
        }
    }

    public function language_provider()
    {
        $directory = self::LANGUAGE_DIR;
        
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('yaml', new YamlFileLoader());
        
        $finder = new Finder();
        $files = $finder->in($directory)->files();
        $language_codes = array();
        foreach ($files as $file) {
            $parts = explode(".", $file->getFilename());
            $language_codes[] = $parts[1];
            $translator->addResource('yaml', $file, $parts[1], 'catroweb');
        }
        
        $translator->setFallbackLocales(array(
            'en'
        ));
        
        $messages = $translator->getMessages('en');
        $message_ids = array_keys($messages['catroweb']);
        
        $data = array();
        foreach ($language_codes as $language_code) {
            $data[] = array(
                $translator,
                $language_code,
                $message_ids
            );
        }
        return $data;
    }

    private function getLanguageCodesFromFilenames()
    {
        $finder = new Finder();
        $directory = self::LANGUAGE_DIR;
        $files = $finder->in($directory)->files();
        $language_codes = array();
        foreach ($files as $file) {
            $parts = explode(".", $file->getFilename());
            $language_codes[] = $parts[1];
        }
        return $language_codes;
    }
}
