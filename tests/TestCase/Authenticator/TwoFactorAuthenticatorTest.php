<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierCollection;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

class TwoFactorAuthenticatorTest extends TestCase
{
    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateFailedNoData()
    {
        $request = $this->requestWithTestPath();
        $response = new Response();
        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);
        $Authenticator = new TwoFactorAuthenticator($identifiers, [
            'loginUrl' => '/testpath'
        ]);

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateFailedInvalidUrl()
    {
        $request = $this->requestWithTestPath();

        $request->getSession()->write(
            TwoFactorAuthenticator::USER_SESSION_KEY,
            new Entity([
                'id' => '42',
                'username' => 'marcelo',
                'role' => 'user'
            ])
        );
        $response = new Response();
        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);
        $Authenticator = new TwoFactorAuthenticator($identifiers, [
            'loginUrl' => '/testpathnotsame'
        ]);

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $request = $this->requestWithTestPath();
        $request->getSession()->write(
            TwoFactorAuthenticator::USER_SESSION_KEY,
            new Entity([
                'id' => '42',
                'username' => 'marcelo',
                'role' => 'user'
            ])
        );
        $response = new Response();
        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);
        $Authenticator = new TwoFactorAuthenticator($identifiers, [
            'loginUrl' => '/testpath'
        ]);

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * @return \Cake\Http\ServerRequest
     */
    protected function requestWithTestPath()
    {
        $request = new \Cake\Http\ServerRequest();
        $uri = new \Zend\Diactoros\Uri('/testpath');
        $uri->base = null;

        return $request->withUri($uri);
    }
}
