# CuteOn.Me
A simple web application that uses the awe.sm APIs to get quick advice from your friends.  

Try it out at <http://CuteOn.Me>

Read more about [how it works](/awesm/cuteonme/blob/master/docs/how-it-works.md)

Or just go take a look at the code

### Code Guide

The application uses Twitter to identify you as a user and requires that you grant our CuteOn.Me Twitter application access to send direct messages to your friends. If you are going to dive into the code, you should examine the code in the following order:

1. [signin.php](/awesm/cuteonme/blob/master/signin.php)
 * presents a link to begin authenticating with Twitter
2. [signin-redirect.php](/awesm/cuteonme/blob/master/signin-redirect.php)
 * begins the OAuth flow and redirects you to Twitter to authenticate
3. [index.php](/awesm/cuteonme/blob/master/index.php)
 * where you are redirected after authenticating
 * presents a view of shared URLs and what people voted
4. [signed-in-check.php](/awesm/cuteonme/blob/master/signed-in-check.php)
 * included in shares.php to complete the OAuth flow and fetch your access tokens
5. [share.php](/awesm/cuteonme/blob/master/share.php)
 * a form to share a URL to your friends
6. [share-submit.php](/awesm/cuteonme/blob/master/share-submit.php)
 * the logic for creating awe.sm shares and sending the direct messages
7. [opinion.php](/awesm/cuteonme/blob/master/opinion.php)
 * presents a friend with a URL and a place to vote

## Setup

### Requirements
*  ubuntu

### Instructions

Install required packages

    apt-get install php5 php5-curl apache2 git-core

Download the repo

    git clone git@github.com:awesm/hackdisrupt.git cuteonme

Update the submodules

    cd cuteonme
    git submodule init
    git submodule update
    cd ..

Copy the application to apache's directory

    cp -R cuteonme /var/www/

Update the apache configuration

    cp cuteonme/setup/cuteonme-apache-config /etc/apache2/sites-available/cuteonme
    a2dissite default
    a2ensite cuteonme
    apache2ctl restart

Set www.cuteon.me as a valid hostname

    vi /etc/hosts
    # add: 127.0.0.1    www.cuteon.me

Test your installation

    curl http://www.cuteon.me/static/html/test-install.html
    # response should be: "Installation successful!"

Try out the app <http://CuteOn.Me>
