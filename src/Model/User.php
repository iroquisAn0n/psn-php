<?php

namespace Tustin\PlayStation\Model;

use Tustin\PlayStation\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Model\TrophySummary;
use Tustin\PlayStation\Factory\UsersFactory;
use Tustin\PlayStation\Interfaces\Fetchable;
use Tustin\PlayStation\Factory\TrophyTitlesFactory;

class User extends Api implements Fetchable
{
    use Model;
    
    private $profile;

    private $accountId;

    /**
     * Constructs a new user object.
     *
     * @param UsersFactory $usersFactory
     * @param string $accountId
     */
    public function __construct(UsersFactory $usersFactory, string $accountId) 
    {
        parent::__construct($usersFactory->getHttpClient());
        $this->setFactory($usersFactory);

        $this->accountId = $accountId;
    }

    public function trophyTitles()
    {
        return new TrophyTitlesFactory($this);
    }

    /**
     * Gets the trophy summary for the user.
     *
     * @return void
     */
    public function trophySummary()
    {
        return new TrophySummary($this);
    }

    /**
     * Gets online ID.
     *
     * @return string
     */
    public function onlineId() : string
    {
        return $this->pluck('onlineId');
    }
    
    /**
     * Gets the about me.
     *
     * @return string
     */
    public function aboutMe() : string
    {
        return $this->pluck('aboutMe');
    }

    /**
     * Gets the user's account ID.
     *
     * @return string
     */
    public function accountId() : string
    {
        return $this->accountId;
    }

    /**
     * Returns all the available avatar URL sizes.
     * 
     * Each array key is the size of the image.
     *
     * @return array
     */
    public function avatarUrls() : array
    {
        $urls = [];

        foreach ($this->pluck('avatarUrls') as $url)
        {
            $urls[$url['size']] = $url['avatarUrl'];
        }

        return $urls;
    }

    /**
     * Gets the avatar URL.
     * 
     * This should return the largest size available.
     *
     * @return string
     */
    public function avatarUrl() : string
    {
        $firstKey = array_key_first($this->avatarUrls());
        return $this->avatarUrls()[$firstKey];
    }

    /**
     * Check if client is blocking the user.
     *
     * @return boolean
     */
    public function isBlocking() : bool
    {
        return $this->pluck('blocking');
    }

    /**
     * Get the user's follower count.
     *
     * @return integer
     */
    public function followerCount() : int
    {
        return $this->pluck('followerCount');
    }

    /**
     * Check if the client is following the user.
     *
     * @return boolean
     */
    public function isFollowing() : bool
    {
        return $this->pluck('following');
    }

    /**
     * Check if the user is verified.
     *
     * @return boolean
     */
    public function isVerified() : bool
    {
        return $this->pluck('isOfficiallyVerified');
    }

    /**
     * Gets all the user's languages.
     *
     * @return array
     */
    public function languages() : array
    {
        return $this->pluck('languagesUsed');
    }

    /**
     * Gets mutual friend count.
     * 
     * Returns -1 if current profile is the logged in user.
     *
     * @return integer
     */
    public function mutualFriendCount() : int
    {
        return $this->pluck('mutualFriendsCount');
    }

    /**
     * Checks if the client has any mutual friends with the user. 
     *
     * @return boolean
     */
    public function hasMutualFriends() : bool
    {
        return $this->mutualFriendCount() > 0;
    }

    /**
     * Checks if the client is close friends with the user.
     *
     * @return boolean
     */
    public function isCloseFriend() : bool
    {
        return $this->pluck('personalDetailSharing') !== 'no';
    }

    /**
     * Checks if the client has a pending friend request with the user.
     * 
     * @TODO: Check if this works both ways.
     *
     * @return boolean
     */
    public function hasFriendRequested() : bool
    {
        return $this->pluck('friendRelation') === 'requesting';
    }

    /**
     * Checks if the user is currently online.
     *
     * @return boolean
     */
    public function isOnline() : bool
    {
        return $this->pluck('presences.0.onlineStatus') === 'online';
    }

    /**
     * Checks if the user has PlayStation Plus.
     *
     * @return boolean
     */
    public function hasPlus() : bool
    {
        return $this->pluck('plus');
    }

    /**
     * Fetches the user's profile information.
     *
     * @return object
     */
    public function fetch() : object
    {
        return $this->get('userProfile/v1/internal/users/' . $this->accountId . '/profiles');
    }
}