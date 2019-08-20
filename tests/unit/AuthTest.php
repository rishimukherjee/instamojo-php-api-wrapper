<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{


    public function test_application_based_authentication()
    {
        $instaobj = Instamojo\Instamojo::init('app',[
            "client_id" => $_ENV["CLIENT_ID"],
            "client_secret" => $_ENV["CLIENT_SECRET"]
        ],true);

        $this->assertInstanceOf(Instamojo\Instamojo::class,$instaobj);

    }

    public function test_throw_Exception_on_worng_client_id() {

        $this->expectException(\Instamojo\Exceptions\AuthenticationException::class);

        $instaobj = Instamojo\Instamojo::init('app',[
            "client_id" => "ABC",
            "client_secret" => $_ENV["CLIENT_SECRET"]
        ],true);

    } 


    public function test_throw_Exception_on_worng_client_secret() {
        
        $this->expectException(\Instamojo\Exceptions\AuthenticationException::class);
        
        $instaobj = Instamojo\Instamojo::init('app',[
            "client_id" => $_ENV["CLIENT_ID"],
            "client_secret" => "ABC"
        ],true);

    }
}
?>