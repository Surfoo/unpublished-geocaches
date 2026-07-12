<?php

namespace App\Tests\Security;

use App\Security\User;
use Geocaching\Enum\MembershipType;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private function makeToken(): AccessToken
    {
        return new AccessToken([
            'access_token'  => 'tok123',
            'refresh_token' => 'ref456',
            'expires'       => time() + 3600,
        ]);
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new User();
        $user->setRoles([]);
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_BASIC']);
        $roles = $user->getRoles();
        $this->assertSame($roles, array_unique($roles));
    }

    public function testIsPremiumReturnsTrueForPremiumMember(): void
    {
        $user = new User();
        // API returns membership level as a string integer
        $user->setMembershipLevelId((string) MembershipType::PREMIUM->id());
        $this->assertTrue($user->isPremium());
    }

    public function testIsPremiumReturnsFalseForBasicMember(): void
    {
        $user = new User();
        $user->setMembershipLevelId((string) MembershipType::BASIC->id());
        $this->assertFalse($user->isPremium());
    }

    public function testSerializeRoundTripKeepsCredentials(): void
    {
        $user = new User();
        $user->setUserId(1);
        $user->setCredentials($this->makeToken());

        $unserialized = unserialize(serialize($user));

        $this->assertSame('tok123', $unserialized->getCredentials()->getToken());
    }

    public function testSetCredentialsStoresToken(): void
    {
        $user = new User();
        $user->setUserId(1);
        $token = $this->makeToken();
        $user->setCredentials($token);
        $this->assertSame('tok123', $user->getCredentials()->getToken());
        $this->assertSame('ref456', $user->getCredentials()->getRefreshToken());
    }
}
