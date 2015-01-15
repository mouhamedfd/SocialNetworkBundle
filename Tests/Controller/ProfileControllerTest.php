<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Tests\Controller;

use Fulgurio\SocialNetworkBundle\Tests\Controller\WebTestCase;

/**
 * Profil controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class ProfileControllerTest extends WebTestCase
{
    /**
     * User data
     * @var array
     */
    private $userData = array(
        'username' => 'user1',
        'email' => 'user1@example.com',
        'password' => 'user1'
    );

    /**
     * Show profil page test
     */
    public function testShowAction()
    {
        $client = $this->getLoggedClient();
        $crawler = $client->request('GET', '/profile/');
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $this->userData['username'], $crawler->filter('section p:first-child')->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $this->userData['email'], $crawler->filter('section p:nth-child(2)')->text());
    }

    /**
     * Edit profil page test
     */
    public function testEditWithEmptyFormAction()
    {
        $client = $this->getLoggedClient();
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[user][username]' => '',
            'fos_user_profile_form[user][email]' => '',
            'fos_user_profile_form[current]' => ''
        );
        $form = $crawler->filter('form[action$="profile/edit"]button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.current_password.invalid")'));
    }

    /**
     * Edit profil page test
     */
    public function testEditWithExistingUserAction()
    {
        $client = $this->getLoggedClient();
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[user][username]' => 'user2',
            'fos_user_profile_form[user][email]' => 'user2@example.com',
            'fos_user_profile_form[current]' => $this->userData['password']
        );
        $form = $crawler->filter('form[action$="profile/edit"]button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.already_used")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.already_used")'));
    }


    /**
     * Edit profil page test
     */
    public function testEditAction()
    {
        $client = $this->getLoggedClient();
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[user][username]' => 'user10',
            'fos_user_profile_form[user][email]' => 'user10@example.com',
            'fos_user_profile_form[current]' => $this->userData['password']
        );
        $form = $crawler->filter('form[action$="profile/edit"]button[name="_submit"]')->form();

        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $data['fos_user_profile_form[user][username]'], $crawler->filter('section p:first-child')->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $data['fos_user_profile_form[user][email]'], $crawler->filter('section p:nth-child(2)')->text());
    }

    /**
     * Get a logged client
     *
     * @return Symfony\Bundle\FrameworkBundle\Client
     */
    private function getLoggedClient()
    {
        $data = array(
            '_username' => $this->userData['username'],
            '_password' => $this->userData['password']
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        return $client;
    }
}