<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * GreenpeaceMessageTemplate.Send API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_GreenpeaceMessageTemplate_SendTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp(): void {
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Test invalid email.
   */
  public function testInvalidEmail() {
    $result = civicrm_api3('GreenpeaceMessageTemplate', 'send', [
      'id' => 1,
      'to_email' => 'abc123'
    ]);
    $this->assertEquals('abc123', $result['values']['invalid'][0]);
  }

  /**
   * Test valid email.
   */
  public function testValidEmail() {
    $result = civicrm_api3('GreenpeaceMessageTemplate', 'send', [
      'id' => 1,
      'to_email' => 'abc123@test.com'
    ]);
    $this->assertEquals('abc123@test.com', $result['values']['valid'][0]);
  }

  /**
   * Test valid emails (comma separated list).
   */
  public function testValidEmails() {
    $emails = [
      'abc123@test.com',
      'xyz123@test.com',
      'test@gmail.com',
      ' space@example.com',
    ];
    $result = civicrm_api3('GreenpeaceMessageTemplate', 'send', [
      'id' => 1,
      'to_email' => implode(',', $emails)
    ]);
    $this->assertEquals($emails[0], $result['values']['valid'][0]);
    $this->assertEquals($emails[1], $result['values']['valid'][1]);
    $this->assertEquals($emails[2], $result['values']['valid'][2]);
    $this->assertEquals(trim($emails[3]), $result['values']['valid'][3]);
  }
}
