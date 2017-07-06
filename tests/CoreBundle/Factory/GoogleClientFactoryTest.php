<?php


namespace Tests\CoreBundle\Factory;


use PHPUnit\Framework\Assert;
use Tests\BaseTestCase;

class GoogleClientFactoryTest extends BaseTestCase
{
    public function testFactory()
    {
        $factory = $this->getContainer()->get('startsymfony.core.google_client_factory');

        Assert::assertInstanceOf(\Google_Client::class, $factory->getGoogleClient());
    }
}