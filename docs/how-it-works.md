## Overview

We built CuteOn.Me to demonstrate how you could use the awe.sm APIs in a sample application.  Using the awe.sm APIs is not the most efficient way to build this application, but it allows us to demonstrate many features of awe.sm and its APIs. Checkout a live version of the application at <http://CuteOn.Me> or take a look at the code.  The code documents its logic, but here we'll give you an in-depth explanation about how and why to utilize awe.sm APIs and features.

### awe.sm Features
* create shares
* batch creation of shares
* update shares with post metadata
* redirection patterns
* conversion tracking
* stats
* no data stored on the server

The live code uses the performance branch which has additional logic for features like the chrome extension.  Feel free to look over that code, but the additional features clutter the code, so we recommend to looking at the simpler master branch first.

### Objective

CuteOn.Me is a web application designed for the simple task of **capturing friends' advice for a link**.  Using the awe.sm APIs and features you can achieve this with a few tasks:

* Create unique shares for each friend with the same URL
* Display the URL to the friend and capture their vote
* Display what your friends voted

We chose to use Twitter direct messages for sharing because the Twitter APIs allow for an easy intergration.  Sharing can easily be extended to email or other social media channels.

### Code 
The code uses: 

* PHP
* PHP cURL
* HTML
* CSS
* Javascript
* Twitter APIs (via <https://github.com/abraham/twitteroauth>)
* awe.sm APIs (via PHP cURL)

The code doesn't persist any information because all the data is stored inside Twitter and awe.sm.  Only a few fields are hardcoded or temporarily stored:

* Twitter application key (in configuration file)
* awe.sm API key to access awe.sm data (in configuration file)
* Twitter OAuth values (stored in the user's session) 

## Create shares

### Single create

To capture each friend's advice we need a way to differentiate one friend from another.  Using awe.sm we can easily create a URL for each friend using awe.sm shares.  awe.sm shares let you tag metadata to a URL and provide you with a short link that redirects to your destination URL.  

Visually, creating a share looks like you are tagging a shortlink with metadata:

![Share with metadata](/awesm/cuteonme/raw/master/docs/img/share.png)

For this applicaiton, the destination URL will be the URL we want our friends to see and vote on, and we'll include additional metadata so we can associate each URL to each of our friends as well as track other attributes.  

* url = the URL we want to share with our friends
* user_id = your Twitter user ID so we can identify which shares you created
* tag = the Twitter user ID of the friend we are sharing to
* notes = the message we want to include in the Twitter direct message

Additional values to include:

* user\_id_username = your Twitter username
* user\_id_icon_url = your Twitter user icon url
* tool = the tool used to create the awe.sm share
* channel = the social media channel that the link is shared to


Request URL

    http://api.awe.sm/url.json?
        v=3&
        key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743&
        url=http://www.cuteon.me&
        user_id=17301118&
        tag=190498288&
        notes=cute%20on%20me%20right%3F&
        user_id_username=bhiles&
        user_id_icon_url=http://a0.twimg.com/profile_images/1292259951/f_ing_social_media_head_normal.jpg&
        tool=tHSSFr&
        channel=twitter-message

JSON Response

    { 
    awesm_url: "http://CuteOn.Me/2W"
    awesm_id: "CuteOn.Me_2W"
    domain: "CuteOn.Me"
    path: "2W"
    created_at: "2011-09-26T23:17:47Z"
    original_url: "http://www.cuteon.me"
    redirect_url: "http://www.cuteon.me/opinion.php?awesm=CuteOn.Me_2W&sharer_icon_url=&url=http%3A%2F%2Fwww.cuteon.me&message=cute+on+me+right%3F&sharer=bhiles"
    channel: "twitter-dm"
    service: "twitter"
    tool: "hackdisrupttool-twitterdm"
    application: "hackdisrupttool"
    parent: null
    sharer_id: null
    username: "bhiles"
    service_userid: null
    service_postid: null
    service_postid_metadata: {
        reach: null
        shared_at: null
    }
    campaign: null
    campaign_metadata: {
        description: null
        name: null
    }
    user_id: "17301118"
    user_id_metadata: {
        profile_url: null
        icon_url: null
    }
    tag: "190498288"
    notes: "cute on me right?"
    }

[Create API documentation](https://github.com/awesm/awesm-dev-tools/wiki/Create-API)

### Batch Create <a id="batch"/>

Instead of making an API call for each friend to create a share, we can use the batch creation endpoint which allows for an array of values for one field and returns multiple shares.  If our friends had user IDs 190498288 and 371635480, the api call would be:

    http://api.awe.sm/url/batch.json?
        v=3&
        key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743&
        url=http://www.cuteon.me&user_id=17301118&notes=cute%20on%20me%20right%3F&
        user_id_username=bhiles&user_id_icon_url=http://a0.twimg.com/profile_images/1292259951/f_ing_social_media_head_normal.jpg&
        tool=tHSSFr&
        channel=twitter-message&
        tag[]=190498288&
        tag[]=371635480

## Share with friends

For each of your friends to see the URL you want their advice on, you need to send each friend the awe.sm URL that was created for of them.  CuteOn.Me uses Twitter direct messages for sharing.  So we just need to extract the awe.sm URL from the create API's response and send a direct message to each friend.

## Update Shares <a id="update"/>

After you send a direct message, the response includes metadata about the post.  awe.sm shares have specific fields for this kind of metadata, so you can update these fields.

* service\_postid = the ID of the direct message
* service\_postid\_shared\_at = the time the share was created
* service\_postid\_reach = a direct message will only be seen by one person, so this value will be 1
 
[Update endpoint documentation](https://github.com/awesm/awesm-dev-tools/wiki/Create-API#wiki-update)

## Redirect Friends <a id="redirectionpatterns"/>
Your friends will receive a direct message containing an awe.sm URL.  The default configuration for awe.sm will have this link redirect to the URL specified.  But, we want to display the URL and capture a voting action from the friend.  We will need javascript to capture the friend's action, but since the URL could be anything, we won't be able to have javascript running on every URL.  Instead, we can redirect the friend back to a page we host.  Then we can display the URL using an iframe and easily run javascript on the page to capture the voting choice.  awe.sm has a feature called redirection patterns where any of a share's metadata fields can be used to build the URL that a user is redirected to after they click an awe.sm URL.  Our redirect pattern needs the following logic:

* redirect to http://www.cuteon.me so we can run javascript on the page
* query parameters
 * url to view the URL in an iframe
 * awesm_id to attribute a vote to an awe.sm share which has the user ID of the friend

[Redirection pattern documentation](https://github.com/awesm/awesm-dev-tools/wiki/Redirection-Patterns)

For CuteOn.Me we pass in additional values so when a friend is redirected they get information about who shared the link to them and the message from the direct message.  

The actual redirection pattern is:

    http://www.cuteon.me/opinion.php?url=%escaped_original_url%&sharer=%user_id_username%&sharer_icon_url=%user_id_icon_url%&message=%notes%&awesm=%awesm_id%

## Conversions <a id="conversions"/>
Once a friend follows the link and ends up on the opinion page, they can make their voting choice.  awe.sm shares so far have metadata associated with them, but when a user interacts with a link actual data is collected.  The simplest form of data is clicks.  When a user clicks on a link, we capture that a click occurred.  Another type of data is conversions, which allow you to capture user-defined actions.  The actions we want to collect is whether a friend votes _yes_ or _no_ for the URL displayed.  awe.sm supports 5 different conversion types per project, so CuteOn.Me is configured for the first conversion type to be a _yes_ vote, and the second type to be for a _no_ vote.  Conversions are collected by calling an API endpoint specifying the awe.sm share, the conversion type, and the value.   We have a javascript library that takes care of most of the heavy lifting, so your code just needs to include the library and make a javascript call to execute.

Sample code

    <script src="http://widgets.awe.sm/v3/widgets.js?key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743"></script>
    <script>
        function voteYes() { AWESM.convert('goal_1',0); }
        function voteNo() { AWESM.convert('goal_2',0); }
    </script>

[Conversion documentation](https://github.com/awesm/awesm-dev-tools/wiki/Conversion-Tracking)

## Stats <a id="stats"/>

To show you what your friends voted, we need to query awe.sm for your data.  The awe.sm Stats API allows you to query data you created and collected inside awe.sm.  We want to group by the URLs you shared, and group again by the friends you shared with so we can see how each friend voted.  This translates to a Stats API call filtered by our user_id, grouped by original_url, and then pivoted by tag.  We also want to see conversion data which needs to be enabled in the call.  Finally, we sort by shared_at so we can see the URL shared most recently first.

Request URL

    http://api.awe.sm/stats/range.json?
        v=3&
        key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743&
        user_id=17301118&
        group_by=original_url&
        pivot=tag&
        with_metadata=true&
        with_conversions=true&
        sort_type=shared_at

[Stats API documentation](https://github.com/awesm/awesm-dev-tools/wiki/Stats-API)

The response only shows raw data, so conversions are only displayed as having numeric values.  There is no logic in the API call that says whether what a friend voted, instead we need to add an algorithm to transform the voting conversion data into what a friend voted.  The algorithm is: 

    if goal_1 (yes conversion type) count > 0, then vote is a yes
    else if goal_2 (no conversion type) count > 0, then vote is no
    else didn't vote

The stats API call allows us to populate the majority of the dashboard.  We can iterate over each URL that was shared, find each of the friends the URL that was shared, and calculate each friends' vote.  To make the dashboard more helpful we include more data.  The title of the URL is displayed to allow for a human readable value for the URL.  Also, the message that was sent along with the link is displayed.  All of this collected using stats API calls.

## Conclusion

This is just one example of how you can use awe.sm as a platform to build applications around sharing.
