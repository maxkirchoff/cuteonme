# CuteOn.Me
A simple web application that uses the awe.sm APIs to get quick advice from your friends.  

Try it out at [http://CuteOn.Me](http://CuteOn.Me)

## Setup

### Requirements
*  ubuntu

### Instructions
    # install required packages
    apt-get install php5 php5-curl apache2 git-core

    # download the repo
    git clone git@github.com:awesm/hackdisrupt.git cuteonme

    # update the submodules
    cd cuteonme
    git submodule init
    git submodule update
    cd ..

    # copy the application to apache's directory
    cp -R cuteonme /var/www/

    # update the apache configuration
    cp cuteonme/setup/cuteonme-apache-config /etc/apache2/sites-available/cuteonme
    a2dissite default
    a2ensite cuteonme
    apache2ctl restart
    
    # set www.cuteon.me as a valid hostname
    vi /etc/hosts
      add: 127.0.0.1    www.cuteon.me

    # test your installation
    curl http://localhost/test-install.php
    # response should be: "Installation successful!"

    # try out the app 
    http://CuteOn.Me
