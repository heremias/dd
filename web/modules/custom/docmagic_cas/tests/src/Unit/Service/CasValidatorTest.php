<?php

namespace Drupal\Tests\docmagic_cas\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\docmagic_cas\Service\CasHelper;
use Drupal\docmagic_cas\Service\CasValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

/**
 * CasValidator unit tests.
 *
 * @ingroup docmagic_cas
 * @group docmagic_cas
 *
 * @coversDefaultClass \Drupal\docmagic_cas\Service\CasValidator
 */
class CasValidatorTest extends UnitTestCase {

  /**
   * Test validation of Cas tickets.
   *
   * @dataProvider validateTicketDataProvider
   */
  public function testValidateTicket($version, $ticket, $username, $response, $is_proxy, $can_be_proxied, $proxy_chains, $ssl_verification, $role) {
    // Setup Guzzle to return a mock response.
    $mock = new MockHandler([new Response(200, array(), $response)]);
    $handler = HandlerStack::create($mock);
    $transactions = [];
    $history = Middleware::history($transactions);
    $handler->push($history);
    $httpClient = new Client(['handler' => $handler]);

    $configFactory = $this->getConfigFactoryStub(array(
      'cas.settings' => array(
        'server.hostname' => 'example.com',
        'server.port' => 443,
        'server.path' => '/cas',
        'server.version' => $version,
        'server.verify' => $ssl_verification,
        'server.cert' => 'foo',
        'proxy.initialize' => $is_proxy,
        'proxy.can_be_proxied' => $can_be_proxied,
        'proxy.proxy_chains' => $proxy_chains,
      ),
    ));

    $loggerFactory = $this->getMock('\Drupal\Core\Logger\LoggerChannelFactoryInterface');

    $casHelper = new CasHelper($configFactory, $loggerFactory);

    $urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $casValidator = new CasValidator($httpClient, $casHelper, $configFactory, $urlGenerator);

    $property_bag = $casValidator->validateTicket($ticket);

    $this->assertEquals($username, $property_bag->getUsername());

    // Test that we sent the correct ssl option to the http client.
    foreach ($transactions as $transaction) {
      switch ($ssl_verification) {
        case CasHelper::CA_CUSTOM:
          $this->assertEquals('foo', $transaction['options']['verify']);
          break;

        case CasHelper::CA_NONE:
          $this->assertEquals(FALSE, $transaction['options']['verify']);
          break;

        default:
          $this->assertEquals(TRUE, $transaction['options']['verify']);
      }
    }

    $this->assertEquals($role, $property_bag->getAttributes()[CasValidator::PERMISSIONS_FROM_GRANTED_AUTHORITY][0]);
  }

  /**
   * Provides parameters and return values for testValidateTicket.
   *
   * @return array
   *   Parameters and return values.
   *
   * @see \Drupal\Tests\docmagic_cas\Unit\Service\CasValidatorTest::testValidateTicket
   */
  public function validateTicketDataProvider() {
    $user1 = $this->randomMachineName(8);
    $account1 = '12';
    $role1 = 'ROLE_COMPLIANCE';
    $response1 = "<cas:serviceResponse xmlns:cas='http://example.com/cas'>
        <cas:authenticationSuccess>
          <cas:user>$user1</cas:user>
          <cas:AccountId>$account1</cas:AccountId>
          <cas:GrantedAuthority active='true' locked='false'>
            <cas:Role>$role1</cas:Role>
          </cas:GrantedAuthority>
        </cas:authenticationSuccess>
      </cas:serviceResponse>";
    $params[] = array(
      '2.0',
      $this->randomMachineName(24),
      $user1,
      $response1,
      FALSE,
      FALSE,
      '',
      CasHelper::CA_NONE,
      $role1,
    );

    $user2 = $this->randomMachineName(8);
    $account2 = '34';
    $role2 = 'ROLE_TRIAL';
    $response2 = "<cas:serviceResponse xmlns:cas='http://example.com/cas'>
        <cas:authenticationSuccess>
          <cas:user>$user2</cas:user>
          <cas:AccountId>$account2</cas:AccountId>
          <cas:GrantedAuthority active='true' locked='false' trial='true'>
            <cas:Role>ROLE_COMPLIANCE</cas:Role>
          </cas:GrantedAuthority>
        </cas:authenticationSuccess>
      </cas:serviceResponse>";
    $params[] = array(
      '2.0',
      $this->randomMachineName(24),
      $user2,
      $response2,
      FALSE,
      FALSE,
      '',
      CasHelper::CA_NONE,
      $role2,
    );

    $user3 = $this->randomMachineName(8);
    $account3 = '56';
    $role3 = 'ROLE_COMPLIANCE_BASIC';
    $response3 = "<cas:serviceResponse xmlns:cas='http://example.com/cas'>
        <cas:authenticationSuccess>
          <cas:user>$user3</cas:user>
          <cas:AccountId>$account3</cas:AccountId>
          <cas:GrantedAuthority active='true'>
            <cas:Role>$role3</cas:Role>
          </cas:GrantedAuthority>
        </cas:authenticationSuccess>
      </cas:serviceResponse>";
    $params[] = array(
      '2.0',
      $this->randomMachineName(24),
      $user3,
      $response3,
      FALSE,
      FALSE,
      '',
      CasHelper::CA_NONE,
      $role3,
    );

    $user4 = $this->randomMachineName(8);
    $account4 = '78';
    $role4 = 'ROLE_EMPLOYEE';
    $response4 = "<cas:serviceResponse xmlns:cas='http://example.com/cas'>
        <cas:authenticationSuccess>
          <cas:user>$user4</cas:user>
          <cas:AccountId>$account4</cas:AccountId>
          <cas:GrantedAuthority active='true'>
            <cas:Role>$role4</cas:Role>
          </cas:GrantedAuthority>
        </cas:authenticationSuccess>
      </cas:serviceResponse>";
    $params[] = array(
      '2.0',
      $this->randomMachineName(24),
      $user4,
      $response4,
      FALSE,
      FALSE,
      '',
      CasHelper::CA_NONE,
      $role4,
    );

    return $params;
  }
}
