<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */
namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use Flagbit\Inxmail\Model\Request\RequestImports;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestImportsTest
 *
 * @package Flagbit\Inxmail\Test\Unit\Model\Request
 */
class RequestImportsTest extends TestCase
{
    /**
     * @var \Flagbit\Inxmail\Model\Request\RequestImports
     */
    private $requestClient;
    protected static $testListId = 7;

    private $testCsvFile = [
        'email;Vorname;Nachname;magentoSubscriberId,magentoSubscriberToken,magentoWebsiteName,magentoWebsiteId,magentoStoreName,magentoStoreViewName,Geburtsdatum,Geschlecht',
        '"dummy@example.com";"dummy";"doo";"4";"a36emacqpz96qe8hdyyl8g5qaqv8yyaa";"demo";"1";"Main Website","1";"Default Store View;"1990-02-04","Male"',
        '"dommy@example.com";"dommy";"duu";"5";"a36emacqpz96qe8hdyyl8g5qaqv8yyaa";"demo";"1";"Main Website","1";"Default Store View;"1995-01-15","Male"'
    ];

    private $testCsvFile2 = [
        ['email', 'Vorname', 'Nachname', 'magentoSubscriberId', 'magentoSubscriberToken', 'magentoWebsiteName', 'magentoWebsiteId', 'magentoStoreViewName', 'Geburtsdatum', 'Geschlecht'],
        ['dummy@example.com', 'dummy','doo','4', 'a36emacqpz96qe8hdyyl8g5qaqv8yyaa','demo', '1','Main Website', '1990-02-04','Male'],
        ['dommy@example.com', 'dommy','duu', '5','a36emacqpz\96qe8hdyyl8g5qaqv8yyaa','demo', '1','Main Website', '1995-01-15','Male'],
    ];

    public function setUp()
    {
        if (!$this->requestClient) {
            $params = $_SERVER;
            $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
            /** @var \Magento\Framework\App\Http $app */
            $app = $bootstrap->createApplication('Magento\Framework\App\Http');
            unset($app);

            $this->om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->requestClient = (new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om))->create(
                RequestImports::class
            );
        }
    }

    public function testImportCsv()
    {
        $csv_data = '';
        foreach ($this->testCsvFile2 as $value){
            array_walk($value, function(&$item, $key){
                $item = '"'.$item.'"';
            });
            $csv_data .= implode(';', $value).PHP_EOL;
        }

        // form field separator
        $delimiter = '----' . 'Inxmail';
        $data = '';

        $fileFields = [
            'file' => [
                'type' => 'text/csv',
                'content' => $csv_data,
            ],
        ];

        foreach ($fileFields as $name => $file) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . $name . '";' .
                ' filename="' . $name . '"' . "\r\n";
            $data .= 'Content-Type: ' . $file['type'] . "\r\n";
            $data .= "\r\n";
            $data .= $file['content'] . "\r\n";
            $data .= "--" . $delimiter . "--";
        }


        $this->requestClient->setRequestParam('?listId='.self::$testListId.'');
        $this->requestClient->setRequestFile($this->testCsvFile2);
        $response = $this->requestClient->writeRequest();
        $this->assertEquals(201,$response);
    }
}
