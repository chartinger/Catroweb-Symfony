<?php
namespace Features\Api\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Catrobat\AppBundle\Entity\ProgramDownloadsRepository;
use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\StatisticsService;
use Catrobat\AppBundle\Services\TestEnv\LdapTestDriver;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Features\Api\Context\FixedTokenGenerator;
use Catrobat\AppBundle\Features\Api\Context\FixedTime;

//
// Require 3rd-party libraries here:
//
// require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{
    private $method;
    private $url;
    private $post_parameters = array();
    private $get_parameters = array();
    private $server_parameters = array('HTTP_HOST' => 'pocketcode.org', 'HTTPS' => true);
    private $files = array();
    
    public function __construct()
    {
        parent::__construct();
    }
    
    // ----------------------------------------------------------------
    
    /**
     * @When /^such a Request is invoked$/
     * @When /^a Request is invoked$/
     * @When /^the Request is invoked$/
     * @When /^I invoke the Request$/
     */
    public function iInvokeTheRequest()
    {
        $request = 'https://' . $this->server_parameters['HTTP_HOST'] . $this->url . '?' . http_build_query($this->get_parameters);
        $this->getClient()->request($this->method, 'https://' . $this->server_parameters['HTTP_HOST'] . $this->url . '?' . http_build_query($this->get_parameters), $this->post_parameters, $this->files, $this->server_parameters);
    }

    /**
     * @When /^I POST the following parameters to "([^"]*)":$/
     */
    public function iPostTheFollowingParametersTo($arg1, TableNode $table)
    {
        $this->method = 'POST';
        $this->url = $arg1;
        $values = $table->getRowsHash();
        $this->post_parameters = $values;
        $this->iInvokeTheRequest();
    }

    /**
     * @When /^I GET "([^"]*)" with parameters:$/
     */
    public function iGetWithParameters($arg1, TableNode $table)
    {
        $this->method = 'GET';
        $this->url = $arg1;
        $values = $table->getRowsHash();
        array_shift($values);
        $this->get_parameters = $values;
        $this->iInvokeTheRequest();
    }

    /**
     * @When /^I upload the file to "([^"]*)" with POST parameters:$/
     */
    public function iUploadTheFileToWithPostParameters($arg1, TableNode $table)
    {
        $this->method = "POST";
        $this->url = $arg1;
        $values = $table->getRowsHash();
        $this->post_parameters = $values;
        $this->iInvokeTheRequest();
    }

    /**
     * @Then /^the returned json object will be:$/
     * @Then /^I will get the json object:$/
     */
    public function iWillGetTheJsonObject(PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
    }

    /**
     * @Then /^the response code will be "([^"]*)"$/
     */
    public function theResponseCodeWillBe($code)
    {
        $response = $this->getClient()->getResponse();
        assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
    }
    
    /**
     * @Given /^the server name is "([^"]*)"$/
     */
    public function theServerNameIs($arg1)
    {
        $this->server_parameters = array('HTTP_HOST' => 'pocketcode.org', 'HTTPS' => true, 'SERVER_NAME' => 'asdsd.org');
    }
    
    /**
     * @Then /^the "([^"]*)" in the error JSON will be (\d+)$/
     */
    public function theStatuscodeInTheErrorJsonWillBe($param, $arg1)
    {
        $response = $this->getClient()->getResponse()->getContent();
        $response = json_decode($response, true);
        assertEquals($arg1, $response[$param]);
    }

    // ----------------------------------------------------------------
    
    /**
     * @Given /^there are users:$/
     */
    public function thereAreUsers(TableNode $table)
    {
        $users = $table->getHash();
    
        for ($i = 0; $i < count($users); ++ $i)
        {
            $this->insertUser(array('name' => $users[$i]['name'], 'token' => $users[$i]['token'], 'password' => $users[$i]['password']));
        }
    }
    
    /**
     * @Given /^there are programs:$/
     */
    public function thereArePrograms(TableNode $table)
    {
        $programs = $table->getHash();
        $program_manager = $this->getProgramManger();
        for ($i = 0; $i < count($programs); ++ $i) {
            $user = $this->getUserManager()->findOneBy(array(
                'username' => isset($programs[$i]['owned by']) ? $programs[$i]['owned by'] : ""
            ));
            if ($user == null) {
                if (isset($programs[$i]['owned by'])) {
                    $user = $this->insertUser(array('name' => $programs[$i]['owned by']));
                }
            }
            @$config = array(
                'name' => $programs[$i]['name'],
                'description' => $programs[$i]['description'],
                'views' => $programs[$i]['views'],
                'downloads' => $programs[$i]['downloads'],
                'uploadtime' => $programs[$i]['upload time'],
                'apk_status' => $programs[$i]['apk_status'],
                'catrobatversionname' => $programs[$i]['version'],
                'directory_hash' => $programs[$i]['directory_hash'],
                'filesize' => @$programs[$i]['FileSize'],
                'visible' => isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true,
                'remix_root' => isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true
            );

            $this->insertProgram($user, $config);
        }
    }
    
    /**
     * @Given /^there are tags:$/
     */
    public function thereAreTags(TableNode $table)
    {
        $tags = $table->getHash();

        foreach($tags as $tag)
        {
            @$config = array(
                'id' => $tag['id'],
                'en' => $tag['en'],
                'de' => $tag['de']
            );
            $this->insertTag($config);
        }
    }

    /**
     * @Given /^following programs are featured:$/
     */
    public function followingProgramsAreFeatured(TableNode $table)
    {
        $em = $this->getManager();
        $featured = $table->getHash();
        for ($i = 0; $i < count($featured); ++ $i) {
            $program = $this->getProgramManger()->findOneByName($featured[$i]['name']);
            $featured_entry = new FeaturedProgram();
            $featured_entry->setProgram($program);
            $featured_entry->setActive(isset($featured[$i]['active']) ?  $featured[$i]['active'] == 'yes' : true);
            $featured_entry->setImageType('jpg');
            $em->persist($featured_entry);
        }
        $em->flush();
    }
    
    /**
     * @Given /^the current time is "([^"]*)"$/
     */
    public function theCurrentTimeIs($time)
    {
        $date = new \DateTime($time, new \DateTimeZone('UTC'));
        $time_service = $this->getSymfonyService('time');
        $time_service->setTime(new FixedTime($date->getTimestamp()));
    }
    
    /**
     * @Given /^we assume the next generated token will be "([^"]*)"$/
     * @Given /^the next generated token will be "([^"]*)"$/
     */
    public function weAssumeTheNextGeneratedTokenWillBe($token)
    {
        $token_generator = $this->getSymfonyService('tokengenerator');
        $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
    }
    
    // ----------------------------------------------------------------

    /**
     * @Given /^a valid catrobat program with the MD5 checksum "([^"]*)"$/
     */
    public function aValidCatrobatProgramWithTheMdChecksumOf($arg1)
    {
        $filepath = self::FIXTUREDIR . 'test.catrobat';
        assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, 'test.catrobat');
        assertEquals(md5_file($filepath), $arg1);
    }

    /**
     * @When /^there is a "([^"]*)" with the registration request$/
     */
    public function thereIsAWithTheRegistrationRequest($problem)
    {
        switch ($problem)
        {
            case "no password given":
                $this->method = "POST";
                $this->url = "/pocketcode/api/loginOrRegister/loginOrRegister.json";
                $this->post_parameters['registrationUsername'] = "Someone";
                $this->post_parameters['registrationEmail'] = "someone@pocketcode.org";
                break;
            default:
                throw new PendingException("No implementation of case \"" . $problem . "\"");
        }
        $this->iInvokeTheRequest();
    }

    /**
     * @When /^there is a "([^"]*)" with the check token request$/
     */
    public function thereIsAWithTheCheckTokenRequest($problem)
    {
        switch ($problem)
        {
            case "invalid token":
                $this->method = "POST";
                $this->url = "/pocketcode/api/checkToken/check.json";
                $this->post_parameters['username'] = "Catrobat";
                $this->post_parameters['token'] = "INVALID";
                break;
            default:
                throw new PendingException("No implementation of case \"" . $problem . "\"");
        }
        $this->iInvokeTheRequest();
    }
    
    /**
     * @When /^searching for "([^"]*)"$/
     */
    public function searchingFor($arg1)
    {
        $this->method = 'GET';
        $this->url = '/pocketcode/api/projects/search.json';
        $this->get_parameters = array('q' => $arg1, 'offset' => 0, 'limit' => 10);
        $this->iInvokeTheRequest();
    }
    

    /**
     * @Given /^the upload problem "([^"]*)"$/
     */
    public function theUploadProblem($problem)
    {
        switch ($problem)
        {
            case "no authentication":
                $this->method = "POST";
                $this->url = "/pocketcode/api/upload/upload.json";
                break;
            case "missing parameters":
                $this->method = "POST";
                $this->url = "/pocketcode/api/upload/upload.json";
                $this->post_parameters['username'] = "Catrobat";
                $this->post_parameters['token'] = "cccccccccc";
                break;
            case "invalid program file":
                $this->method = "POST";
                $this->url = "/pocketcode/api/upload/upload.json";
                $this->post_parameters['username'] = "Catrobat";
                $this->post_parameters['token'] = "cccccccccc";
                $filepath = self::FIXTUREDIR . 'invalid_archive.catrobat';
                assertTrue(file_exists($filepath), 'File not found');
                $this->files[] = new UploadedFile($filepath, 'test.catrobat');
                $this->post_parameters['fileChecksum'] = md5_file($this->files[0]->getPathname());
                break;
            default:
                throw new PendingException("No implementation of case \"" . $problem . "\"");
        }
    }

    /**
     * @When /^the tags are requested without a language parameter$/
     */
    public function theTagsAreRequestedWithoutALanguageParameter()
    {
        $this->method = 'GET';
        $this->url = '/pocketcode/api/tags/getTags.json';
        $this->get_parameters = array();
        $this->iInvokeTheRequest();
    }

    /**
     * @Then /^the "([^"]*)" array will show the english tags\.$/
     */
    public function theConstanttagsArrayWillShowTheEnglishTags($arg1)
    {
        $response = $this->getClient()->getResponse()->getContent();
        $response = json_decode($response, true);
        assertEquals(array("Games","Story","Music","Art"), $response[$arg1]);
    }

    /**
     * @When /^there is a "([^"]*)" with the tag request$/
     */
    public function thereIsAWithTheTagRequest($arg1)
    {
        switch ($arg1) {
            case "no language given":
                $this->get_parameters = array();
                break;
            case "unsupported language":
                $this->get_parameters = array('language' => 'xx');
                break;
            default: 
                throw new PendingException("No implementation of case \"" . $arg1 . "\"");
        }
        $this->method = 'GET';
        $this->url = '/pocketcode/api/tags/getTags.json';
        $this->iInvokeTheRequest();
    }

}
