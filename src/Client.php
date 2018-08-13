<?php

namespace PlayStation;

use PlayStation\Api\MessageThread;
use PlayStation\Api\User;

use PlayStation\Http\HttpClient;
use PlayStation\Http\ResponseParser;
use PlayStation\Http\TokenMiddleware;

use GuzzleHttp\Middleware;

class Client {

    const OAUTH_BASE    = 'https://auth.api.sonyentertainmentnetwork.com/2.0/oauth/token';

    private const CLIENT_ID     = 'ebee17ac-99fd-487c-9b1e-18ef50c39ab5';
    private const CLIENT_SECRET = 'e4Ru_s*LrL4_B2BD';
    private const DUID          = '0000000d00040080027BC1C3FBB84112BFC9A4300A78E96A';
    private const SCOPE         = 'kamaji:get_players_met kamaji:get_account_hash kamaji:activity_feed_submit_feed_story kamaji:activity_feed_internal_feed_submit_story kamaji:activity_feed_get_news_feed kamaji:communities kamaji:game_list kamaji:ugc:distributor oauth:manage_device_usercodes psn:sceapp user:account.profile.get user:account.attributes.validate user:account.settings.privacy.get kamaji:activity_feed_set_feed_privacy kamaji:satchel kamaji:satchel_delete user:account.profile.update';

    private $httpClient;
    private $onlineId;
    private $messageThreads;

    public function __construct(HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new HttpClient();
    }
    
    /**
     * Login to PlayStation network using a refresh token or 2FA.
     *
     * @param string $ticketUuidOrRefreshToken Ticket UUID for 2FA, or the refresh token.
     * @param string $code 2FA code sent to your device (ignore if using refresh token).
     * @return void
     */
    public function login(string $ticketUuidOrRefreshToken, string $code = null) 
    {
        if ($code === null) {
            $response = $this->getHttpClient()->post(self::OAUTH_BASE, [
                "app_context" => "inapp_ios",
                "client_id" => self::CLIENT_ID,
                "client_secret" => self::CLIENT_SECRET,
                "refresh_token" => $ticketUuidOrRefreshToken,
                "duid" => self::DUID,
                "grant_type" => "refresh_token",
                "scope" => self::SCOPE
            ]);

            $handler = \GuzzleHttp\HandlerStack::create();
            $handler->push(Middleware::mapRequest(new TokenMiddleware($response->access_token, $response->refresh_token, $response->expires_in)));
    
            $this->httpClient = new HttpClient(new \GuzzleHttp\Client(['handler' => $handler, 'verify' => false, 'proxy' => '127.0.0.1:8888']));
        }
    }


    /**
     * Gets the HttpClient.
     *
     * @return HttpClient
     */
    public function getHttpClient() : HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Gets the logged in user's onlineId.
     *
     * @return string
     */
    public function getOnlineId() : string
    {
        if ($this->onlineId === null) {
            $response = $this->getHttpClient()->get(sprintf(User::USERS_ENDPOINT . 'profile2', 'me'), [
                'fields' => 'onlineId'
            ]);

            $this->onlineId = $response->profile->onlineId;
        }
        return $this->onlineId;
    }

    /**
     * Gets all the logged in user's message threads
     *
     * @param integer $offset Where to start.
     * @param integer $limit Amount of threads.
     * @return object
     */
    public function getMessageThreads(int $offset = 0, int $limit = 20) : object
    {
        if ($this->messageThreads === null) {
            $response = $this->getHttpClient()->get(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/', [
                'fields' => 'threadMembers',
                'limit' => $limit,
                'offset' => $offset,
                'sinceReceivedDate' => '1970-01-01T00:00:00Z' // Don't hardcode
            ]);

            $this->messageThreads = $response;
        }
        return $this->messageThreads;
    }

    /**
     * Creates a new User object.
     *
     * @param string $onlineId The user's onlineId (null to get current user's account).
     * @return void
     */
    public function user(string $onlineId = null) 
    {
        return new Api\User($this, $onlineId);
    }
}