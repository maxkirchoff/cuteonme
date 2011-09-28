# CuteOn.Me
A simple web application that uses the awe.sm APIs to get quick advice from your friends.  

Try it out at <http://CuteOn.Me>

Read more about [how it works](/awesm/cuteonme/docs/how-it-works.markdown)

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
