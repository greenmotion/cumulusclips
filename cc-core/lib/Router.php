<?php

class Router
{
    /**
     * Perform a REGEX test of a route's path against a given URI
     * @param Route $route Route to test
     * @param string $uri URI to test route path against
     * @return array Returns array of preg_match matches when match is found
     * boolean false otherwise
     * @see http://php.net/manual/en/function.preg-match.php For $matches format
     */
    protected function _testPattern(Route $route, $uri)
    {
        $pattern = '#^' . trim($route->path,'/') . '/?$#i';
        if (preg_match($pattern, $uri, $matches) === 1) {
            return $matches;
        } else {
            return false;
        }
    }

    /**
     * Map REGEX pattern matches found in route's path to keys for $_GET superglobal
     * @param Route $route The matched route whose path matches are to be mapped
     * @param array $patternMatches REGEX pattern matches from given route against current URI
     * @return array Returns list of key/value pairs to be merged into $_GET superglobal
     * @see http://php.net/manual/en/function.preg-match.php For $patternMatches format
     */
    protected function _mapPatternMatches(Route $route, $patternMatches)
    {
        $mappedData = array();
        foreach($route->mappings as $key => $variableName) {
            if (!is_numeric($key)) {
                $mappedData[$key] = $variableName;
            } else if (isset($patternMatches[$key+1])) {
                $mappedData[$variableName] = $patternMatches[$key+1];
            } else {
                $mappedData[$variableName] = null;
            }
        }
        return $mappedData;
    }
    
    /**
     * Return the Apache request uri with the site base URL and query string removed
     * @return string Returns the intended request uri
     */
    public function getRequestUri()
    {
        // Strip query string from Apache request uri
        $apacheRequestUri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);

        // Strip path part of base URL and query string from Apache request uri
        $urlParts = parse_url(HOST);
        if (!empty($urlParts['path'])) {
            return preg_replace('#^' . $urlParts['path'] . '#', '', $apacheRequestUri);
        } else {
            return $apacheRequestUri;
        }
    }
    
    /**
     * Detect which route matches current request URI, and merge it's pattern
     * matches into $_GET superglobal. URI is compared to ALL routes, and the
     * last matching route is used.
     * @return Route 
     */
    public function getRoute()
    {
        $matchedRoute = new stdClass();
        $routes = $this->_staticRoutes();
        $requestUri = $this->getRequestUri();

        // Go through all routes to test if it matches current URI
        foreach ($routes as $route) {
            $patternMatches = $this->_testPattern($route, trim($requestUri, '/'));
            if ($patternMatches) {
                $matchedRoute->route = $route;
                $matchedRoute->matches = $patternMatches;
            }
        }

        // Merge user defined GET variables with original GET vars
        if (isset($matchedRoute->route->mappings)) {
            $additionalGetVars = $this->_mapPatternMatches($matchedRoute->route, $matchedRoute->matches);
            $_GET = array_merge($_GET, $additionalGetVars);
        }
        
        return $matchedRoute->route;   
    }

    /**
     * Retrieve a static route
     * @param string $routeName The static route being requested
     * @return Route Returns the requested static route
     * @throws Exception If requested route does not exist
     */
    public function getStaticRoute($routeName)
    {
        $routes = $this->_staticRoutes();
        if (isset($routes[$routeName])) {
            return $routes[$routeName];
        } else {
            throw new Exception('Unknown Static Route');
        }
    }

    /**
     * Retrieve static built-in routes
     * @return array List of static routes
     */
    protected function _staticRoutes()
    {
        $routes = array();

        // Catch all route
        $routes['pages'] = new Route(array(
            'path' => '.*',
            'location' => 'cc-core/controllers/page.php'
        ));
        
        
        /** General Routes **/
        
        $routes['index'] = new Route(array(
            'path' => '/',
            'location' => 'cc-core/controllers/index.php'
        ));

        $routes['browseVideos'] = new Route(array(
            'path' => 'videos',
            'location' => 'cc-core/controllers/videos.php'
        ));

        $routes['browseVideosPaginated'] = new Route(array(
            'path' => 'videos/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('page')
        ));

        $routes['browseVideosCategories'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('category')
        ));

        $routes['browseVideosCategoriesPaginated'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('category', 'page')
        ));

        $routes['browseVideosSorted'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('load')
        ));

        $routes['browseVideosSortedPaginated'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('load', 'page')
        ));
        
        $routes['play'] = new Route(array(
            'path' => 'videos/([0-9]+)/[a-z0-9\-]+',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('vid')
        ));  

        $routes['browseMembers'] = new Route(array(
            'path' => 'members',
            'location' => 'cc-core/controllers/members.php'
        ));

        $routes['browseMembersPaginated'] = new Route(array(
            'path' => 'members/page/([0-9]+)',
            'location' => 'cc-core/controllers/members.php',
            'mappings' => array('page')
        ));
        
        $routes['profile'] = new Route(array(
            'path' => 'members/([a-z0-9]+)',
            'location' => 'cc-core/controllers/profile.php',
            'mappings' => array('username')
        ));

        $routes['optOut'] = new Route(array(
            'path' => 'opt-out',
            'location' => 'cc-core/controllers/opt_out.php'
        ));

        $routes['register'] = new Route(array(
            'path' => 'register',
            'location' => 'cc-core/controllers/register.php'
        ));

        $routes['activate'] = new Route(array(
            'path' => 'activate',
            'location' => 'cc-core/controllers/activate.php'
        ));

        $routes['login'] = new Route(array(
            'path' => 'login',
            'location' => 'cc-core/controllers/login.php'
        ));

        $routes['forgotPassword'] = new Route(array(
            'path' => 'login/(forgot)',
            'location' => 'cc-core/controllers/login.php',
            'mappings' => array('action')
        ));

        $routes['logout'] = new Route(array(
            'path' => 'logout',
            'location' => 'cc-core/system/logout.php'
        ));

        $routes['search'] = new Route(array(
            'path' => 'search',
            'location' => 'cc-core/controllers/search.php'
        ));

        $routes['searchPaginated'] = new Route(array(
            'path' => 'search/page/([0-9]+)',
            'location' => 'cc-core/controllers/search.php',
            'mappings' => array('page')
        ));

        $routes['contact'] = new Route(array(
            'path' => 'contact',
            'location' => 'cc-core/controllers/contact.php'
        ));
        
        
        /** Private Videos Routes **/

        $routes['getPrivateCode'] = new Route(array(
            'path' => 'private/get',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('get_private' => 'true')
        ));

        $routes['privateVideoPlay'] = new Route(array(
            'path' => 'private/videos/([a-z0-9]+)',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('private')
        ));

        
        /** My Account Routes **/
        
        $routes['myaccount'] = new Route(array(
            'path' => 'myaccount',
            'location' => 'cc-core/controllers/myaccount/myaccount.php'
        ));

        $routes['myaccountUpload'] = new Route(array(
            'path' => 'myaccount/upload',
            'location' => 'cc-core/controllers/myaccount/upload.php'
        ));

        $routes['myaccountUploadVideo'] = new Route(array(
            'path' => 'myaccount/upload/video',
            'location' => 'cc-core/controllers/myaccount/upload_video.php'
        ));

        $routes['myaccountUploadComplete'] = new Route(array(
            'path' => 'myaccount/upload/complete',
            'location' => 'cc-core/controllers/myaccount/upload_complete.php'
        ));

        $routes['myaccountMyVideos'] = new Route(array(
            'path' => 'myaccount/myvideos',
            'location' => 'cc-core/controllers/myaccount/myvideos.php'
        ));

        $routes['myaccountMyVideosDelete'] = new Route(array(
            'path' => 'myaccount/myvideos/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/myvideos.php',
            'mappings' => array('vid')
        ));

        $routes['myaccountMyVideosPaginated'] = new Route(array(
            'path' => 'myaccount/myvideos/page/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/myvideos.php',
            'mappings' => array('page')
        ));

        $routes['myaccountEditVideo'] = new Route(array(
            'path' => 'myaccount/editvideo/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/edit_video.php',
            'mappings' => array('vid')
        ));

        $routes['myaccountPlaylists'] = new Route(array(
            'path' => 'myaccount/playlists',
            'location' => 'cc-core/controllers/myaccount/playlists.php'
        ));

        $routes['myaccountPlaylistsEdit'] = new Route(array(
            'path' => 'myaccount/playlists/edit/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/playlists_edit.php',
            'mappings' => array('playlist_id')
        ));

        $routes['myaccountUpdateProfile'] = new Route(array(
            'path' => 'myaccount/profile',
            'location' => 'cc-core/controllers/myaccount/update_profile.php'
        ));

        $routes['myaccountResetAvatar'] = new Route(array(
            'path' => 'myaccount/profile/(reset)',
            'location' => 'cc-core/controllers/myaccount/update_profile.php',
            'mappings' => array('action')
        ));

        $routes['myaccountPrivacySettings'] = new Route(array(
            'path' => 'myaccount/privacy-settings',
            'location' => 'cc-core/controllers/myaccount/privacy_settings.php'
        ));

        $routes['myaccountChangePassword'] = new Route(array(
            'path' => 'myaccount/change-password',
            'location' => 'cc-core/controllers/myaccount/change_password.php'
        ));

        $routes['myaccountSubscriptions'] = new Route(array(
            'path' => 'myaccount/subscriptions',
            'location' => 'cc-core/controllers/myaccount/subscriptions.php'
        ));

        $routes['myaccountSubscriptionsDelete'] = new Route(array(
            'path' => 'myaccount/subscriptions/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/subscriptions.php',
            'mappings' => array('id')
        ));

        $routes['myaccountSubscriptionsPaginated'] = new Route(array(
            'path' => 'myaccount/subscriptions/page/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/subscriptions.php',
            'mappings' => array('page')
        ));

        $routes['myaccountSubscribers'] = new Route(array(
            'path' => 'myaccount/subscribers',
            'location' => 'cc-core/controllers/myaccount/subscribers.php'
        ));

        $routes['myaccountSubscribersPaginated'] = new Route(array(
            'path' => 'myaccount/subscribers/page/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/subscribers.php',
            'mappings' => array('page')
        ));

        $routes['myaccountInbox'] = new Route(array(
            'path' => 'myaccount/message/inbox',
            'location' => 'cc-core/controllers/myaccount/message_inbox.php'
        ));

        $routes['myaccountInboxDelete'] = new Route(array(
            'path' => 'myaccount/message/inbox/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/message_inbox.php',
            'mappings' => array('delete')
        ));

        $routes['myaccountInboxPaginated'] = new Route(array(
            'path' => 'myaccount/message/inbox/page/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/message_inbox.php',
            'mappings' => array('page')
        ));

        $routes['myaccountReadMessage'] = new Route(array(
            'path' => 'myaccount/message/read/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/message_read.php',
            'mappings' => array('msg')
        ));

        $routes['myaccountSendMessage'] = new Route(array(
            'path' => 'myaccount/message/send',
            'location' => 'cc-core/controllers/myaccount/message_send.php'
        ));

        $routes['myaccountSendMessageUsername'] = new Route(array(
            'path' => 'myaccount/message/send/([a-z0-9]+)',
            'location' => 'cc-core/controllers/myaccount/message_send.php',
            'mappings' => array('username')
        ));

        $routes['myaccountSendMessageReply'] = new Route(array(
            'path' => 'myaccount/message/reply/([0-9]+)',
            'location' => 'cc-core/controllers/myaccount/message_send.php',
            'mappings' => array('msg')
        ));
        
        
        /** Mobile Routes **/
        
        $routes['mobile'] = new Route(array(
            'path' => 'm',
            'location' => 'cc-core/controllers/mobile/index.php',
            'mobile' => true
        ));
        
        $routes['mobileBrowseVideos'] = new Route(array(
            'path' => 'm/v',
            'location' => 'cc-core/controllers/mobile/videos.php',
            'mobile' => true
        ));
        
        $routes['mobilePlay'] = new Route(array(
            'path' => 'm/v/([0-9]+)',
            'location' => 'cc-core/controllers/mobile/play.php',
            'mappings' => array('vid'),
            'mobile' => true
        ));
        
        $routes['mobileSearch'] = new Route(array(
            'path' => 'm/s',
            'location' => 'cc-core/controllers/mobile/search.php',
            'mobile' => true
        ));
        
        $routes['mobileLogin'] = new Route(array(
            'path' => 'm/l',
            'location' => 'cc-core/controllers/mobile/login.php',
            'mobile' => true
        ));
        
        $routes['mobileUser'] = new Route(array(
            'path' => 'm/u/([a-z0-9]+)',
            'location' => 'cc-core/controllers/mobile/user.php',
            'mappings' => array('username'),
            'mobile' => true
        ));
        
        
        /** System Routes **/
        
        $routes['system404'] = new Route(array(
            'path' => 'not-found',
            'location' => 'cc-core/controllers/system_404.php'
        ));
        
        $routes['systemError'] = new Route(array(
            'path' => 'system-error',
            'location' => 'cc-core/controllers/system_error.php'
        ));
                
        $routes['embed'] = new Route(array(
            'path' => 'embed/([0-9]+)',
            'location' => 'cc-core/system/embed.php',
            'mappings' => array('vid')
        ));

        $routes['languageGet'] = new Route(array(
            'path' => 'language/(get)',
            'location' => 'cc-core/system/language.php',
            'mappings' => array('action')
        ));

        $routes['languageSet'] = new Route(array(
            'path' => 'language/(set)/(.*)',
            'location' => 'cc-core/system/language.php',
            'mappings' => array('action', 'language')
        ));

        $routes['videoSitemap'] = new Route(array(
            'path' => 'video-sitemap\.xml',
            'location' => 'cc-core/system/video_sitemap.php'
        ));

        $routes['videoSitemapPaginated'] = new Route(array(
            'path' => 'video-sitemap-([0-9]+)\.xml',
            'location' => 'cc-core/system/video_sitemap.php',
            'mappings' => array('page')
        ));
        
        
        /** AJAX Routes **/

        $routes['ajaxMemberVideos'] = new Route(array(
            'path' => 'members/videos',
            'location' => 'cc-core/controllers/ajax/member.videos.ajax.php'
        ));

        $routes['ajaxMemberPlaylists'] = new Route(array(
            'path' => 'members/playlists',
            'location' => 'cc-core/controllers/ajax/member.playlists.ajax.php'
        ));

        $routes['ajaxSearchSuggest'] = new Route(array(
            'path' => 'search/suggest',
            'location' => 'cc-core/controllers/ajax/search.suggest.ajax.php'
        ));

        $routes['ajaxAvatarUpload'] = new Route(array(
            'path' => 'myaccount/upload/avatar',
            'location' => 'cc-core/controllers/ajax/avatar.ajax.php'
        ));

        $routes['ajaxVideoUpload'] = new Route(array(
            'path' => 'myaccount/upload/validate',
            'location' => 'cc-core/controllers/ajax/upload.ajax.php'
        ));

        $routes['ajaxUsernameExists'] = new Route(array(
            'path' => 'actions/username',
            'location' => 'cc-core/controllers/ajax/username.ajax.php'
        ));

        $routes['ajaxFlag'] = new Route(array(
            'path' => 'actions/flag',
            'location' => 'cc-core/controllers/ajax/flag.ajax.php'
        ));

        $routes['ajaxPlaylist'] = new Route(array(
            'path' => 'actions/playlist',
            'location' => 'cc-core/controllers/ajax/playlist.ajax.php'
        ));

        $routes['ajaxSubscribe'] = new Route(array(
            'path' => 'actions/subscribe',
            'location' => 'cc-core/controllers/ajax/subscribe.ajax.php'
        ));

        $routes['ajaxRate'] = new Route(array(
            'path' => 'actions/rate',
            'location' => 'cc-core/controllers/ajax/rate.ajax.php'
        ));

        $routes['ajaxCommentAdd'] = new Route(array(
            'path' => 'actions/comment/add',
            'location' => 'cc-core/controllers/ajax/comment.add.ajax.php'
        ));

        $routes['ajaxCommentGet'] = new Route(array(
            'path' => 'actions/comments/get',
            'location' => 'cc-core/controllers/ajax/comment.get.ajax.php'
        ));

        $routes['ajaxPost'] = new Route(array(
            'path' => 'actions/post',
            'location' => 'cc-core/controllers/ajax/post.ajax.php'
        ));

        $routes['ajaxMobileLoadMoreVideos'] = new Route(array(
            'path' => 'actions/mobile-videos',
            'location' => 'cc-core/controllers/ajax/mobile_videos.ajax.php',
            'mobile' => true
        ));

        $routes['ajaxMobileLoadMoreSearch'] = new Route(array(
            'path' => 'actions/mobile-search',
            'location' => 'cc-core/controllers/ajax/mobile_search.ajax.php',
            'mobile' => true
        ));


        /** API Routes **/

        $routes['apiGetVideo'] = new Route(array(
            'path' => 'api/video/([0-9]+)',
            'location' => 'cc-core/controllers/api/video.get.php',
            'mappings' => array('videoId')
        ));

        $routes['apiGetVideoList'] = new Route(array(
            'path' => 'api/video/list',
            'location' => 'cc-core/controllers/api/video_list.get.php'
        ));

        return Plugin::triggerFilter('router.getRoutes', $routes);
    }
}